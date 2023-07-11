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
        // var dateFieldHTML = '<div class="holidate-field">' +
        //     '<input type="date" name="holidays[]" value="">' +
        //     '<button type="button" class="remove-holidate">Remove Holiday</button>' +
        //     '</div>';
        var dateFieldHTML = `
            <div class="form-row holidate-field">
                <div class="form-group col-md-2">
                    <input type="date" class="form-control" name="holidays[]" value="">
                </div>
                <div class="form-group col-md-2"> 
                    <svg class="remove-holidate" xmlns="http://www.w3.org/2000/svg" width="16" 
                        height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                    </svg>
                </div>
            </div>
          `;
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
        var row = '<div class="repeater-row border m-0 mb-2 p-3 row">' +
            '<div class="form-group col-md-3">' +
            '<label class="h6" for="advance_date_' + index + '">Advance Date:</label>' +
            '<input type="date" class="form-control" id="advance_date_' + index + '" name="advancedata[' + index + '][advance_date]" value="">' +
            '</div>' +
            '<div id="timeslot-repeater-' + index + '" class="timeslot-repeater timeslot-container  col-md-9">' +
            '<div class="add-timeslot" id="add_timeslot_m">'+
            '<label class="h6 ml-1">Add Timeslots </label>'+
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">'+
            '<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>'+
            '<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>'+
            '</svg>'+
            '</div>'+
            '<div class="form-row timeslot-row ">'+
            '<div class="form-group col-md-3">'+
            '<label>Start Time:</label>' +
            '<input type="time" class="form-control" name="advancedata[' + index + '][advance_timeslot][0][start_time]" value="">' +
            '</div>'+
            '<div class="form-group col-md-3">'+
            '<label>End Time:</label>' +
            '<input type="time" class="form-control" name="advancedata[' + index + '][advance_timeslot][0][end_time]" value="">' +
            '</div>'+
            '<div class="form-group col-md-3"> '+
            '<label>Bookings:</label>' +
            '<input type="number" class="form-control" name="advancedata[' + index + '][advance_timeslot][0][bookings]" value="">' +
            '</div>'+
            '<div class="form-group col-2 remove-timeslot-wrapper">'+
            '<svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" '+
            'height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">'+
            '<path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>'+
            '</svg>' +
            '</div>'+
            '</div>' +
            '</div>' +
            '<button type="button" class="remove-row btn btn-danger">Remove Date</button>' +
            '</div>';

        jQuery('#advance-meta-box').append(row);
    });

    // Add timeslot
    $(document).on('click', '.add-timeslot', function() {
        var $repeaterRow = $(this).closest('.repeater-row');
        var $timeslotContainer = $repeaterRow.find('.timeslot-container');
        var repeaterIndex = $repeaterRow.index();

        var timeslotRow = '<div class="form-row timeslot-row ">'+
            '<div class="form-group col-md-3">'+
            '<label>Start Time:</label>' +
            '<input type="time"  class="form-control"  name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][start_time]" value="">' +
            '</div>'+
            '<div class="form-group col-md-3">'+
            '<label>End Time:</label>' +
            '<input type="time"  class="form-control"  name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][end_time]" value="">' +
            '</div>'+
            '<div class="form-group col-md-3"> '+
            '<label>Bookings:</label>' +
            '<input type="number"  class="form-control" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + $timeslotContainer.find('.timeslot-row').length + '][bookings]" value="">' +
            '</div>'+
            '<div class="form-group col-2 remove-timeslot-wrapper">'+
            '<svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" '+
            'height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">'+
            '<path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>'+
            '</svg>' +
            '</div>'+
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

        $('#redirectpage-dropdown option').each(function() {
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
            post_id: post,
            },
            success: function (response) {
                var timeSlotsHTML = response.data.output;
                jQuery("div.generatetimeslot").remove();
                jQuery('.generatetimeslot-repeater').append(timeSlotsHTML);
               
            }
            
        });

    });
});
jQuery(document).ready(function($) {
    // Add Timeslot
    $(document).on('click', '.add-generatetimeslot', function() {
      var index = $('.generatetimeslot-repeater .generatetimeslot').length;
      var timeslot = `
          <div class="form-row timeslot-row generatetimeslot">
              <div class="form-group col-md-3">
                  <label>Start Time:</label>
                  <input type="time" class="form-control" name="generatetimeslot[${index}][start_time]" value="">
              </div>
              <div class="form-group col-md-3">
                  <label>End Time:</label>
                  <input type="time" class="form-control" name="generatetimeslot[${index}][end_time]" value="">                            
              </div>
              <div class="form-group col-2 remove-generatetimeslot">
                  <svg class="remove-generatetimeslot" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                      <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                  </svg>
              </div>
          </div>
      `;
      $('.generatetimeslot-repeater').append(timeslot);
    });
  
    // Remove Timeslot
    $(document).on('click', '.remove-generatetimeslot', function() {
      $(this).closest('.generatetimeslot').remove();
    });
  });
  
