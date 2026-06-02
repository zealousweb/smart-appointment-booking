<?php
/**
 * Optimized post queries by post meta (avoids slow meta_query in WP_Query).
 *
 * @package Smart Appointment & Booking
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SAAB_Query' ) ) {

	/**
	 * Database helpers for entry lookups by post meta.
	 */
	class SAAB_Query {

		const CACHE_GROUP = 'saab_db_queries';

		/**
		 * Maximum number of meta conditions per query.
		 */
		const MAX_META_JOINS = 20;

		/**
		 * Register cache invalidation hooks.
		 *
		 * @return void
		 */
		public static function init() {
			add_action( 'save_post_manage_entries', array( __CLASS__, 'flush_cache' ) );
			add_action( 'deleted_post', array( __CLASS__, 'flush_cache_on_deleted_post' ), 10, 2 );
			add_action( 'updated_post_meta', array( __CLASS__, 'maybe_flush_cache_on_meta' ), 10, 4 );
			add_action( 'added_post_meta', array( __CLASS__, 'maybe_flush_cache_on_meta' ), 10, 4 );
			add_action( 'deleted_post_meta', array( __CLASS__, 'maybe_flush_cache_on_meta' ), 10, 4 );
			add_filter( 'posts_clauses', array( __CLASS__, 'filter_single_meta_posts_clauses' ), 10, 2 );
			add_filter( 'posts_clauses', array( __CLASS__, 'filter_admin_entries_posts_clauses' ), 10, 2 );
		}

		/**
		 * Flush query cache when manage_entries meta changes.
		 *
		 * @param int    $meta_id    Meta ID.
		 * @param int    $post_id    Post ID.
		 * @param string $meta_key   Meta key.
		 * @param mixed  $meta_value Meta value.
		 * @return void
		 */
		public static function maybe_flush_cache_on_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
			unset( $meta_id, $meta_key, $meta_value );

			if ( 'manage_entries' !== get_post_type( $post_id ) ) {
				return;
			}

			self::flush_cache();
		}

		/**
		 * Clear cached query results (WordPress 5.9+ compatible).
		 *
		 * @return void
		 */
		public static function flush_cache() {
			wp_cache_set( 'saab_query_cache_version', microtime( true ), self::CACHE_GROUP );
		}

		/**
		 * Cache-buster version for query cache keys.
		 *
		 * @return string
		 */
		private static function get_cache_version() {
			$version = wp_cache_get( 'saab_query_cache_version', self::CACHE_GROUP );
			if ( false === $version ) {
				$version = (string) microtime( true );
				wp_cache_set( 'saab_query_cache_version', $version, self::CACHE_GROUP );
			}

			return (string) $version;
		}

		/**
		 * Clear cache when a manage_entries post is deleted.
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 * @return void
		 */
		public static function flush_cache_on_deleted_post( $post_id, $post ) {
			unset( $post_id );

			if ( $post instanceof WP_Post && 'manage_entries' === $post->post_type ) {
				self::flush_cache();
			}
		}

		/**
		 * Query posts that match all given meta conditions (AND).
		 *
		 * @param string $post_type       Post type slug.
		 * @param array  $meta_conditions List of arrays with keys: key, value, compare (=|LIKE).
		 * @param array  $query_args      Optional. posts_per_page, paged, orderby, order, post_status.
		 * @return array{posts: int[], found_posts: int, max_num_pages: int}
		 */
		public static function query_by_meta_and( $post_type, array $meta_conditions, array $query_args = array() ) {
			if ( empty( $meta_conditions ) ) {
				return array(
					'posts'         => array(),
					'found_posts'   => 0,
					'max_num_pages' => 0,
				);
			}

			$defaults = array(
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'paged'          => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$query_args = wp_parse_args( $query_args, $defaults );

			$cache_key = 'meta_and_' . self::get_cache_version() . md5( wp_json_encode( array( $post_type, $meta_conditions, $query_args ) ) );
			$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}

			$found_posts = self::count_posts_by_meta_and( $post_type, $meta_conditions, $query_args['post_status'] );
			$post_ids    = self::get_post_ids_by_meta_and( $post_type, $meta_conditions, $query_args );

			$posts_per_page = (int) $query_args['posts_per_page'];
			$max_num_pages  = 1;

			if ( $posts_per_page > 0 ) {
				$max_num_pages = (int) max( 1, ceil( $found_posts / $posts_per_page ) );
			}

			$result = array(
				'posts'         => $post_ids,
				'found_posts'   => $found_posts,
				'max_num_pages' => $max_num_pages,
			);

			wp_cache_set( $cache_key, $result, self::CACHE_GROUP, MINUTE_IN_SECONDS );

			return $result;
		}

		/**
		 * Count posts matching meta conditions.
		 *
		 * @param string $post_type       Post type.
		 * @param array  $meta_conditions Meta conditions.
		 * @param string $post_status     Post status or "any".
		 * @return int
		 */
		public static function count_posts_by_meta_and( $post_type, array $meta_conditions, $post_status = 'publish' ) {
			$cache_key = 'count_' . self::get_cache_version() . md5( wp_json_encode( array( $post_type, $meta_conditions, $post_status ) ) );
			$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

			if ( false !== $cached ) {
				return (int) $cached;
			}

			$count = count( self::get_intersected_post_ids( $post_type, $meta_conditions, $post_status ) );

			wp_cache_set( $cache_key, $count, self::CACHE_GROUP, MINUTE_IN_SECONDS );

			return $count;
		}

		/**
		 * Get post IDs matching meta conditions with ordering and pagination.
		 *
		 * @param string $post_type       Post type.
		 * @param array  $meta_conditions Meta conditions.
		 * @param array  $query_args      Query arguments.
		 * @return int[]
		 */
		public static function get_post_ids_by_meta_and( $post_type, array $meta_conditions, array $query_args ) {
			$cache_key = 'ids_' . self::get_cache_version() . md5( wp_json_encode( array( $post_type, $meta_conditions, $query_args ) ) );
			$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}

			$post_status = isset( $query_args['post_status'] ) ? $query_args['post_status'] : 'publish';
			$matched_ids = self::get_intersected_post_ids( $post_type, $meta_conditions, $post_status );

			if ( empty( $matched_ids ) ) {
				wp_cache_set( $cache_key, array(), self::CACHE_GROUP, MINUTE_IN_SECONDS );
				return array();
			}

			$wp_query_args = array(
				'post_type'           => $post_type,
				'post__in'            => $matched_ids,
				'posts_per_page'      => (int) $query_args['posts_per_page'],
				'paged'               => max( 1, (int) $query_args['paged'] ),
				'orderby'             => $query_args['orderby'],
				'order'               => $query_args['order'],
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			);

			if ( 'any' === $post_status ) {
				$wp_query_args['post_status'] = 'any';
			} else {
				$wp_query_args['post_status'] = $post_status;
			}

			$ordered_query = new WP_Query( $wp_query_args );
			$post_ids      = array();

			if ( ! empty( $ordered_query->posts ) && is_array( $ordered_query->posts ) ) {
				$post_ids = array_map( 'absint', $ordered_query->posts );
			}

			wp_reset_postdata();

			wp_cache_set( $cache_key, $post_ids, self::CACHE_GROUP, MINUTE_IN_SECONDS );

			return $post_ids;
		}

		/**
		 * Post IDs matching every meta condition (AND), via cached per-condition lookups.
		 *
		 * @param string $post_type       Post type.
		 * @param array  $meta_conditions Meta conditions.
		 * @param string $post_status     Post status or "any".
		 * @return int[]
		 */
		private static function get_intersected_post_ids( $post_type, array $meta_conditions, $post_status = 'publish' ) {
			$cache_key = 'intersect_' . self::get_cache_version() . md5( wp_json_encode( array( $post_type, $meta_conditions, $post_status ) ) );
			$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}

			$candidate_ids = null;
			$index         = 0;

			foreach ( $meta_conditions as $condition ) {
				if ( empty( $condition['key'] ) ) {
					continue;
				}

				$compare = isset( $condition['compare'] ) ? $condition['compare'] : '=';
				$value   = isset( $condition['value'] ) ? $condition['value'] : '';
				$key     = $condition['key'];
				$ids     = self::get_post_ids_for_single_meta( $post_type, $post_status, $key, $value, $compare );

				if ( null === $candidate_ids ) {
					$candidate_ids = $ids;
				} else {
					$candidate_ids = array_values( array_intersect( $candidate_ids, $ids ) );
				}

				if ( empty( $candidate_ids ) ) {
					$candidate_ids = array();
					break;
				}

				++$index;
				if ( $index >= self::MAX_META_JOINS ) {
					break;
				}
			}

			if ( ! is_array( $candidate_ids ) ) {
				$candidate_ids = array();
			}

			wp_cache_set( $cache_key, $candidate_ids, self::CACHE_GROUP, MINUTE_IN_SECONDS );

			return $candidate_ids;
		}

		/**
		 * Post IDs for one meta key/value pair (posts_clauses JOIN, cached).
		 *
		 * @param string $post_type   Post type.
		 * @param string $post_status Post status or "any".
		 * @param string $meta_key    Meta key.
		 * @param string $meta_value  Meta value.
		 * @param string $compare     = or LIKE.
		 * @return int[]
		 */
		private static function get_post_ids_for_single_meta( $post_type, $post_status, $meta_key, $meta_value, $compare = '=' ) {
			$single_cache_key = 'single_' . self::get_cache_version() . md5( wp_json_encode( func_get_args() ) );
			$cached           = wp_cache_get( $single_cache_key, self::CACHE_GROUP );

			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}

			$ids_query = new WP_Query(
				array(
					'post_type'              => $post_type,
					'post_status'            => ( 'any' === $post_status ) ? 'any' : $post_status,
					'fields'                 => 'ids',
					'posts_per_page'         => -1,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'saab_single_meta_lookup' => array(
						'key'     => $meta_key,
						'value'   => $meta_value,
						'compare' => $compare,
					),
				)
			);

			$ids = array();

			if ( ! empty( $ids_query->posts ) && is_array( $ids_query->posts ) ) {
				$ids = array_map( 'absint', $ids_query->posts );
			}

			wp_reset_postdata();

			wp_cache_set( $single_cache_key, $ids, self::CACHE_GROUP, MINUTE_IN_SECONDS );

			return $ids;
		}

		/**
		 * INNER JOIN for single-meta lookups (replaces meta_query on internal WP_Query).
		 *
		 * @param string[] $clauses   Query clauses.
		 * @param WP_Query $wp_query  Query instance.
		 * @return string[]
		 */
		public static function filter_single_meta_posts_clauses( $clauses, $wp_query ) {
			$lookup = $wp_query->get( 'saab_single_meta_lookup' );
			if ( empty( $lookup ) || ! is_array( $lookup ) || empty( $lookup['key'] ) ) {
				return $clauses;
			}

			$compare = isset( $lookup['compare'] ) ? $lookup['compare'] : '=';
			$value   = isset( $lookup['value'] ) ? $lookup['value'] : '';
			$key     = $lookup['key'];

			$clauses['join'] .= self::get_single_meta_join_sql( $compare, $key, $value );

			return $clauses;
		}

		/**
		 * Prepared JOIN for a single meta condition (fixed alias saab_sm_0).
		 *
		 * @param string $compare Compare operator.
		 * @param string $key     Meta key.
		 * @param string $value   Meta value.
		 * @return string
		 */
		private static function get_single_meta_join_sql( $compare, $key, $value ) {
			global $wpdb;

			if ( 'LIKE' === $compare ) {
				return $wpdb->prepare(
					" INNER JOIN {$wpdb->postmeta} AS saab_sm_0 ON ({$wpdb->posts}.ID = saab_sm_0.post_id AND saab_sm_0.meta_key = %s AND saab_sm_0.meta_value LIKE %s) ",
					$key,
					$value
				);
			}

			return $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS saab_sm_0 ON ({$wpdb->posts}.ID = saab_sm_0.post_id AND saab_sm_0.meta_key = %s AND saab_sm_0.meta_value = %s) ",
				$key,
				$value
			);
		}

		/**
		 * Add INNER JOINs for admin manage_entries list filters (replaces meta_query on main query).
		 *
		 * @param string[] $clauses   Query clauses.
		 * @param WP_Query $wp_query  Query instance.
		 * @return string[]
		 */
		public static function filter_admin_entries_posts_clauses( $clauses, $wp_query ) {
			if ( ! is_admin() || ! $wp_query->is_main_query() ) {
				return $clauses;
			}

			$filters = $wp_query->get( 'saab_entries_meta_filters' );
			if ( empty( $filters ) || ! is_array( $filters ) ) {
				return $clauses;
			}

			if ( 'manage_entries' !== $wp_query->get( 'post_type' ) ) {
				return $clauses;
			}

			global $wpdb;

			$index = 0;
			foreach ( $filters as $filter ) {
				if ( empty( $filter['key'] ) ) {
					continue;
				}

				$compare = isset( $filter['compare'] ) ? $filter['compare'] : '=';
				$value   = isset( $filter['value'] ) ? $filter['value'] : '';
				$key     = $filter['key'];

				if ( 0 === $index ) {
					$clauses['join'] .= self::get_entries_filter_join_sql( $compare, 0, $key, $value );
				} elseif ( 1 === $index ) {
					$clauses['join'] .= self::get_entries_filter_join_sql( $compare, 1, $key, $value );
				}

				++$index;
				if ( $index >= 2 ) {
					break;
				}
			}

			return $clauses;
		}

		/**
		 * Prepared JOIN clause for admin entries list (fixed alias per index).
		 *
		 * @param string $compare Compare operator.
		 * @param int    $index   0 or 1 only.
		 * @param string $key     Meta key.
		 * @param string $value   Meta value.
		 * @return string
		 */
		private static function get_entries_filter_join_sql( $compare, $index, $key, $value ) {
			global $wpdb;

			if ( 0 === $index ) {
				if ( 'LIKE' === $compare ) {
					return $wpdb->prepare(
						" INNER JOIN {$wpdb->postmeta} AS saab_ef_0 ON ({$wpdb->posts}.ID = saab_ef_0.post_id AND saab_ef_0.meta_key = %s AND saab_ef_0.meta_value LIKE %s) ",
						$key,
						$value
					);
				}

				return $wpdb->prepare(
					" INNER JOIN {$wpdb->postmeta} AS saab_ef_0 ON ({$wpdb->posts}.ID = saab_ef_0.post_id AND saab_ef_0.meta_key = %s AND saab_ef_0.meta_value = %s) ",
					$key,
					$value
				);
			}

			if ( 'LIKE' === $compare ) {
				return $wpdb->prepare(
					" INNER JOIN {$wpdb->postmeta} AS saab_ef_1 ON ({$wpdb->posts}.ID = saab_ef_1.post_id AND saab_ef_1.meta_key = %s AND saab_ef_1.meta_value LIKE %s) ",
					$key,
					$value
				);
			}

			return $wpdb->prepare(
				" INNER JOIN {$wpdb->postmeta} AS saab_ef_1 ON ({$wpdb->posts}.ID = saab_ef_1.post_id AND saab_ef_1.meta_key = %s AND saab_ef_1.meta_value = %s) ",
				$key,
				$value
			);
		}
	}

	SAAB_Query::init();
}
