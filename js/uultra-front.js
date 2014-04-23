if(typeof $ == 'undefined'){
	var $ = jQuery;
}
(function($) {
    $(document).ready(function () { 
	
	   "use strict";
       
		
				
		$('.rating-s').click(function() {
			
			
			var data_id =  jQuery(this).attr("data-id");
			var data_target =  jQuery(this).attr("data-target");
			var data_vote =  jQuery(this).attr("data-vote");	
			

		
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {action: "rating_vote", data_id: data_id , data_target: data_target , data_vote: data_vote },
				
				success: function(data){
					
					alert(data);
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
				
        });
		
		
		$('#uu-send-private-message').click(function() {
			
			
			$( "#uu-pm-box" ).slideDown();	
			 return false;
    		e.preventDefault();
				
        });
		
		$('#uu-close-private-message-box').click(function() {		
			
			$( "#uu-pm-box" ).slideUp();	
			
			
			 return false;
    		e.preventDefault();
				
        });
		
		$('#uu-send-private-message').click(function() {			
			
			$( "#uu-upload-avatar-box" ).slideDown();	
			 return false;
    		e.preventDefault();
				
        });
		
		
		
		$('#uu-send-private-message-confirm').click(function() {
			
			
			var receiver_id =  jQuery(this).attr("data-id");
			
			var uu_subject =   $('#uu_subject').val();
			var uu_message =   $('#uu_message').val();
			
			if(uu_subject==""){alert(uu_subject_empty); $('#uu_subject').focus(); return;}
			if(uu_message==""){alert(uu_message_empty);  $('#uu_message').focus(); return;}

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "send_private_message", "receiver_id": receiver_id , "uu_subject": uu_subject , "uu_message": uu_message },
				
				success: function(data){
					
					 $('#uu_subject').val("");
					 $('#uu_message').val("");
					
					$("#uu-message-noti-id").html(data);
					jQuery("#uu-message-noti-id").slideDown();
					setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		$('#uu-reply-private-message-confirm').click(function() {
			
			
			var message_id =  jQuery(this).attr("message-id");			
			
			var uu_message =   $('#uu_message').val();
			
			if(uu_message==""){alert(uu_message_empty);  $('#uu_message').focus(); return;}

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "reply_private_message", "message_id": message_id ,  "uu_message": uu_message },
				
				success: function(data){
					
										
					$("#uu-message-noti-id").html(data);
					jQuery("#uu-message-noti-id").slideDown();
					setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
					 window.location.reload();
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		$('#uu-private-message-change-status').click(function() {
			
			
			var message_id =  jQuery(this).attr("message-id");			
			var message_status =  jQuery(this).attr("message-status");			
			
			
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "message_change_status", "message_id": message_id ,  "message_status": message_status },
				
				success: function(data){
					
										
					$("#uu-message-noti-id").html(data);
					jQuery("#uu-message-noti-id").slideDown();
					setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		$('#uu-private-message-delete').click(function() {
			
			
			var message_id =  jQuery(this).attr("message-id");			
			
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "message_delete", "message_id": message_id  },
				
				success: function(data){
					
										
					$("#uu-message-noti-id").html(data);
					jQuery("#uu-message-noti-id").slideDown();
					setTimeout("hidde_noti('uu-message-noti-id')", 3000)	;
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		$(document).on("click", ".btn-gallery-conf", function(e) {
			
			e.preventDefault();		
			
			
				var gal_id =  jQuery(this).attr("data-id");	
				var gal_name= $("#uultra_gall_name_edit_"+gal_id).val()	;
				var gal_desc =  $("#uultra_gall_desc_edit_"+gal_id).val();
				var gal_visibility =  $("#uultra_gall_visibility_edit_"+gal_id).val();
									
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {"action": "edit_gallery_confirm", "gal_id": gal_id , "gal_name": gal_name , "gal_desc": gal_desc , "gal_visibility": gal_visibility },
					
					success: function(data){					
						
												
						$( "#gallery-edit-div-"+gal_id ).slideUp();
						reload_gallery_list();
						
						
						}
				});
			
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 
				
        });
		
		$(document).on("click", "a[href='#uultra-forgot-link']", function(e) {
		
			
			e.preventDefault();
			$( "#xoouserultra-forgot-pass-holder" ).slideDown();
			 return false;
    		e.preventDefault();
			 
				
        });
		
		$(document).on("click", "#xoouserultra-forgot-pass-btn-confirm", function(e) {		
			
			e.preventDefault();			
		
		
				var email= $("#user_name_email").val()	;
				
									
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {"action": "send_reset_link", "user_login": email },
					
					success: function(data){
						
					
						$("#uultra-signin-ajax-noti-box").html(data);
						jQuery("#uultra-signin-ajax-noti-box").slideDown();
						setTimeout("hidde_noti('uultra-signin-ajax-noti-box')", 3000)	;			
												
												
						
						}
				});
			
			
			
			 return false;
    		e.preventDefault();
			 
				
        });
		
		//send friend request				
		$(document).on("click", "#uu-send-friend-request", function(e) {
			
			
			var user_id =  jQuery(this).attr("user-id");		
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "send_friend_request", "user_id": user_id },
				
				success: function(data){					
										
					alert(data);
					
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		//like item
				
		$(document).on("click", "#uu-like-item", function(e) {
			
			e.preventDefault();			
			
			var item_id =  jQuery(this).attr("item-id");
			var module =  jQuery(this).attr("data-module");	
			var vote =  jQuery(this).attr("data-vote");		
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "like_item", "item_id": item_id , "module": module , "vote": vote },
				
				success: function(data){					
										
					$("#uu-like-sore-id-"+item_id).html(data);
					
					setTimeout("get_total_likes('"+item_id+"','"+module+"')", 2000)	;
					
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		
		$(document).on("click", "#uu-approvedeny-friend", function(e) {
			
			e.preventDefault();			
			
			var item_id =  jQuery(this).attr("item-id");			
			var item_action =  jQuery(this).attr("action-id");		
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "friend_request_action", "item_id": item_id , "item_action": item_action },
				
				success: function(data){
								
					reload_friend_request();								
					reload_my_friends();
					
					
					}
			});
			
			 // Cancel the default action
			 return false;
    		e.preventDefault();
			 

				
        });
		
		
		
		
		   
 
       
    }); //END READY
})(jQuery);


function get_total_likes (item_id, module)
{
	jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "get_item_likes_amount_only", "item_id": item_id , "module": module  },
				
				success: function(data){					
										
					$("#uu-like-sore-id-"+item_id).html(data);
					
													
					
					}
			});
			
		
}

function reload_my_friends ()
{
	jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "show_all_my_friends"  },
				
				success: function(data){					
										
								$("#uultra-my-friends-list").html(data);
					
					}
			});
			
		
}

function reload_friend_request ()
{
	jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {"action": "show_friend_request"  },
				
				success: function(data){					
										
								$("#uultra-my-friends-request").html(data);
					
					}
			});
			
		
}

function hidde_noti (div_d)
{
		jQuery("#"+div_d).slideUp();		
		
}
