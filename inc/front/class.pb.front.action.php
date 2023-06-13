<?php
/**
 * PB_Front_Action Class
 *
 * Handles the Frontend Actions.
 *
 * @package WordPress
 * @subpackage Plugin name
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'PB_Front_Action' ) ){

	/**
	 *  The PB_Front_Action Class
	 */
	class PB_Front_Action {

		function __construct()  {

		
			add_action('wp_ajax_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );
			add_action('wp_ajax_nopriv_bms_front_save_post_meta', array( $this, 'bms_front_save_post_meta' ) );

			add_action( 'wp_enqueue_scripts',  array( $this, 'action__enqueue_styles' ));
			add_action( 'wp_enqueue_scripts', array( $this, 'action__wp_enqueue_scripts' ));

			add_shortcode('booking_form',array( $this, 'zealbms_get_booking_form' ));
		}

		/*
		   ###     ######  ######## ####  #######  ##    ##  ######
		  ## ##   ##    ##    ##     ##  ##     ## ###   ## ##    ##
		 ##   ##  ##          ##     ##  ##     ## ####  ## ##
		##     ## ##          ##     ##  ##     ## ## ## ##  ######
		######### ##          ##     ##  ##     ## ##  ####       ##
		##     ## ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##     ##  ######     ##    ####  #######  ##    ##  ######
		*/

		/**
		 * Action: wp_enqueue_scripts
		 *
		 * - enqueue script in front side
		 *
		 */
		function action__wp_enqueue_scripts() {
			wp_enqueue_script( PB_PREFIX . '_bookingform', PB_URL . 'assets/js/booking-form.js', array( 'jquery-core' ), PB_VERSION );

			// wp_enqueue_script( PB_PREFIX . '_front_js', PB_URL . 'assets/js/front.min.js', array( 'jquery-core' ), PB_VERSION );
			wp_enqueue_script( 'bms_formio_full_min', PB_URL.'assets/js/formio.full.min.js', array( 'jquery' ), 1.1, false );
			wp_localize_script('bms_formio_full_min', 'myAjax', array(
				'ajaxurl' => admin_url('admin-ajax.php')
			));
			wp_enqueue_script( 'bms_bootstrap.min', PB_URL.'assets/js/bootstrap.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.slim.min', PB_URL.'assets/js/jquery-3.7.0.slim.min.js', array( 'jquery' ), 1.1, false );
			wp_enqueue_script( 'bms_jquery-3.7.0.min',PB_URL.'assets/js/jquery-3.7.0.min.js', array( 'jquery' ), 1.1, false );
		}
		function action__enqueue_styles() {
			
			wp_enqueue_style( 'bms_boostrap_min',PB_URL.'assets/css/bootstrap.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_formio_full_min',PB_URL.'assets/css/formio.full.min.css', array(), 1.1, 'all' );
			wp_enqueue_style( 'bms_font-awesomev1','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css', array(), 1.1, 'all' );
				
	   }


		/*
		######## ##     ## ##    ##  ######  ######## ####  #######  ##    ##  ######
		##       ##     ## ###   ## ##    ##    ##     ##  ##     ## ###   ## ##    ##
		##       ##     ## ####  ## ##          ##     ##  ##     ## ####  ## ##
		######   ##     ## ## ## ## ##          ##     ##  ##     ## ## ## ##  ######
		##       ##     ## ##  #### ##          ##     ##  ##     ## ##  ####       ##
		##       ##     ## ##   ### ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##        #######  ##    ##  ######     ##    ####  #######  ##    ##  ######
		*/
		
		function zealbms_get_booking_form_test() {
			ob_start();
			?>
			<p id="clickajax">click</p>
			<?php
			
			
			return ob_get_clean();
		}
		function bms_front_save_post_meta() {
			
        	$form_id = $_POST['fid'];
            $form_data = $_POST['form_data'];
            // Prepare the new post data
			$pid = get_option('tot_bms_entries');
			if(empty($pid)){
				$pid = 1;
			}else{
				$pid++;
			}
            $new_post = array(
              	'post_title'   => 'submission_#' . $pid,
             	'post_type'    => 'bms_entries',
             	'post_status'  => 'publish'
            );
            
            $created_post_id = wp_insert_post($new_post);
			update_option('tot_bms_entries',$pid);
			update_post_meta($created_post_id,'bms_submission_data',$form_data);
			update_post_meta($created_post_id,'bms_form_id',$form_id);
            wp_send_json_success( 'Form data saved successfully.' );
            exit;
		}
		function zealbms_get_booking_form($attr) {
			ob_start();	
			?>
			<div id="calender"></div>
			
			
			<?php
			// Get the current month and year
			$currentMonth = date('m');
			$currentYear = date('Y');

			// Remove leading zeros from the current month
			$currentMonth = ltrim($currentMonth, '0');

			// Create an array of month names
			$monthNames = array(
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December'
			);

			

			// Get the number of days in the current month
			$numberOfDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

			// Get the first day of the current month
			$firstDay = date('N', strtotime("$currentYear-$currentMonth-01"));

			// Output the calendar
			echo "<div style='display: inline-block; vertical-align: top;margin-left:660px'>";
			// Output the dropdown menu for selecting the month
			// echo "<label for='month'>Select Month:</label>";
			echo "<select name='bms_month' id='month'>";
			for ($i = $currentMonth; $i <= 12; $i++) {
				echo "<option value='$i'";
				if ($i == $currentMonth) {
					echo " selected";
					echo ">{$monthNames[$i]} $currentYear</option>";
				}else{
					echo ">{$monthNames[$i]}</option>";
				}
				
			}
			echo "</select>";
			//simply display month name
			//echo "<h2>{$monthNames[$currentMonth]}</h2>";
			echo "<table>";
			echo "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";

			// Start the table row
			echo "<tr>";

			// Output blank cells for the days before the first day of the month
			for ($i = 1; $i < $firstDay; $i++) {
				echo "<td></td>";
			}

			// Output the days of the month
			for ($day = 1; $day <= $numberOfDays; $day++) {
				echo "<td data_day='bms_".$currentMonth."_".$day."' class='bms_cal_day'>$day</td>";

				// Start a new row after each Saturday
				if ((($day + $firstDay - 1) % 7) == 0 && $day != $numberOfDays) {
					echo "</tr><tr>";
				}
			}

			// Complete the table row with blank cells
			while ((($day + $firstDay - 1) % 7) != 0) {
				echo "<td></td>";
				$day++;
			}

			// Close the table row and table
			echo "</tr>";
			echo "</table>";
			echo "</div>";

			// Output the additional div with the provided heading and time slots
			echo "<div style='display: inline-block; vertical-align: top; margin-left: 25px;'>";
			echo "<h3>Available Time Slots</h3>";
			echo "<h4>June 05, 2023</h4>";
			echo "<p>11:00 to 12:00</p>";
			echo "<p>12:00 to 13:00</p>";
			echo "<p>14:00 to 15:00</p>";
			echo "</div>";

			$timeslot = '';
			?>
			
			<input type="hidden" value="<?php echo $currentMonth; ?>" name="month" >
			<input type="hidden" value="<?php echo $timeslot; ?>" name="timeslot" >
			<?php	
			
			if($timeslot && $currentMonth){

			
				$post_id = $attr['form_id'];
				if (get_post($post_id)) {
				$post_status = get_post_status($post_id);
				if ($post_status === 'publish') {
					$fields = get_post_meta($post_id, '_my_meta_value_key', true);
					if ($fields) {
					?>
					<div id="formio"></div>
					<script type='text/javascript'>
						var formid = <?php echo json_encode($post_id); ?>;
						var myScriptData = <?php echo $fields; ?>;
						
						var value = myScriptData;
						console.log(value);
						Formio.createForm(document.getElementById('formio'), {
						components: value
						}).then(function(form) {
							form.on('submit', function(submission) {
								event.preventDefault();
								jQuery.ajax({
									url: '<?php echo admin_url('admin-ajax.php'); ?>',
									type : 'post',
									data: { 
									action: "bms_front_save_post_meta",
									form_data: submission,
									fid:formid,
									},
									success: function (data) {
										console.log(data);
									}
									
								});
								return false;
							});
								
						});
						
					</script>
					<?php
					} else {
					echo __('Form data not found.', 'bms');
					}
				} else {
					// Post exists but is not published
					echo __("Post exists but is not published.", 'bms');
				}
				} else {
				// Post does not exist
				echo __("Post does not exist.", 'bms');
				}
			}
			return ob_get_clean();
		  }

	}//eoc

	add_action( 'plugins_loaded', function() {
		// PB()->front->action = new PB_Front_Action;
		$PB_Front_Action = new PB_Front_Action();
	} );
}
function PB_Front_Action() {
	return new PB_Front_Action();	
}
PB_Front_Action();