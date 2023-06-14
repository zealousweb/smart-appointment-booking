jQuery(document).ready(function($) {
    jQuery(document).on('change', '#bms_year', function() {        
        var currentMonth = document.getElementById('bms_month').value;
        var currentYear = document.getElementById('bms_year').value;
        reloadCalendar(currentMonth, currentYear);
    });
});
jQuery(document).ready(function($) {
    jQuery(document).on('change', '#bms_month', function() {        
        var currentMonth = document.getElementById('bms_month').value;
        var currentYear = document.getElementById('bms_year').value;       
        reloadCalendar(currentMonth, currentYear);
        // reload_timeslot_value(currentMonth, currentYear);
    });
});

function getClickedId(element) {
  var data_day = element.getAttribute("data_day");
  var clickedId = element.getAttribute("id");  
  jQuery('table td').removeClass('calselected_date');
  jQuery('#'+clickedId).addClass('calselected_date');
    // Perform any further operations with the clicked ID as needed
    jQuery.ajax({
        url: myAjax.ajaxurl,
        type : 'post',
        data: { 
        action: "action_display_available_timeslots",
        form_data: data_day,
        clickedId:clickedId
        },
        success: function (data) {
            //console.log(data);
            jQuery('#timeslot_result_i').html(data);
        }
        
    });
}
function getClicked_next(element) {
    var currentMonth = document.getElementById('bms_month').value;
    var currentYear = document.getElementById('bms_year').value;

    if (parseInt(currentMonth) === 12) {
        currentMonth = 1;
        currentYear++;
    }else{
        currentMonth++;
    }
    reloadCalendar(currentMonth,currentYear);
}
function getClicked_prev(element) {
  
    var currentMonth = document.getElementById('bms_month').value;
    var currentYear = document.getElementById('bms_year').value;
    if (parseInt(currentMonth) === 1) {
        currentMonth = 12;
        currentYear--;
    } else {
        currentMonth--;
    }
    reloadCalendar(currentMonth,currentYear);
}
function reloadCalendar(currentMonth, currentYear) {
    var form_id = document.getElementById('zealform_id').value;
    var lastdateid = jQuery('#zeallastdate').val();
    console.log(lastdateid);
    jQuery.ajax({
      url: myAjax.ajaxurl,
      type: 'post',
      data: {
        action: "action_reload_calender",
        currentMonth: currentMonth,
        currentYear: currentYear,
        lastdateid:lastdateid,
        form_id: form_id,
      },
      success: function(data) {
        jQuery('#calender_reload').html(data);
        if (lastdateid) { // Check if lastdateid is not empty or falsy
            // alert("test1");
          var element = jQuery('#' + lastdateid);
          if (element.length > 0) {
            element.click();
          } else {
            // alert("test");
            var currentMonth = document.getElementById('bms_month').value;
            var currentYear = document.getElementById('bms_year').value;
            var monthName = getMonthName(parseInt(currentMonth));
            jQuery('#headtodays_date').html(monthName + ' ' + currentYear);
            jQuery('#timeslot-container').html('');
          }
        }
      }
    });
  }
  
function getMonthName(month) {
    var monthNames = {
        1: 'January',
        2: 'February',
        3: 'March',
        4: 'April',
        5: 'May',
        6: 'June',
        7: 'July',
        8: 'August',
        9: 'September',
        10: 'October',
        11: 'November',
        12: 'December'
      };
    return monthNames[month];
}