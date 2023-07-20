// function showAlertWithFadeOut(message, duration) {
//     var alert = jQuery('<div class="alert alert-success">' + message + '</div>');
//     alert.css({
//       'position': 'fixed',
//       'top': '10px',
//       'left': '50%',
//       'transform': 'translateX(-50%)',
//       'padding': '10px',
//       'background-color': '#4CAF50',
//       'color': '#FFFFFF',
//       'font-size': '16px',
//       'border-radius': '5px',
//       'opacity': '0',
//       'transition': 'opacity 0.5s ease-in-out'
//     });
  
//     jQuery('body').append(alert);
//     setTimeout(function() {
//       alert.css('opacity', '1');
//     }, 100);
  
//     setTimeout(function() {
//       alert.css('opacity', '0');
//       setTimeout(function() {
//         alert.remove();
//       }, 500);
//     }, duration);
//   }
  

function front_getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }
  
  jQuery(document).ready(function() {
    var searchParams_booking = front_getQueryParam("booking_id");
    var searchParams_status = front_getQueryParam("status");
    console.log(searchParams_booking + searchParams_status);
  
    if (searchParams_booking && searchParams_status === "cancel") {
      jQuery.ajax({
        url: myAjax.ajaxurl,
        type: 'post',
        data: {
          action: "zfb_cancel_booking",
          bookingId: searchParams_booking,
          bookingstatus: searchParams_status,
          status: 'check',
        },
        success: function(response) {
          console.log(response);
          if (response.error === 'false') {
            var confirmationType = response.status;
            
            if (confirmationType === 'readytoconfirm') {
              var confirmed = confirm(`Do you want to proceed to cancel the booking?`);
              if (confirmed) {
                console.log("confirmed");
                jQuery.ajax({
                    url: myAjax.ajaxurl,
                    type: 'post',
                    data: {
                      action: "zfb_cancel_booking",
                      bookingId: searchParams_booking,
                      bookingstatus: searchParams_status,
                      status: 'confirm',
                    },
                    success: function(response) {
                  
                      if (response.error === 'false') {
                        var message = response.message;
                        alert(message);
                      
                      } else {
                        var errorMessage = response.message;
                        alert(errorMessage);
                      }
                    },
                    error: function(xhr, status, error) {
                      console.log(xhr.responseText);
                      
                    }
                  });
              }
            }
          }else{
            alert(response.message);
          }
        }
      });
  
    } else {
      console.log('Invalid URL.');
    }
  });
  
  function showAlertWithFadeOut(message, duration) {
    var alert = jQuery('<div class="alert alert-success">' + message + '</div>');
    jQuery('body').append(alert);
    setTimeout(function() {
      alert.fadeOut(500, function() {
        alert.remove();
      });
    }, duration);
  }
  jQuery(document).ready(function($) {
    $('.booking-cancellation-buttons .btn-yes').on('click', function() {
        var bookingId = front_getQueryParam("booking_id");
        var bookingstatus = front_getQueryParam("status");

        if (bookingId && bookingstatus === 'cancel') {
            $.ajax({
                url: myAjax.ajaxurl,
                type: 'post',
                data: {
                    action: 'cancel_booking_shortcode',
                    bookingId: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        $('.booking-cancellation-card').html('<p>Your booking has been cancelled.</p>');
                    } else {
                        $('#msg_booking_cancel').html('<p>Failed to cancel the booking. Please try again.</p>');
                        
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        } else {
            $('#msg_booking_cancel').html('<p>Unable to cancel the booking.</p>');
        }
    });
});
