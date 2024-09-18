jQuery(document).ready(function($) {
  jQuery(document).on('change', '#sab_year', function() {        
      var currentMonth = document.getElementById('sab_month').value;
      var currentYear = document.getElementById('sab_year').value;
      reloadCalendar(currentMonth, currentYear);
  });
});
jQuery(document).ready(function($) {
  jQuery(document).on('change', '#sab_month', function() {        
      var currentMonth = document.getElementById('sab_month').value;
      var currentYear = document.getElementById('sab_year').value;       
      reloadCalendar(currentMonth, currentYear);
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
        action: "saab_action_display_available_timeslots",
        form_data: data_day,
        clickedId:clickedId
        },
        success: function (data) {
           
            jQuery('#saab-timeslots-table-container').html(data);
            jQuery('#booking_date').val(getid);
            checkNextButtonState();
        }
        
    });
  }
// Listen for month dropdown change
jQuery(document).on('change', '#saab_month', function() {
  var currentMonth = jQuery(this).val();
  var currentYear = jQuery('#saab_year').val();
  reloadCalendar(currentMonth, currentYear);
});

// Listen for year dropdown change
jQuery(document).on('change', '#saab_year', function() {
  var currentYear = jQuery(this).val();
  var currentMonth = jQuery('#saab_month').val();
  reloadCalendar(currentMonth, currentYear);
});

function getClicked_next(element) {
  var currentMonth = parseInt(document.getElementById('saab_month').value);
  var currentYear = parseInt(document.getElementById('saab_year').value);

  if (currentMonth === 12) {
      currentMonth = 1;
      currentYear++;
  } else {
      currentMonth++;
  }
  reloadCalendar(currentMonth, currentYear);
}

function getClicked_prev(element) {
  var currentMonth = parseInt(document.getElementById('saab_month').value);
  var currentYear = parseInt(document.getElementById('saab_year').value);

  if (currentMonth === 1) {
      currentMonth = 12;
      currentYear--;
  } else {
      currentMonth--;
  }
  reloadCalendar(currentMonth, currentYear);
}

