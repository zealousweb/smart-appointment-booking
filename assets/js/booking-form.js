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
  
    // Add timeslot functionality
    // $('.add-breaktimeslot').on('click', function() {
    //     // alert("test");
    //   var timeslotHTML = '<div class="breaktimeslot">' +
    //     '<label>Start Time:</label>' +
    //     '<input type="number" name="breaktimeslots[start_hours][]" min="0" max="23"  placeholder="HH" value="">' +
    //     '<input type="number" name="breaktimeslots[start_minutes][]" min="0" max="59" placeholder="MM" value="">' +
    //     '<input type="number" name="breaktimeslots[start_seconds][]" min="0" max="59" placeholder="SS" value="">' +
    //     '<br>' +
    //     '<label>End Time:</label>' +
    //     '<input type="number" name="breaktimeslots[end_hours][]" min="0" max="23"  placeholder="HH" value="">' +
    //     '<input type="number" name="breaktimeslots[end_minutes][]" min="0" max="59" placeholder="MM" value="">' +
    //     '<input type="number" name="breaktimeslots[end_seconds][]" min="0" max="59" placeholder="SS" value="">' +
    //      '<button type="button" class="remove-breaktimeslot">Remove Timeslot</button>' +
    //     '</div>';
    //   $('.breaktimeslot-repeater').append(timeslotHTML);
    // });
  
    // // Remove timeslot functionality
    // $('.breaktimeslot-repeater').on('click', '.remove-breaktimeslot', function() {
    //   $(this).closest('.breaktimeslot').remove();
    // });
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
