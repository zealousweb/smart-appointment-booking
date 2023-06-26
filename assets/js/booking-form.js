jQuery(document).ready(function() {
    jQuery("#custom-meta-box-tabs").tabs();
}); 
//Url Link hide and show
jQuery(document).ready(function() {
    // Hide/show container of field Appointment type Tab1 based on radio button selection
    jQuery('input[name="appointment_type"]').on('change', function() {
        // alert("test");
      var selectedOption = jQuery(this).val();
      if (selectedOption === 'virtual') {
        jQuery('.vlink-container').removeClass('hidden');
      } else {
        jQuery('.vlink-container').addClass('hidden');
      }
    });
  });
//break field
jQuery(document).ready(function($) {
        
    //break field Tab 1
    var $container = $('#break_time_container');
    var $addTimeBtn = $('#add-break-time');

    // Add Time Field
    $addTimeBtn.click(function() {
        var $repeaterRow = $('.break-repeater-row:first').clone(); // Clone the first repeater row
        $repeaterRow.find('input').val(''); // Clear the input values of the cloned repeater row
        $repeaterRow.find('.brk-remove-time').show(); // Show the remove button for the cloned repeater row
        $repeaterRow.appendTo($container); // Append the cloned repeater row to the container
    });

    // Remove Time Field
    $container.on('click', '.brk-remove-time', function() {
        $(this).closest('.break-repeater-row').remove(); // Remove the corresponding repeater row
    });

    // Hide the Remove button for the initial row
    $container.find('.break-repeater-row:first .brk-remove-time').hide();
});
//Holiday Repeater Field
jQuery(document).ready(function($) {
    // Add date functionality
    $(document).on('click', '.add-holidate', function() {
        var dateFieldHTML = '<div class="holidate-field">' +
            '<input type="date" name="holidays[]" value="">' +
            '<button type="button" class="remove-holidate">Remove Holiday</button>' +
            '</div>';
        $('.holiday-repeater').append(dateFieldHTML);
    });

    // Remove date functionality
    $(document).on('click', '.remove-holidate', function() {
        $(this).closest('.holidate-field').remove();
    });
});
//timeslots break
jQuery(document).ready(function($) {
    // Hide/show container based on repeat dropdown selection
    $('#repeat_field').on('change', function() {
      var selectedOption = $(this).val();
      if (selectedOption === 'advanced') {
        $('.repeater-container').show();
      } else {
        $('.repeater-container').hide();
      }
    });
    
  });
  
jQuery(document).ready(function($) {
   
    // Show/hide certain weekdays fields
    $('#recurring_type').on('change', function() {
        
        var selectedOption = $(this).val();
        if (selectedOption === 'certain_weekdays') {
            $('#certain_weekdays_fields').show();
        } else {
            $('#certain_weekdays_fields').hide();
        }
        if (selectedOption === 'advanced') {
           
            jQuery('#advance-meta-box').show();
            jQuery('.end_repeats_label').hide();
            jQuery('.end_repeats_options').hide();
        } else {
          
            jQuery('#advance-meta-box').hide();
            jQuery('.end_repeats_label').show();
            jQuery('.end_repeats_options').show();
        }
       
    }).trigger('change');

    // Show/hide custom date fields
    $('#recurring_type').on('change', function() {
        var selectedOption = $(this).val();
        if (selectedOption === 'custom_date') {
            $('#custom_date_fields').show();
        } else {
            $('#custom_date_fields').hide();
        }
    }).trigger('change');

    // Add Custom Date button click event
    $('#add-custom-date').on('click', function(e) {
        e.preventDefault();

        // Clone the custom date and time input fields and remove button
        var $customDate = $('.custom-date:last').clone();
        $customDate.find('.remove-custom-date').removeClass('remove-custom-date').addClass('remove-custom-date-new').text('Remove');

        // Clear the values of the cloned custom date and time input fields
        $customDate.find('.custom-date-input').val('');
        $customDate.find('.custom-time-input').val('');

        // Append the cloned custom date and time fields to the container
        $('#custom_dates_container').append($customDate);
    });

    // Remove Custom Date button click event
    $(document).on('click', '.remove-custom-date', function(e) {
        e.preventDefault();

        // Remove the corresponding custom date and time fields
        $(this).parent('.custom-date').remove();
    });
});

