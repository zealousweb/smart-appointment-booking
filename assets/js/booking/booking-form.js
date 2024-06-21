jQuery(document).ready(function($) {
    jQuery("#custom-meta-box-tabs").tabs();
}); 
//Url Link hide and show
jQuery(document).ready(function() {
   // Hide/show container of field Appointment type Tab1 based on radio button selection
   jQuery('input[name="appointment_type"]').on('change', function() {
      
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
   var $container = jQuery('#break_time_container');
   var $addTimeBtn = jQuery('#add-break-time');

   // Add Time Field
   $addTimeBtn.click(function() {
       var $repeaterRow = jQuery('.break-repeater-row:first').clone(); // Clone the first repeater row
       $repeaterRow.find('input').val(''); // Clear the input values of the cloned repeater row
       $repeaterRow.find('.brk-remove-time').show(); // Show the remove button for the cloned repeater row
       $repeaterRow.appendTo($container); // Append the cloned repeater row to the container
   });

   // Remove Time Field
   $container.on('click', '.brk-remove-time', function() {
       jQuery(this).closest('.break-repeater-row').remove(); // Remove the corresponding repeater row
   });

   // Hide the Remove button for the initial row
   $container.find('.break-repeater-row:first .brk-remove-time').hide();
});
//Holiday Repeater Field
jQuery(document).ready(function($) {
   // Add date functionality
   jQuery(document).on('click', '.add-holidate', function() {
   
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
       jQuery('.holiday-repeater').append(dateFieldHTML);
   });

   // Remove date functionality
   jQuery(document).on('click', '.remove-holidate', function() {
       jQuery(this).closest('.holidate-field').remove();
   });
});
//timeslots break
jQuery(document).ready(function($) {
   // Hide/show container based on repeat dropdown selection
   jQuery('#repeat_field').on('change', function() {
     var selectedOption = jQuery(this).val();
     if (selectedOption === 'advanced') {
       jQuery('.repeater-container').show();
     } else {
       jQuery('.repeater-container').hide();
     }
   });
 });
 
jQuery(document).ready(function($) {
  
   // Show/hide certain weekdays fields
   jQuery('#recurring_type').on('change', function() {
       
       var selectedOption = jQuery(this).val();
       if (selectedOption === 'certain_weekdays') {
           jQuery('#certain_weekdays_fields').show();
       } else {
           jQuery('#certain_weekdays_fields').hide();
       }
       if (selectedOption === 'advanced') {
          
        //    jQuery('#advance-meta-box').show();
           jQuery('.end_repeats_label').hide();
           jQuery('.end_repeats_options').hide();
       } else {
         
        //    jQuery('#advance-meta-box').hide();
           jQuery('.end_repeats_label').show();
           jQuery('.end_repeats_options').show();
       }
      
   }).trigger('change');

   // Show/hide custom date fields
   jQuery('#recurring_type').on('change', function() {
       var selectedOption = jQuery(this).val();
       if (selectedOption === 'custom_date') {
           jQuery('#custom_date_fields').show();
       } else {
           jQuery('#custom_date_fields').hide();
       }
   }).trigger('change');

   // Add Custom Date button click event
   jQuery('#add-custom-date').on('click', function(e) {
       e.preventDefault();

       // Clone the custom date and time input fields and remove button
       var $customDate = jQuery('.custom-date:last').clone();
       $customDate.find('.remove-custom-date').removeClass('remove-custom-date').addClass('remove-custom-date-new').text('Remove');

       // Clear the values of the cloned custom date and time input fields
       $customDate.find('.custom-date-input').val('');
       $customDate.find('.custom-time-input').val('');

       // Append the cloned custom date and time fields to the container
       jQuery('#custom_dates_container').append($customDate);
   });

   // Remove Custom Date button click event
   jQuery(document).on('click', '.remove-custom-date', function(e) {
       e.preventDefault();

       // Remove the corresponding custom date and time fields
       jQuery(this).parent('.custom-date').remove();
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
jQuery(document).ready(function($) {
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
   jQuery('#add-row').click(function() {
       var intial_index = jQuery('.repeater-row').length;
       var index = intial_index ;
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
   jQuery(document).on('click', '.add-timeslot', function() {
       var repeaterRow = jQuery(this).closest('.repeater-row');
      
       var timeslotContainer = repeaterRow.find('.timeslot-container');
       var repeaterIndex = repeaterRow.index() - 1;
       var timeslotRows = timeslotContainer.find('.timeslot-row');
       var newTimeslotIndex = timeslotRows.length;
   
       var timeslotRow = '<div class="form-row timeslot-row">' +
           '<div class="form-group col-md-3">' +
           '<label>Start Time:</label>' +
           '<input type="time" class="form-control" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + newTimeslotIndex + '][start_time]" value="">' +
           '</div>' +
           '<div class="form-group col-md-3">' +
           '<label>End Time:</label>' +
           '<input type="time" class="form-control" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + newTimeslotIndex + '][end_time]" value="">' +
           '</div>' +
           '<div class="form-group col-md-3">' +
           '<label>Bookings:</label>' +
           '<input type="number" class="form-control" name="advancedata[' + repeaterIndex + '][advance_timeslot][' + newTimeslotIndex + '][bookings]" value="">' +
           '</div>' +
           '<div class="form-group col-2 remove-timeslot-wrapper">' +
           '<svg class="remove-timeslot" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">' +
           '<path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>' +
           '</svg>' +
           '</div>' +
           '</div>';

       timeslotContainer.append(timeslotRow);
   });

   // Remove row
   jQuery(document).on('click', '.remove-row', function() {
       jQuery(this).closest('.repeater-row').remove();
   });

   // Remove timeslot
   jQuery(document).on('click', '.remove-timeslot', function() {
       jQuery(this).closest('.timeslot-row').remove();
   });
});
jQuery(document).ready(function($) {
   jQuery('input[name="confirmation"]').change(function() {     
       var selectedOption = jQuery(this).val();      
       jQuery('.redirectto_main').addClass('hidden');
       jQuery('.redirectto_main.' + selectedOption).removeClass('hidden');
   });
});
jQuery(document).ready(function($) {
   // Search functionality
   jQuery('#redirectpage-search').on('keyup', function() {
       var searchValue = jQuery(this).val().toLowerCase();

       jQuery('#redirectpage-dropdown option').each(function() {
           var optionText = jQuery(this).text().toLowerCase();

           if (optionText.indexOf(searchValue) > -1) {
               jQuery(this).prop('hidden', false);
           } else {
               jQuery(this).prop('hidden', true);
           }
       });
   });
});

jQuery(document).ready(function($) {
   jQuery(document).on('click', '#preview_timeslot', function() {        
       var post = jQuery(this).attr("pid");
       var url=window.location.href;
       var arr=url.split('wp-admin')[0];
       var link=arr+'wp-admin/admin-ajax.php';
       var nonce = ajax_object.nonce;
       jQuery.ajax({
           url: link,
           type: "POST",
           data: { 
           action: "saab_preiveiw_timeslot",
           post_id: post,
           security: nonce, 
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
   jQuery(document).on('click', '.add-generatetimeslot', function() {
     var index = jQuery('.generatetimeslot-repeater .generatetimeslot').length;
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
             <button class="remove-generatetimeslot btn btn-danger">
                   <svg class="remove-generatetimeslot" xmlns="http://www.w3.org/2000/svg" width="16" 
                   height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                   <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                   </svg>
               </button>
             </div>
         </div>
     `;
     jQuery('.generatetimeslot-repeater').append(timeslot);
   });
 
   // Remove Timeslot
   jQuery(document).on('click', '.remove-generatetimeslot', function() {
     jQuery(this).closest('.generatetimeslot').remove();
   });
 });
 
jQuery(document).ready(function($) {
   // Add Timeslot
   jQuery('.add-breaktimeslot').click(function() {
     var index = jQuery('.breaktimeslot-repeater .breaktimeslot').length;
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
     jQuery('.breaktimeslot-repeater').append(timeslot);
   });
 
   // Remove Timeslot
   jQuery(document).on('click', '.remove-breaktimeslot', function() {
     jQuery(this).closest('.breaktimeslot').remove();
   });
 });

jQuery(document).ready(function($) {
   jQuery('#send_notification_button').on('click', function() {
     var status = jQuery('#manual_notification').attr('data-formid');
     jQuery('#publish').click();
    
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
            var nonce = ajax_object.nonce;
           event.preventDefault();    
           var form= jQuery(this).serialize();
           var index =  jQuery(this).data('id');
           var formData=new FormData();
           formData.append('action','saab_save_new_notification');
           formData.append('notification_data',form);
           formData.append('editnotify', index);
           formData.append('security', nonce);
           jQuery.ajax({
               type: 'POST',
               url: ajaxurl,
               data:formData,
               processData:false,
               contentType:false,        
               success: function (response) {
                   jQuery('.notifyform').trigger("reset");
                   jQuery('.close').trigger('click');                   
                   jQuery('#notifytable').load(location.href + ' #notifytable');         
               }
               
           });

       });
   });
   jQuery(document).ready(function($) {
       // Delete button click event
       jQuery('#deletenotify').on('click', function() {

           var post_id = jQuery('#post_id').val();
           // Get the checked checkboxes
           var checkedItems = jQuery('.child-checkall:checked');
           var indexesToDelete = [];

           // Extract the indexes of checked checkboxes
           checkedItems.each(function() {
               indexesToDelete.push(jQuery(this).val());
           });
           var nonce = ajax_object.nonce;
           // Perform AJAX request to delete the indexes
           $.ajax({
               type: 'POST',
               url: ajaxurl,
               data: {
                   action: 'delete_notification_indexes',
                   indexes: indexesToDelete,
                   post_id: post_id,
                   security: nonce, 
               },
               success: function(response) {
                   // Handle the success response here                   
                   jQuery('#notifytable').load(location.href + ' #notifytable');
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
       jQuery('#main-check-all').on('click', function() {
           // Get the checked status of the main checkbox
           var isChecked = jQuery(this).prop('checked');
           jQuery('.child-checkall').prop('checked', isChecked);
       });
   });
   jQuery(document).ready(function($) {
       jQuery(document).on('click', '.enable-btn', function() {
           var post_id = jQuery('#post_id').val();
           var notifyTable = jQuery('#notifytable');
           var notificationId = jQuery(this).data('notification-id');
           var notificationState = jQuery(this).data('notification-state');
           var newState = (notificationState === 'enabled') ? 'disabled' : 'enabled';
           var nonce = ajax_object.nonce;
           $.ajax({
               url: ajaxurl,
               type: 'POST',
               data: {
                   action: 'saab_update_notification_state',
                   notification_id: notificationId,
                   new_state: newState,
                   post_id: post_id,
                   security: nonce, 
               },
              
               success: function(response) {
                   if (response.success) {
                   var enableBtn = jQuery('.enable-btn[data-notification-id="' + notificationId + '"]');
                   enableBtn.text(newState === 'enabled' ? 'Enable' : 'Disable');
                   enableBtn.data('notification-state', newState);
                   } else {
                   console.log('Failed to update notification status. Message: ' + response.message);
                   }
               },
               
               error: function(xhr, textStatus, errorThrown) {
                   console.log('AJAX request failed: ' + errorThrown);
               }
           });
       });
   });

   jQuery(document).ready(function($) {
        // Check if the element with ID 'notifytable' exists
        if (jQuery('#notifytable').length) {
            var notifytable = jQuery('#notifytable').DataTable({
                dom: 'Bfrtip',
                paging: true,
                pageLength: 1,
                searching: true,
                ordering: true,
                aaSorting: [[0, 'asc'], [1, 'asc']],
                responsive: true
            });

            // Add the search functionality
            jQuery('#searchbox').keyup(function() {
                notifytable.search(this.value).draw();
            });
        }
    });

  
   jQuery(document).on('submit', '#usermap_form', function(event) {    
       event.preventDefault();    
       var nonce = ajax_object.nonce;
       var form=jQuery('#usermap_form').serialize();
       var formData=new FormData();
       formData.append('action','saab_save_user_mapping');
       formData.append('saabuser_mapping',form);
       formData.append('security',nonce);
       jQuery.ajax({
           type: 'POST',
           url: ajaxurl,
           data:formData,
           processData:false,
           contentType:false,        
           success: function (response) {
               jQuery('#map_success').html(response.message).fadeIn().delay(2000).fadeOut();              
           }
       });

   });

   jQuery(document).on('submit', '#confirm_form', function(event) {   
       event.preventDefault();    
       var nonce = ajax_object.nonce;
       var form=jQuery('#confirm_form').serialize();
       var formData=new FormData();
       formData.append('action','saab_save_confirmation');
       formData.append('confirmation_data',form);
       formData.append('security',nonce);
       jQuery.ajax({
           type: 'POST',
           url: ajax_object.ajax_url,
           data:formData,
           processData:false,
           contentType:false,        
           success: function (response) {
               jQuery('#confirm_msg').html(response.message).fadeIn().delay(2000).fadeOut();              
           }
       });

   });
}

jQuery(document).ready(function ($) {
    // jQuery('#saabpage-number').on('change', function(e) {
    jQuery(document).on('change', '#saabpage-number', function(e) {
        e.preventDefault();
        const page = jQuery("#saabpage-number").val();
        const timeslot = jQuery("#saabpage-number").data("timeslot");
        const booking_date = jQuery("#saabpage-number").data("booking_date");
        const nonce = jQuery("#saabpage-number").data("nonce"); // Get the nonce value
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "html",
            data: {
            action: "saab_get_paginated_items_for_waiting_list", // Action hook to trigger the server-side function
            page: page,
            timeslot: timeslot,
            booking_date: booking_date,
            security: nonce, // Include the nonce in the data
            },
            success: function (data) {
                // Display the fetched items on the page
                jQuery("#waitinglist_main").html(data);
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
        });
    });
});