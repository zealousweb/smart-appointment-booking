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

			add_action('wp_ajax_action_reload_calender', array( $this, 'action_reload_calender' ) );
			add_action('wp_ajax_nopriv_action_reload_calender', array( $this, 'action_reload_calender' ) );

			//on click of any date
			add_action('wp_ajax_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );
			add_action('wp_ajax_nopriv_action_display_available_timeslots', array( $this, 'action_display_available_timeslots' ) );

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
			if(is_admin()){
				wp_enqueue_script( PB_PREFIX . '_bookingform', PB_URL . 'assets/js/booking-form.js', array( 'jquery-core' ), PB_VERSION );
			}
			
			wp_enqueue_script( PB_PREFIX . '_front', PB_URL . 'assets/js/front.js', array( 'jquery-core' ), PB_VERSION );
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
			$post_id = $attr['form_id'];			
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

			// Get the first day of the current month
			$firstDayOfWeek = date('w', strtotime($currentYear . '-' . $currentMonth . '-01'));
			$firstDayOfWeek += 1;
			
			// Output the calendar
			?>
			<div style='display: inline-block; vertical-align: top;margin-left:660px' id='calender_reload'>
				<div class="month-navigation">
					<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>">
					
					<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
					<!-- <span class="arrow" id="prev-month"  >&larr;</span> -->					
					<select name='bms_month_n' id='bms_month'>
						<?php
						for ($i = 1; $i <= 12; $i++) {
							echo "<option value='$i'";
							if ($i == $currentMonth) {
								echo " selected";
							}
							echo ">{$monthNames[$i]}</option>";
						}
						?>
					</select>
					<select name="bms_year_n" id="bms_year">
						<?php
						$startYear = $currentYear + 5;
						$endYear = 1990;
						for ($year = $startYear; $year >= $endYear; $year--) {
							echo "<option value='$year'";
							if ($year == $currentYear) {
								echo " selected";
							}
							echo ">$year</option>";
						}
						?>
					</select>
					<span class="arrow" id="next-month" onclick="getClicked_next(this)">&rarr;</span>
				</div>
				<table>
					<tr>
						<th>Sun</th>
						<th>Mon</th>
						<th>Tue</th>
						<th>Wed</th>
						<th>Thu</th>
						<th>Fri</th>
						<th>Sat</th>
					</tr>

					<?php
					$totalDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
					$daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1, $currentYear);

					// Calculate the number of cells needed
					$totalCells = ceil(($totalDays + $firstDayOfWeek) / 7) * 7;

					$dayCounter = 1;
					$date = 1;
					$monthYear = $currentMonth . '-' . $currentYear;

					while ($dayCounter <= $totalCells) {
						echo "<tr>";
						for ($i = 0; $i < 7; $i++) {
							if ($dayCounter >= $firstDayOfWeek && $date <= $totalDays) {
								$isToday = ($date == date('j') && $monthYear == date('n-Y')) ? "calselected_date" : "";
								if ($isToday === "calselected_date") {
									$lastdateid = 'calid_' . $post_id . '_' . $currentMonth . '_' . $date . '_' . $currentYear;
								}
								echo "<td id='calid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='bms_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='bms_cal_day $isToday' onclick='getClickedId(this)'>$date</td>";
								$date++;
							} elseif ($dayCounter < $firstDayOfWeek) {
								$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter) + 1;
								echo "<td class='previous-month'>$prevDate</td>";
							} else {
								$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
								echo "<td class='next-month'>$nextDate</td>";
							}

							$dayCounter++;
						}

						echo "</tr>";
					}
					?>
				</table>
			</div>

			<style>
				.previous-month {
					color: #999;
				}

				.next-month {
					color: #999;
				}
			</style>

			<!-- // Output the additional div with the provided heading and time slots -->
			<div class='timeslot_result_c' id='timeslot_result_i' style='display: inline-block; vertical-align: top; margin-left: 25px;'>
							
				<?php
				$TodaysDate = date('F d, Y');	
				echo "<h3 id='head_avail_time'>Available Time Slots</h3>";
				echo "<h4 id='headtodays_date'>$TodaysDate</h4>";			
				$check_type = get_post_meta($post_id,'enable_recurring_apt',true);
				?>
				<input type="hidden" id="zeallastdate" name="zeallastdate_n" value="<?php echo $lastdateid; ?>" >
				<div id='timeslot-container'>
					<?php
					//if single
					if($check_type){
						//check recurring type
					}else{
						$selected_date = get_post_meta($post_id,'selected_date',true);
						$selected_date = date("Y-m-d", strtotime($selected_date));

						if($selected_date == $date ){
							
							$start_time = get_post_meta( $post_id, 'start_time', true );
							$end_time = get_post_meta( $post_id, 'end_time', true );
							$timeslot_duration = get_post_meta($post_id, 'timeslot_duration', true);
							$steps_duration = get_post_meta( $post_id, 'steps_duration', true );

							$start_hours = $start_time['hours'];
							$start_minutes = $start_time['minutes'];
							$start_seconds = $start_time['seconds'];

							$end_hours = $end_time['hours'];
							$end_minutes = $end_time['minutes'];
							$end_seconds = $end_time['seconds'];

							$timeslot_hours = $timeslot_duration['hours'];
							$timeslot_minutes = $timeslot_duration['minutes'];
							$timeslot_seconds = $timeslot_duration['seconds'];

							$gap_hours = $steps_duration['hours'];
							$gap_minutes = $steps_duration['minutes'];
							$gap_seconds = $steps_duration['seconds'];

							$start_time_seconds = ($start_hours * 3600) + ($start_minutes * 60) + $start_seconds;
							$end_time_seconds = ($end_hours * 3600) + ($end_minutes * 60) + $end_seconds;
							$timeslot_duration_seconds = ($timeslot_hours * 3600) + ($timeslot_minutes * 60) + $timeslot_seconds;
							$gap_seconds = ($gap_hours * 3600) + ($gap_minutes * 60) + $gap_seconds;

							$current_time = $start_time_seconds;
							while ($current_time <= $end_time_seconds) {
								$st_hours = floor($current_time / 3600);
								$st_minutes = floor(($current_time % 3600) / 60);
								$st_seconds = $current_time % 60;

								$current_time += $timeslot_duration_seconds;
								$et_hours = floor($current_time / 3600);
								$et_minutes = floor(($current_time % 3600) / 60);
								$et_seconds = $current_time % 60;

								$st_formatted_time = sprintf('%02d:%02d:%02d', $st_hours, $st_minutes, $st_seconds);
								$et_formatted_time = sprintf('%02d:%02d:%02d', $et_hours, $et_minutes, $et_seconds);
								// echo $st_formatted_time . " - " . $et_formatted_time ."<br>";
								echo "<p>".$st_formatted_time . " - " . $et_formatted_time ."</p>";
								$current_time += $gap_seconds;
							}
						}else{
							echo "<p class='not_avail'>Not Available</p>";
						}
					}		
					?>
				</div>
			</div>
			<?php
			$timeslot = '';			
			?>
			
			<input type="hidden" value="<?php echo $currentMonth; ?>" name="month" >
			<input type="hidden" value="<?php echo $timeslot; ?>" name="timeslot" >
			<?php	
			
			if($timeslot && $currentMonth){			
				
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
		  function action_reload_calender(){
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
			$currentMonth = $_POST['currentMonth'];
			$currentYear = $_POST['currentYear'];
			$post_id = $_POST['form_id'];
			
			?>
			<div class="month-navigation">
					<input type="hidden" id="zealform_id" value="<?php echo $post_id; ?>" >
					<span class="arrow" id="prev-month" onclick="getClicked_prev(this)">&larr;</span>
					<select name='bms_month_n' id='bms_month'>
						<?php
						for ($i = 1; $i <= 12; $i++) {
							echo "<option value='$i'";
							if ($i == $currentMonth) {
								echo " selected";
							}
							echo ">{$monthNames[$i]}</option>";
						}
						?>
					</select>
					<select name="bms_year_n" id="bms_year">
						<?php
						$startYear = $currentYear+5;
						$endYear = 1990;
						for ($year = $startYear; $year >= $endYear; $year--) {
							echo "<option value='$year'";
							if ($year == $currentYear) {
								echo " selected";
							}
							echo ">$year</option>";
						}
						?>
					</select>
					<span class="arrow" id="next-month" onclick="getClicked_next(this)">&rarr;</span>
				</div>
				<table>
					<tr>
						<th>Sun</th>
						<th>Mon</th>
						<th>Tue</th>
						<th>Wed</th>
						<th>Thu</th>
						<th>Fri</th>
						<th>Sat</th>
					</tr>
					<?php
					$firstDayOfWeek = date('N', strtotime($currentYear . '-' . $currentMonth . '-01'));
					$firstDayOfWeek += 1;
					$totalDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
					$daysInPreviousMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth - 1, $currentYear);

					// Calculate the number of cells needed
					$totalCells = ($totalDays + $firstDayOfWeek - 1) % 7 === 0 ? $totalDays + $firstDayOfWeek - 1 : ceil(($totalDays + $firstDayOfWeek - 1) / 7) * 7;

					$dayCounter = 1;
					$date = 1;
					$monthYear = $currentMonth . '-' . $currentYear;

					while ($dayCounter <= $totalCells) {
						echo "<tr>";
						for ($i = 1; $i <= 7; $i++) {
							if ($dayCounter >= $firstDayOfWeek && $date <= $totalDays) {
								// $isToday = ($date == date('j') && $monthYear == date('n-Y')) ? "calselected_date" : "";
								echo "<td  id='calid_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' data_day='bms_" . $post_id . '_' . $currentMonth . "_" . $date . "_" . $currentYear . "' class='bms_cal_day' onclick='getClickedId(this)'>$date</td>";
								$date++;
							} elseif ($dayCounter < $firstDayOfWeek) {
								$prevDate = $daysInPreviousMonth - ($firstDayOfWeek - $dayCounter - 1);
								echo "<td class='previous-month'>$prevDate</td>";
							} else {
								$nextDate = $dayCounter - ($totalDays + $firstDayOfWeek) + 1;
								echo "<td class='next-month'>$nextDate</td>";
							}

							$dayCounter++;
						}

						echo "</tr>";
					}
					?>
				</table>
			<?php
			wp_die();
		  }
		  function action_display_available_timeslots(){

				if(isset( $_POST['form_data'])){

					$form_data = $_POST['form_data'];
					$array_data = explode('_',$form_data);
					// print_r($array_data);
					$post_id = $array_data[1];
					$current_month = $array_data[2];
					$current_day = $array_data[3];
					$current_year = $array_data[4];
				}
				if(isset( $_POST['clickedId'])){
					$clickedId = $_POST['clickedId'];
				}
				$date = date('Y-m-d', strtotime("$current_year-$current_month-$current_day"));
				$TodaysDate = date('F d, Y', strtotime("$current_year-$current_month-$current_day"));
				echo "<h3 id='head_avail_time'>Available Time Slots</h3>";
				echo "<h4 id='headtodays_date'>$TodaysDate</h4>";
				echo '<input type="hidden" id="zeallastdate" value="'.$clickedId.'" >';
				echo "<div id='timeslot-container'>";
				
				$check_type = get_post_meta($post_id,'enable_recurring_apt',true);
				//if single
				if($check_type){

				}else{
					$selected_date = get_post_meta($post_id,'selected_date',true);
					$selected_date = date("Y-m-d", strtotime($selected_date));
					if($selected_date == $date ){
						
						$start_time = get_post_meta( $post_id, 'start_time', true );
						$end_time = get_post_meta( $post_id, 'end_time', true );
						$timeslot_duration = get_post_meta($post_id, 'timeslot_duration', true);
						$steps_duration = get_post_meta( $post_id, 'steps_duration', true );

						$start_hours = $start_time['hours'];
						$start_minutes = $start_time['minutes'];
						$start_seconds = $start_time['seconds'];

						$end_hours = $end_time['hours'];
						$end_minutes = $end_time['minutes'];
						$end_seconds = $end_time['seconds'];

						$timeslot_hours = $timeslot_duration['hours'];
						$timeslot_minutes = $timeslot_duration['minutes'];
						$timeslot_seconds = $timeslot_duration['seconds'];

						$gap_hours = $steps_duration['hours'];
						$gap_minutes = $steps_duration['minutes'];
						$gap_seconds = $steps_duration['seconds'];

						$start_time_seconds = ($start_hours * 3600) + ($start_minutes * 60) + $start_seconds;
						$end_time_seconds = ($end_hours * 3600) + ($end_minutes * 60) + $end_seconds;
						$timeslot_duration_seconds = ($timeslot_hours * 3600) + ($timeslot_minutes * 60) + $timeslot_seconds;
						$gap_seconds = ($gap_hours * 3600) + ($gap_minutes * 60) + $gap_seconds;

						$current_time = $start_time_seconds;
						while ($current_time <= $end_time_seconds) {
							$st_hours = floor($current_time / 3600);
							$st_minutes = floor(($current_time % 3600) / 60);
							$st_seconds = $current_time % 60;

							$current_time += $timeslot_duration_seconds;
							$et_hours = floor($current_time / 3600);
							$et_minutes = floor(($current_time % 3600) / 60);
							$et_seconds = $current_time % 60;

							$st_formatted_time = sprintf('%02d:%02d:%02d', $st_hours, $st_minutes, $st_seconds);
							$et_formatted_time = sprintf('%02d:%02d:%02d', $et_hours, $et_minutes, $et_seconds);
							// echo $st_formatted_time . " - " . $et_formatted_time ."<br>";
							echo "<p>".$st_formatted_time . " - " . $et_formatted_time ."</p>";
							$current_time += $gap_seconds;
						}
					}else{
						echo "<p class='message_note'>Not Available</p>";
					}
				}
				echo "</div>";
				wp_die();
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