jQuery(document).ready(function($) {
   
        jQuery("#enable_recurring_apt_i").click(function () {
            
            if (jQuery(this).is(":checked")) {
                jQuery("#recurring_result").show();
            } else {
                jQuery("#recurring_result").hide();
            }
        });
});
//link validation
jQuery(document).ready(function() {
    // Client-side validation for the virtual link field
    const virtualLinkField = jQuery('input[name="virtual_link"]');
    const validationError = jQuery('.validation-error');
    virtualLinkField.on('input', function() {
        if (!this.validity.valid) {
            validationError.css('display', 'block');
        } else {
            validationError.css('display', 'none');
        }
    });
});
jQuery(document).ready(function($) {
    // Add row
    $('#add-row').click(function() {
        var intial_index = $('.repeater-row').length;
        var index = intial_index + 1 ;
        var row = '<div class="repeater-row">' +
            '<label for="advance_date_' + index + '">Advance Date:</label>' +
            '<input type="date" id="advance_date_' + index + '" name="advancedata[' + index + '][advance_date]" value="">' +
            '<div id="timeslot-repeater-' + index + '" class="timeslot-repeater timeslot-container">' +
            '<label>Advance Timeslots:</label>' +
            '<div class="timeslot-row">' +
            '<label>Start Time:</label>' +
            '<input type="time" name="advancedata[' + index + '][advance_timeslot][0][start_time]" value="">' +
            '<label>End Time:</label>' +
            '<input type="time" name="advancedata[' + index + '][advance_timeslot][0][end_time]" value="">' +
            '<label>Bookings:</label>' +
            '<input type="number" name="advancedata[' + index + '][advance_timeslot][0][bookings]" value="">' +
            '<button type="button" class="remove-timeslot">Remove Timeslot</button>' +
            '</div>' +
            '<button type="button" class="add-timeslot">Add Timeslot</button>' +
            '</div>' +
            '<button type="button" class="remove-row">Remove Date</button>' +
            '</div>';

        $('#advance-meta-box').append(row);
    });

    // Add timeslot
    $(document).on('click', '.add-timeslot', function() {
        var $repeaterRow = $(this).closest('.repeater-row');
        var $timeslotContainer = $repeaterRow.find('.timeslot-container');
        var repeaterIndex = $repeaterRow.index();

        var timeslotRow = '<div class="timeslot-row">' +
            '<label>Start Time:</label>' +
            '<input type="time" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][start_time]" value="">' +
            '<label>End Time:</label>' +
            '<input type="time" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][end_time]" value="">' +
            '<label>Bookings:</label>' +
            '<input type="number" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][bookings]" value="">' +
            '<button type="button" class="remove-timeslot">Remove Timeslot</button>' +
            '</div>';

        $timeslotContainer.append(timeslotRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('.repeater-row').remove();
    });

    // Remove timeslot
    $(document).on('click', '.remove-timeslot', function() {
        $(this).closest('.timeslot-row').remove();
    });
});
jQuery(document).ready(function($) {
    $('input[name="confirmation"]').change(function() {
        // alert("test");
        var selectedOption = $(this).val();
        // alert(selectedOption);
        $('.redirectto_main').addClass('hidden');
        $('.redirectto_main.' + selectedOption).removeClass('hidden');
    });
});
jQuery(document).ready(function($) {
    // Search functionality
    $('#redirectpage-search').on('keyup', function() {
        var searchValue = $(this).val().toLowerCase();

        $('#page-dropdown option').each(function() {
            var optionText = $(this).text().toLowerCase();

            if (optionText.indexOf(searchValue) > -1) {
                $(this).prop('hidden', false);
            } else {
                $(this).prop('hidden', true);
            }
        });
    });
});
// show error if url is entered wrong
// jQuery(document).ready(function($) {
//     // URL validation
//     $('#redirect-url').on('blur', function() {
//         var url = $(this).val();

//         if (url !== '') {
//             var pattern = /^(https?:\/\/)?[\w.-]+\.[a-zA-Z]{2,7}\/?$/;
//             var isValidUrl = pattern.test(url);

//             if (!isValidUrl) {
//                 $(this).addClass('error');
//                 $(this).next('.redirecturl-error').show();
//             } else {
//                 $(this).removeClass('error');
//                 $(this).next('.redirecturl-error').hide();
//             }
//         }
//     });
// });
jQuery(document).ready(function($) {
    jQuery(document).on('click', '#preview_timeslot', function() {        
        var post = $(this).attr("pid");
        var url=window.location.href;
        var arr=url.split('wp-admin')[0];
        var link=arr+'wp-admin/admin-ajax.php';
        jQuery.ajax({
            url: link,
            type: "POST",
            data: { 
            action: "zfb_preiveiw_timeslot",
            post_id: post,  // Current post ID
            },
            success: function (response) {
                var start_time = response.data.output;
                jQuery('#preview_output').html('<p>' + start_time + '</p>');               
                console.log(start_time);
                // console.log(end_time);
                // console.log(break_times);
                // console.log(gap_minutes);
                // console.log(duration_minutes);

                // if (confirmationType === 'redirect_text') {
                //     // Replace div content with wpEditorValue or message
                //     jQuery('#calender_reload').html(wpEditorValue);
                // } else if (confirmationType === 'redirect_to') {
                //     jQuery('#calender_reload').html('<p>' + message + '</p>');
                //     // Replace div content with message
                //     jQuery('#preview_output').html('<p>' + message + '</p>');
                // } else if (confirmationType === 'redirect_page') {
                    
                //     jQuery('#calender_reload').html('<p>' + message + '</p>');
                //     setTimeout(function() {
                //         window.location.href = pageUrl;
                //     }, 3000); // Redirect after 3 seconds (adjust as needed)
                // }
            }
            
        });

    });
});
jQuery(document).ready(function($) {
    // Add Timeslot
    $('.add-breaktimeslot').click(function() {
        var index = $('.breaktimeslot-repeater .breaktimeslot').length;
        var timeslot = `
          <div class="breaktimeslot">
            <label>Start Time:</label>
            <input type="time" name="breaktimeslots[${index}][start_time]" value="">
            <br>
            <label>End Time:</label>
            <input type="time" name="breaktimeslots[${index}][end_time]" value="">
            <button type="button" class="remove-timeslot">Remove Timeslot</button>
          </div>
        `;
        $('.breaktimeslot-repeater').append(timeslot);
      });

      // Remove Timeslot
      $(document).on('click', '.remove-breaktimeslot', function() {
        $(this).closest('.breaktimeslot').remove();
      });
  });
//   jQuery(document).ready(function() {
//     jQuery(document).on('submit', '#notifyform, #notifyformadd', function(event) {
//         event.preventDefault();

//         var form = jQuery(this).serialize();
//         var formData = new FormData();
//         formData.append('action', 'zfb_save_new_notification');
//         formData.append('notification_data', form);

//         jQuery.ajax({
//             type: 'POST',
//             url: ajaxurl,
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(response) {
//                 console.log(response);
//                 // Perform any desired actions upon successful submission
//             }
//         });
//     });
// });

jQuery(document).ready(function() {
    jQuery(document).on('submit', '#notifyformadd', function(event) {    
        event.preventDefault();    
        var form=jQuery('#notifyformadd').serialize();
        var formData=new FormData();
        formData.append('action','zfb_save_new_notification');
        formData.append('notification_data',form);
        var modalId = jQuery(this).data('target');
        var modal = jQuery(modalId); 

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data:formData,
            processData:false,//off other action only run this event
		    contentType:false,        
            success: function (response) {
                 console.log(response);
                 jQuery('#notifytable').load(location.href + ' #notifytable');
                 modal.modal('hide');
            }
            
        });

    });
});
jQuery(document).ready(function() {
    jQuery(document).on('submit', '#notifyform', function(event) {    
        event.preventDefault();    
        var form=jQuery('#notifyform').serialize();
        var formData=new FormData();
        formData.append('action','zfb_save_new_notification');
        formData.append('notification_data',form);
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data:formData,
            processData:false,//off other action only run this event
		    contentType:false,        
            success: function (response) {
                jQuery('#notifytable').load(location.href + ' #notifytable');
                 console.log(response);
                // jQuery('#closemodal').click();
                
            }
            
        });

    });
});
jQuery(document).ready(function($) {
    // Delete button click event
    $('#deletenotify').on('click', function() {

        var post_id = jQuery('#post_id').val();
        // Get the checked checkboxes
        var checkedItems = $('.child-checkall:checked');
        var indexesToDelete = [];

        // Extract the indexes of checked checkboxes
        checkedItems.each(function() {
            indexesToDelete.push($(this).val());
        });
        console.log(indexesToDelete);
        // Perform AJAX request to delete the indexes
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'delete_notification_indexes',
                indexes: indexesToDelete,
                post_id: post_id,
            },
            success: function(response) {
                // Handle the success response here
                console.log(response);
                $('#notifytable').load(location.href + ' #notifytable');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle the error here
                console.log(errorThrown);
            }
        });
    });
});
jQuery(document).ready(function($) {
    // Check all checkbox click event
    $('#main-check-all').on('click', function() {
        // Get the checked status of the main checkbox
        var isChecked = $(this).prop('checked');
        
        // Set the checked status of all child checkboxes accordingly
        $('.child-checkall').prop('checked', isChecked);
    });
});
jQuery(document).on('click', '.toggle-notification', function() {
    var row = jQuery(this).closest('tr');
    var index = row.data('index');
    var postID = jQuery('#post_id').val();
    var currentState = row.find('.notification-state').text();
    var newState = currentState === 'Enabled' ? 'Disabled' : 'Enabled';
    
    // Send AJAX request to update the notification state
    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'zfb_update_notification_state',
            index: index,
            post_id: postID,
            state: newState,
        },
        success: function(response) {
            // Update the row with the new state
            row.find('.notification-state').text(newState);
            
            // Add/remove classes for styling
            row.toggleClass('enabled-notification', newState === 'Enabled');
            row.toggleClass('disabled-notification', newState === 'Disabled');
        },
        error: function(xhr, status, error) {
            console.log(error);
        }
    });
});
