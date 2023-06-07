jQuery(document).on("click", "#clickajax", function(event){
    event.preventDefault();
    var post_ID = 1;
    
    // var link = "<?php echo admin_url('admin-ajax.php'); ?>";
    jQuery.ajax({
        url:  myAjax.ajaxurl,
        type : 'post',
           data: { 
           action: "bms_front_save_post_meta",
           post_link:post_ID,
          
        },
        success: function (data) {
            // console.log(data);
            //alert(data);
            jQuery('#main_sp_45').html(data);
            //jQuery("#myresult).html(data);
              
        }
    });
    return false;
});