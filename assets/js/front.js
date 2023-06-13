function getClickedId(element) {
  var clickedId = element.getAttribute("data_day");
    // console.log(clickedId); // Print the clicked ID to the console
    // Perform any further operations with the clicked ID as needed
    jQuery.ajax({
        url: myAjax.ajaxurl,
        type : 'post',
        data: { 
        action: "action_display_available_timeslots",
        form_data: clickedId,
        },
        success: function (data) {
            //console.log(data);
            jQuery('#timeslot_result_i').html(data);
        }
        
    });
}
 // Get the elements
//  var prevMonthArrow = document.getElementById("prev-month");
//  var nextMonthArrow = document.getElementById("next-month");
//  var monthSelect = document.getElementById("month");

//  // Add event listeners for navigation
//  prevMonthArrow.addEventListener("click", navigateMonth.bind(null, -1));
//  nextMonthArrow.addEventListener("click", navigateMonth.bind(null, 1));
//  monthSelect.addEventListener("change", selectMonth);

//  function navigateMonth(direction) {
//      var selectedMonth = parseInt(monthSelect.value);
//      selectedMonth += direction;

//      if (selectedMonth < 1) {
//          selectedMonth = 12;
//          currentYear--;
//      } else if (selectedMonth > 12) {
//          selectedMonth = 1;
//          currentYear++;
//      }

//      monthSelect.value = selectedMonth;
//      // You can perform any necessary actions here, like updating the calendar with the new month
//  }

//  function selectMonth() {
//      // You can perform any necessary actions here, like updating the calendar with the selected month
//  }