function reloadCalendar(currentMonth, currentYear) {
  var form_id = document.getElementById('zealform_id').value;
  jQuery.ajax({
      url: myAjax.ajaxurl,
      type: 'post',
      data: {
          action: "saab_action_reload_calender",
          currentMonth: currentMonth,
          currentYear: currentYear,
          form_id: form_id,
      },
      success: function(data) {
          jQuery('#month-navigationid').html(data);
          jQuery('#saab-timeslots-table-container').html('');
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
  const selectedElements = jQuery('.saab_timeslot.selected');
  var message = jQuery('#no-timeslots-message');
  message.hide();
  selectedElements.removeClass('selected');
  jQuery(element).addClass('selected');
  var isEnabled = true; 
  jQuery('.saab-selected-capacity').prop('disabled', isEnabled);
  jQuery('.saab-selected-capacity').show();
  var seats = jQuery(element).find('.saab-tooltip-text').attr('data-seats');
  var waitingseats = jQuery(element).find('.saab-waiting').attr('data-seats');
  var datawaiting = jQuery(element).find('.saab-waiting').attr('data-waiting');
   // // Find the element with the id "calender_reload"
  var saabStripeElement = document.getElementById('calender_reload');

  // Check if the element exists
  if (saabStripeElement) {
      // Use jQuery to set the "data-seats" attribute
      jQuery(saabStripeElement).attr('data-seats', seats);
  }
  if(seats == 0 || seats === 'not_available' ){
    if(datawaiting == 0){
      
      jQuery('.saab-selected-capacity').hide();
      var isEnabled = true; 
      jQuery('.saab-selected-capacity').prop('disabled', isEnabled);
      message.show();
      jQuery('.saab-selected-capacity').attr('max', seats);
    }else{
      var isEnabled = true; 
      jQuery('.saab-selected-capacity').prop('disabled', !isEnabled); 
      jQuery('.saab-selected-capacity').attr('max', 1);
      
    }   
  }else{    
    var isEnabled = false;
    jQuery('.saab-selected-capacity').prop('disabled', isEnabled);
    jQuery(element).find('.saab-selected-capacity').show();
    jQuery('.saab-selected-capacity').attr('max', seats);
  }

}


jQuery(document).ready(function($) {
  var currentStep = 1;
  var totalSteps = jQuery('.container > .step').length;
  function updateButtons() {
      if (currentStep === 1) {
        jQuery('#backButton').hide();
      } else {
        jQuery('#backButton').show();
      }
      
      if (currentStep === totalSteps) {
        jQuery('#nextButton').hide();
      } else {
        jQuery('#nextButton').show();
      }
  }
  function showStep(step) {
    jQuery('.step').hide();
    jQuery('.step.step' + step).show();
  }
  jQuery('#backButton').click(function() {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
      updateButtons();
    }
  });

  jQuery('#nextButton').click(function() {
    if (currentStep < totalSteps) {
      currentStep++;
      showStep(currentStep);
      updateButtons();
    } else {
      alert('Form submitted!');
    }
  });
  updateButtons();
  showStep(currentStep);
});
function cancelbooking_getQueryParam(name) {
const urlParams = new URLSearchParams(window.location.search);
return urlParams.get(name);
}
//cancel booking shortcode
jQuery(document).ready(function($) {
jQuery('.booking-cancellation-buttons .btn-yes').on('click', function() {
 
    var bookingId = cancelbooking_getQueryParam("booking_id");
    var bookingstatus = cancelbooking_getQueryParam("status");

    if (bookingId && bookingstatus === 'cancel') {
        jQuery.ajax({
            url: myAjax.ajaxurl,
            type: 'post',
            data: {
                action: 'saab_cancel_booking_shortcode',
                bookingId: bookingId
            },
            success: function(response) {
              // console.log(response.message);
                if (response.message) {
                    jQuery('.booking-cancellation-card').html('<p>'+ response.message+'</p>');
                } else {
                    jQuery('#msg_booking_cancel').html('<p>Failed to cancel the booking. Please try again.</p>');
                    
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    } else {
        jQuery('#msg_booking_cancel').html('<p>Unable to cancel the booking.</p>');
    }
});
});
function checkNextButtonState() {
  var inputValue = parseInt(jQuery('.saab-selected-capacity').val());
  var maxValue = parseInt(jQuery('.saab-selected-capacity').attr('max'));
  var minValue = parseInt(jQuery('.saab-selected-capacity').attr('min'));
  var isInputValid = inputValue >= minValue && inputValue <= maxValue;
  //console.log(isInputValid);
  var isSelected = jQuery('#saab-slot-list li').hasClass('selected');
  var isButtonDisabled = !isInputValid || !isSelected;

  jQuery('#nextButton').prop('disabled', isButtonDisabled);
}
jQuery(document).ready(function($) {
  
  checkNextButtonState();
  jQuery(window).on('load', function() {
    checkNextButtonState();
  });
  jQuery('.saab-selected-capacity').change(function() {
    checkNextButtonState();
  });
  jQuery('.saab-selected-capacity').on('input', function() {
   
    var value = jQuery(this).val();
    // Remove any non-numeric characters except digits
    value = value.replace(/[^\d]/g, '');
    if (value === '') {
      value = '1'; // Set to 1 if input is empty
    }
    jQuery(this).val(value);     
    checkNextButtonState();
    
  });

  jQuery(document).on('click', '#saab-slot-list li', function() {  
    var isSelected = jQuery(this).hasClass('selected');     
    jQuery('#nextButton').prop('disabled', !isSelected);
  }); 
});


function bookingform_ajaxrequest(submission, formid, token,card,stripe,isSubmitting) {
  var booking_date = jQuery('input[name="booking_date"]').val();
  var timeslot = "";
  var slotcapacity = "";
  jQuery('.saab_timeslot').each(function() {
    if (jQuery(this).hasClass('selected')) {
      timeslot = jQuery(this).find('input[name="booking_slots"]').val();	
      slotcapacity = jQuery(this).find('.saab-tooltip-text').attr('data-seats');
    } 
  });
  bookedseats = jQuery('input[name="saabslotcapacity"]').val();
  jQuery.ajax({
    url:  myAjax.ajaxurl,
    type : 'post',
    data: { 
    action: "saab_booking_form_submission",
    form_data: submission,
    fid:formid,
    timeslot:timeslot,
    booking_date:booking_date,
    bookedseats:bookedseats,
    slotcapacity:slotcapacity,
    token:token
    },
    success: function (response) {
      if (response.success) {	
        jQuery('#formio_res_msg').hide();
        var confirmationType = response.data.confirmation;
        var message = response.data.message;
        var redirectPage = response.data.redirect_page;
        var wpEditorValue = response.data.wp_editor_value;
        var redirectUrl = response.data.redirect_url;
        var payment_enabled = response.data.payment_enabled;
        var status = response.data.status;
        var fpid= response.data.fpid;
        
        function handleConfirmation() {

          if (confirmationType === 'redirect_text') {
            // Replace div content with wpEditorValue or message
            jQuery('#calender_reload').html(wpEditorValue).fadeIn().delay(3000);
          } else if (confirmationType === 'redirect_to') {
            jQuery('#calender_reload').html('<p>' + message + '</p>');
            setTimeout(function() {
              window.location.href = redirectUrl;
            }, 3000); 
          } else if (confirmationType === 'redirect_page') {
            jQuery('#calender_reload').html('<p>' + message + '</p>');
            setTimeout(function() {
              window.location.href = redirectUrl;
            }, 3000);
          } else if(confirmationType === ''){
            // jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
            jQuery('#calender_reload').html(response.data.message);
          }else if(redirectPage == 'null' && redirectPage == 'null'){
            jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
          }
          else {
            jQuery('#calender_reload').html(response.data.message).fadeIn().delay(3000).fadeOut();
          }
          jQuery('#nextButton').css('display', 'none');
          jQuery('#backButton').css('display', 'none');
          jQuery("button[name='data[submit]'] i.fa.fa-refresh.fa-spin.button-icon-right").hide();
        } 
        if (payment_enabled == 1 && status !== 'waiting') {
          var clientSecret = response.data.client_secret;
          stripe.confirmCardPayment(clientSecret, {
              payment_method: {
                  card: card,
              },
          }).then(function(result) {
              var paymentStatus = result.paymentIntent.status;        
              if (result.error) {
                  isSubmitting = false;
                  jQuery('#formio_res_msg').html(result.error.message);
                  // console.error(result.error.message);
              } else {
                var args = '';      
                  // console.log("Payment success");
                  updatePaymentStatus(clientSecret,fpid,args,paymentStatus);
                  saab_send_notification(response.data.mail_response,fpid);
                  handleConfirmation();
              }
          });
        } else {          
            saab_send_notification(response.data.mail_response,fpid);
            handleConfirmation();
        }
      }else {
        // console.log(response.data.message);
        var errorMessage = response.data.error;
        isSubmitting = false; 
        jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(5000).fadeOut();
      }
    }
  });
  return false;
}

function makeAjaxRequest(submission, formid, token,card,stripe,isSubmitting) {
  // console.log("testfn");
  var form_id = parseInt(formid);
  isSubmitting = true;			
  jQuery.ajax({
    url: myAjax.ajaxurl,
    type : 'post',
    data: { 
    action: "saab_save_form_submission",
    form_data: submission,
    fid:form_id,
    token:token,
    },
    success: function (response) {
      if (response.success) {
        isSubmitting = false;
        jQuery("i.fa.fa-refresh.fa-spin.button-icon-right").hide();
        var confirmationType = response.data.confirmation;
        var message = response.data.message;
        var redirectPage = response.data.redirect_page;
        var wpEditorValue = response.data.wp_editor_value;
        var redirectUrl = response.data.redirect_url;
        var payment_enabled = response.data.payment_enabled;
        var fpid= response.data.fpid;
        var args = '';
        // console.log(payment_enabled);
        function handleConfirmation() {

            if (confirmationType === 'redirect_text') {
                jQuery('#formio').hide();
                jQuery('#formio_res_msg').html(wpEditorValue);
            } else if (confirmationType === 'redirect_url' || confirmationType === 'redirect_page') {
                jQuery('#formio').html('<p>' + message + '</p>');
                setTimeout(function() {
                    window.location.href = redirectUrl;
                }, 3000);
            } else if (confirmationType === '') {
                jQuery('#formio').html(message);
            } else if (redirectPage === 'null' && redirectPage === 'null') {
                jQuery('#formio').html(response.data.message).fadeIn().delay(3000).fadeOut();
            } else {
                jQuery('#formio').html(message).fadeIn().delay(3000).fadeOut();
            }
        }
        if (payment_enabled == 1) {
          var clientSecret = response.data.client_secret;
          stripe.confirmCardPayment(clientSecret, {
              payment_method: {
                  card: card,
              },
          }).then(function(result) {
            var paymentStatus = result.paymentIntent.status;        
              if (result.error) {
                  isSubmitting = false;
                  jQuery('#formio_res_msg').html(result.error.message);
                  // console.error(result.error.message);
              } else {
                  // console.log("Payment success");
                  updatePaymentStatus(clientSecret,fpid,args,paymentStatus);
                  saab_send_notification(response.data.mail_response,fpid);
                  handleConfirmation();
              }
          });
        } else {          
            saab_send_notification(response.data.mail_response,fpid);
            handleConfirmation();
        }
       
      } else {
        isSubmitting = false;
        // console.log(response.data.message);
        var errorMessage = response.data.error;        
        jQuery('#formio_res_msg').html(response.data.message).fadeIn().delay(3000).fadeOut();
        
      }
    },

  });
  return false;
}


jQuery(document).ready(function($) {
 
  jQuery(document).on('click', '#saab_addtocal2', function(event) {
     event.preventDefault(); // Prevent default link behavior
    //  alert("test");
     var form_id = jQuery(this).data('if');
     var created_post_id = jQuery(this).data('cp');
     jQuery.ajax({
         url: myAjax.ajaxurl,
         method: 'POST',
         data: {
             action: 'saab_add_event_to_calender', // This is a custom action identifier
             form_id: form_id,
             post_id: created_post_id
         },
         success: function(response) {
             // Handle the response from the server, e.g., display a success message
             // console.log('Event added:', response);
         },
         error: function(error) {
             // Handle errors, e.g., display an error message
             // console.error('Error:', error);
         }
     });
 });
});

				// JavaScript function to handle the response for both scenarios
				function handleFormResponse(response) {
					if (response.success) {
						var confirmationType = response.data.confirmation;
						var message = response.data.message;
						var wpEditorValue = response.data.wp_editor_value;
						var redirectUrl = response.data.redirect_url;
				
						if (confirmationType === 'redirect_text') {
							jQuery('#formio').hide();
							jQuery('#formio_res_msg').html(wpEditorValue);
						} else if (confirmationType === 'redirect_to' || confirmationType === 'redirect_page') {
							jQuery('#formio_res_msg').html('<p>' + message + '</p>');
							setTimeout(function () {
								window.location.href = redirectUrl;
							}, 3000);
						} else {
							jQuery('#formio_res_msg').html(message).fadeIn().delay(3000).fadeOut();
						}
						jQuery("button[name='data[submit]'] i.fa.fa-refresh.fa-spin.button-icon-right").hide();
					} else {
						var errorMessage = response.data.error;
						jQuery('#formio_res_msg').html(errorMessage).fadeIn().delay(3000).fadeOut();
					}
				}