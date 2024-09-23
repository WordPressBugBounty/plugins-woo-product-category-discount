/*
 * This function will handle ajax based category save.
 * Date: 25-04-2017
 * Author: Vidish Purohit
 */

jQuery(document).ready(function() {

	jQuery('input[type="text"], input[type="checkbox"], select').each(function() {
	       
       	if(jQuery(this).parent().parent().find('input[type="checkbox"]:checked').length == 1) {
    		jQuery(this).parent().parent().find('input[type="text"], select').attr('disabled', true);
    	}
    });
	jQuery('#chkEntireShopDiscount').on('click', function() {
	    	

		var controller = jQuery('#bulk_controller').find(":selected").val();
		var controller_text = jQuery('#bulk_controller').find(":selected").text();

		var row_id = jQuery(this).attr('data-row_id');
		
		if(jQuery(this).is(':checked')) {

			if(confirm( 'This will discount entire shop based on ' + controller_text + '. This process may take time. If your shop has high amount of products, then we recommend you to do it when your site has less traffic. Are you sure you want to proceed?')) {

				jQuery('input[type="text"], input[type="checkbox"], select').prop("disabled", true);

				jQuery.post(
				    wpcd_obj.admin_url, 
				    {
				        'action': 'wpcdp_apply_bulk_discount',
				        'controller': controller
				    }, 
				    function(response){

				        jQuery('input[type="text"], input[type="checkbox"], select').removeAttr("disabled");

				        setTimeout(function() {
				        	jQuery('.trwcpd' + row_id).find('.complete').fadeOut(300);
				        }, 3000);
				    }
				);
			}
		} else {

			if(confirm( 'This will remove discount from entire shop based on ' + controller + '. This process may take time. If your shop has high amount of products, then we recommend you to do it when your site has less traffic. Are you sure you want to proceed?')) {

				jQuery('input[type="text"], input[type="checkbox"], select').prop("disabled", true);

				jQuery.post(
				    wpcd_obj.admin_url, 
				    {
				        'action': 'wpcdp_remove_bulk_discount',
				        'controller': controller
				    }, 
				    function(response){

				        jQuery('input[type="text"], input[type="checkbox"], select').removeAttr("disabled");

				        setTimeout(function() {
				        	jQuery('.trwcpd' + row_id).find('.complete').fadeOut(300);
				        }, 3000);
				    }
				);
			}
		}
	});

	jQuery('.wpcd_active').on('click', function() {
	    	
		var row_id = jQuery(this).attr('data-row_id');
		
		updateCategories(row_id);
	});

	jQuery('.type').on('change', function() {
	    		
		var row_id = jQuery(this).attr('data-row_id');
	    updateCategories(row_id);
	});

	jQuery('.amount').keypress(function(event) {
		  if ((event.which != 46 || jQuery(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
		    event.preventDefault();
		  }
	});
	jQuery('.amount, .fromDate, .toDate').blur(function(event) {
	  	var row_id = jQuery(this).attr('data-row_id');
	    updateCategories(row_id);
	});
});

function updateCategories(row_id) {
	
	var flgActive = jQuery('.trwcpd' + row_id).find('.wpcd_active').is(':checked');
	var flgScheduled = jQuery('.trwcpd' + row_id).find('.scheduled').is(':checked');

	var fltAmount = jQuery('.trwcpd' + row_id).find('.amount').val();
	var type = jQuery('.trwcpd' + row_id).find('.type').val();
	var fromDate = jQuery('.trwcpd' + row_id).find('.fromDate').val();
	var toDate = jQuery('.trwcpd' + row_id).find('.toDate').val();
	jQuery('.trwcpd' + row_id).find('.complete').fadeOut(300);
	jQuery('input[type="text"], input[type="checkbox"], select').prop("disabled", true);
	jQuery('.trwcpd' + row_id).find('.loader').fadeIn(300);
	var controller = jQuery('#controller').val();
	jQuery.post(
	    wpcd_obj.admin_url, 
	    {
	        'action': 'wpcd_calculate_discount',
	        'type':   type,
	        'amount': fltAmount,
	        'active': flgActive,
	        'cat_id': row_id,
	        'controller': controller,
	        'sec':jQuery('#wpcd_discount').val()
	    }, 
	    function(response){

	        jQuery('.trwcpd' + row_id).find('.loader').css('display', 'none');
	        jQuery('.trwcpd' + row_id).find('.complete').fadeIn(300);
	        jQuery('input[type="text"], input[type="checkbox"], select').each(function() {
	        	
	        	if(jQuery(this).parent().parent().find('input[type="checkbox"]:checked').length !== 1) {
	        		jQuery(this).removeAttr("disabled");
	        	} else if(jQuery(this).attr('type') == 'checkbox') {
	        		jQuery(this).removeAttr("disabled");
	        	}
	        })
	        
	        if(jQuery('.trwcpd' + row_id).find('input[type="checkbox"]:checked').length === 1) {
	        	jQuery('.trwcpd' + row_id).find('input[type="text"], select').prop("disabled", true);
	        }
	        setTimeout(function() {
	        	jQuery('.trwcpd' + row_id).find('.complete').fadeOut(300);
	        }, 3000);
	    }
	);
}

