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
  var getid = element.getAttribute("id");
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
            jQuery('#zfb-timeslots-table-container').html(data);
            jQuery('#booking_date').val(getid);
        }
        
    });
}
function getClicked_next(element) {
  // alert("test");
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
  // alert("wee");
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
        jQuery('#month-navigationid').html(data);
        jQuery('#zfb-timeslots-table-container').html('');
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
            // jQuery('#headtodays_date').html(monthName + ' ' + currentYear);
            // jQuery('#zfb-slot-list').html('');
           
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
function selectTimeslot(element) {
  // alert("test");
  const selectedElements = $('.zfb_timeslot.selected');
  // Deselect all previously selected timeslots
  selectedElements.removeClass('selected');
  selectedElements.find('.zfb-selected-capacity').hide();
  // Select the clicked timeslot
  $(element).addClass('selected');
  
  var seats = $(element).find('.zfb-tooltip-text').attr('data-seats');
  var datawaiting = $(element).find('.zfb-waiting').attr('data-waiting');
  if(seats == 0 || seats === 'not_available' ){
    if(datawaiting == 0){
      $(element).find('.zfb-selected-capacity').hide();
    }else{
      $(element).find('.zfb-selected-capacity').show();
    }   
  }else{
    
   $(element).find('.zfb-selected-capacity').show();
  }
  // console.log(seats);
  // alert(seats);  
  // $(element).append('<input class="zfb-selected-capacity" type="number" name="zfb-selected-capacity_i" placeholder="Enter Slot Capacity" min="1" value="1">');
}
jQuery(document).ready(function($) {
  var currentStep = 1;
  var totalSteps = $('.container > .step').length;
  // alert(totalSteps);
  
  function updateButtons() {
    if (currentStep === 1) {
      $('#backButton').hide();
    } else {
      $('#backButton').show();
    }
    
    if (currentStep === totalSteps) {
      $('#nextButton').hide();
    } else {
      $('#nextButton').show();
    }
  }
  function showStep(step) {
    $('.step').hide();
    $('.step.step' + step).show();
  }
  $('#backButton').click(function() {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
      updateButtons();
    }
  });
  
  $('#nextButton').click(function() {
    if (currentStep < totalSteps) {
      currentStep++;
      showStep(currentStep);
      updateButtons();
    } else {
      // Perform actions for the last step (e.g., submit form, show success message)
      alert('Form submitted!');
    }
  });
  
  // Initial setup
  updateButtons();
  showStep(currentStep);
});