jQuery(document).ready(function($) {
    // Add Timeslot
    $('.add-breaktimeslot').click(function() {
      var index = $('.breaktimeslot-repeater .breaktimeslot').length;
      var timeslot = `
          <div class="breaktimeslot">
              <label  class="h6">Start Time:</label>
              <input type="time" name="breaktimeslots[${index}][start_time]" value="">
          
              <label  class="h6">End Time:</label>
              <input type="time" name="breaktimeslots[${index}][end_time]" value="">                           
              <button class="remove-breaktimeslot rm-brktime-slot">
                  <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                      <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                      <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                  </svg>
              </button>
          </div>
      `;
      $('.breaktimeslot-repeater').append(timeslot);
    });
  
    // Remove Timeslot
    $(document).on('click', '.remove-breaktimeslot', function() {
      $(this).closest('.breaktimeslot').remove();
    });
  });
// Function to retrieve the value of a query parameter from the URL
function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}
const pageParam = getQueryParam("page");
if (pageParam === "notification-settings") {
    
    jQuery(document).ready(function() {
        jQuery(document).on('submit', '.notifyform', function(event) {   
           
            event.preventDefault();    
            var form= jQuery(this).serialize();
            var index =  jQuery(this).data('id');
            // console.log(index);
            var formData=new FormData();
            formData.append('action','zfb_save_new_notification');
            formData.append('notification_data',form);
            formData.append('editnotify', index);
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
            // alert("test");
            // Get the checked status of the main checkbox
            var isChecked = $(this).prop('checked');
            
            // Set the checked status of all child checkboxes accordingly
            $('.child-checkall').prop('checked', isChecked);
        });
    });
    jQuery(document).ready(function($) {
        $(document).on('click', '.enable-btn', function() {
            var post_id = $('#post_id').val();
            var notifyTable = $('#notifytable');
            var notificationId = $(this).data('notification-id');
            var notificationState = $(this).data('notification-state');
            var newState = (notificationState === 'enabled') ? 'disabled' : 'enabled';

            // AJAX request to update the notification status
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zfb_update_notification_state',
                    notification_id: notificationId,
                    new_state: newState,
                    post_id: post_id
                },
                // success: function(response) {
                //     if (response.success) {                  
                //         // $('.enable-btn[data-notification-id="' + notificationId + '"]').text(newState === 'enabled' ? 'Enabled' : 'Disabled');
                //         // $('.enable-btn[data-notification-id="' + notificationId + '"]').data('notification-state', newState);
                //     } else {
                //         // Display an error message
                //         console.log('Failed to update notification status. Message: ' + response.message);
                //     }
                //     //refresh ajax content after 
                //     $('#notifytable').load(location.href + ' #notifytable');
                // },
                success: function(response) {
                    if (response.success) {
                    // Update the button text and data attribute
                    var enableBtn = $('.enable-btn[data-notification-id="' + notificationId + '"]');
                    enableBtn.text(newState === 'enabled' ? 'Enable' : 'Disable');
                    enableBtn.data('notification-state', newState);
                
                    // Reload the table content
                    $('.datatable').DataTable().ajax.reload();
                    } else {
                    // Display an error message
                    console.log('Failed to update notification status. Message: ' + response.message);
                    }
                },
                
                error: function(xhr, textStatus, errorThrown) {
                    // Display an error message
                    console.log('AJAX request failed: ' + errorThrown);
                }
            });
        });
    });

    jQuery(document).ready(function() {
        var notifytable = jQuery('#notifytable').DataTable();({
        dom: 'Bfrtip',
        paging: true, // Enable pagination
        pageLength: 1,
        searching: true,
        ordering: true, // Enable column sorting
        aaSorting: [[0, 'asc'] , [ 1, "asc" ]],
        columnDefs: [
            {
                // targets: [4], // Column index for which sorting should be disabled
                // orderable: false
            },
            {
                // targets: [1,2, 3, 4], // Column indexes for which width should be set
                // width: '200px'
            }
            // Add more columnDefs to set widths for other columns if needed
        ],
        responsive: true
        
        });
        jQuery("#searchbox").keyup(function() {
            //table.fnFilter();
            notifytable.search(this.value).draw();
        }); 
    });

    jQuery(document).on('submit', '#usermap_form', function(event) {    
        event.preventDefault();    
        var form=jQuery('#usermap_form').serialize();
        var formData=new FormData();
        formData.append('action','zfb_save_user_mapping');
        formData.append('zfbuser_mapping',form);
      
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data:formData,
            processData:false,//off other action only run this event
            contentType:false,        
            success: function (response) {
                console.log(response);
                jQuery('#map_success').html(response.message).fadeIn().delay(2000).fadeOut();              
            }
        });

    });

    jQuery(document).on('submit', '#confirm_form', function(event) {   
        event.preventDefault();    
        var form=jQuery('#confirm_form').serialize();
        var formData=new FormData();
        formData.append('action','zfb_save_confirmation');
        formData.append('confirmation_data',form);
      
        jQuery.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data:formData,
            processData:false,//off other action only run this event
            contentType:false,        
            success: function (response) {
                 console.log(response);
                jQuery('#confirm_msg').html(response.message).fadeIn().delay(2000).fadeOut();              
            }
        });

    });

    jQuery(document).ready(function($) {
        $('#send_notification_button').on('click', function() {
            var status = $('#custom_status').val();
            sendNotification(status);
        });
    
        function sendNotification(status) {
            var data = {
                action: 'send_notification',
                status: status
            };
    
            $.post(ajaxurl, data, function(response) {
                console.log(response); // Optional: Log the response
                alert('Notification sent successfully.'); // Optional: Display a success message
            });
        }
    });
    
  
}