<?php
class XooUserRegister {

	function __construct() {
		
		/*Form is fired*/
	    
		if (isset($_POST['xoouserultra-register-form'])) {
			
			/* Prepare array of fields */
			$this->uultra_prepare_request( $_POST );
       			
			/* Validate, get errors, etc before we create account */
			$this->uultra_handle_errors();
			
			/* Create account */
			$this->uultra_create_account();
				
		}

	}
	
	/*Prepare user meta*/
	function uultra_prepare_request ($array ) 
	{
		foreach($array as $k => $v) 
		{
			if ($k == 'usersultra-register') continue;
			$this->usermeta[$k] = $v;
		}
		return $this->usermeta;
	}
	
	/*Handle/return any errors*/
	function uultra_handle_errors() 
	{
	    global $usersultra_captcha_loader;
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		
		if(get_option('users_can_register') == '1')
		{
		    foreach($this->usermeta as $key => $value) {
		    
		        /* Validate username */
		        if ($key == 'user_login') {
		            if (esc_attr($value) == '') {
		                $this->errors[] = __('<strong>ERROR:</strong> Please enter a username.','xoousers');
		            } elseif (username_exists($value)) {
		                $this->errors[] = __('<strong>ERROR:</strong> This username is already registered. Please choose another one.','xoousers');
		            }
		        }
		    
		        /* Validate email */
		        if ($key == 'user_email') 
				{
		            if (esc_attr($value) == '') 
					{
		                $this->errors[] = __('<strong>ERROR:</strong> Please type your e-mail address.','xoousers');
		            } elseif (!is_email($value)) 
					{
		                $this->errors[] = __('<strong>ERROR:</strong> The email address isn\'t correct.','xoousers');
		            } elseif (email_exists($value)) 
					{
		                $this->errors[] = __('<strong>ERROR:</strong> This email is already registered, please choose another one.','xoousers');
		            }
		        }
		    
		    }
		    
		    if(!is_in_post('no_captcha','yes'))
		    {
		        if(!$usersultra_captcha_loader->validate_captcha(post_value('captcha_plugin')))
		        {
		            $this->errors[] = __('<strong>ERROR:</strong> Please complete Captcha Test first.','xoousers');
		        }
		    } 
		}
		else
		{
		    $this->errors[] = __('<strong>ERROR:</strong> Registration is disabled for this site.','xoousers');
		}
		
		
		
	}
	
	
	/*Create user*/
	function uultra_create_account() 
	{
		
		global $xoouserultra;
		session_start();
		
		require_once(ABSPATH . 'wp-includes/pluggable.php');
			
			/* Create profile when there is no error */
			if (!isset($this->errors)) 
			{
				
				/* Create account, update user meta */
				$sanitized_user_login = sanitize_user($_POST['user_login']);
				
				/* Get password */
				if (isset($_POST['user_pass']) && $_POST['user_pass'] != '') 
				{
					$user_pass = $_POST['user_pass'];
				} else {
					$user_pass = wp_generate_password( 12, false);
				}
				
				/* We create the New user */
				$user_id = wp_create_user( $sanitized_user_login, $user_pass, $_POST['user_email'] );
				
				if ( ! $user_id ) 
				{

				}else{
					
					/*We've got a valid user id then let's create the meta informaion*/						
					foreach($this->usermeta as $key => $value) 
					{
						
						update_user_meta($user_id, $key, esc_attr($value));

						/* update core fields - email, url, pass */
						if ( in_array( $key, array('user_email', 'user_url', 'display_name') ) )
						{
							wp_update_user( array('ID' => $user_id, $key => esc_attr($value)) );
						}
						
					}
					
					//set account status					
					$xoouserultra->login->user_account_status($user_id);
					
					$verify_key = $xoouserultra->login->get_unique_verify_account_id();					
					update_user_meta ($user_id, 'xoouser_ultra_very_key', $verify_key);							
					
					 //mailchimp					 
					 if(isset($_POST["uultra-mailchimp-confirmation"]) && $_POST["uultra-mailchimp-confirmation"]==1)
					 {
						 $list_id =  $xoouserultra->get_option('mailchimp_list_id');					 
						 $xoouserultra->subscribe->mailchimp_subscribe($user_id, $list_id);
						 update_user_meta ($user_id, 'xoouser_mailchimp', 1);	
						 						
					
					 }
					
					
					
				}
				

				//check if it's a paid sign up				
				
				if($xoouserultra->get_option('registration_rules')==4)
				{
					//this is a paid sign up					
										
					//get package
					$package = $xoouserultra->paypal->get_package($_POST["usersultra_package_id"]);
					$amount = $package->package_amount;
					$p_name = $package->package_name;
					$package_id = $package->package_id;
					
					//payment Method
					$payment_method = 'paypal';
					
					//create transaction
					$transaction_key = session_id()."_".time();
					
					$order_data = array('user_id' => $user_id,
					 'transaction_key' => $transaction_key,
					 'amount' => $amount,
					 'order_package_id' => $package_id ,
					 'product_name' => $p_name ,
					 'status' => 'pending',
					 'method' => $payment_method); 
					 
					if( $amount > 0)
					 {
						 $xoouserultra->order->create_order($order_data);
						
					 }	
					
										
					
					//update status
					 update_user_meta ($user_id, 'usersultra_account_status', 'pending_payment');
					 
					 //store tempassword
					 update_user_meta ($user_id, 'usersultra_temp_password', $user_pass);
					 
					 //package 
					 update_user_meta ($user_id, 'usersultra_user_package_id', $package_id);
					 
					 //mailchimp					 
					 if(isset($_POST["uultra-mailchimp-confirmation"]) && $_POST["uultra-mailchimp-confirmation"]==1)
					 {						
						 //do mailchimp stuff	
						 $list_id =  $xoouserultra->get_option('mailchimp_list_id');					 
						 $xoouserultra->subscribe->mailchimp_subscribe($user_id, $list_id);	
						 update_user_meta ($user_id, 'xoouser_mailchimp', 1);					
					
					  }
					 
					 
					 
					 //set expiration date
					 // update_user_meta ($user_id, 'usersultra_account_creation_date', 'pending_payment');
					 // update_user_meta ($user_id, 'usersultra_account_expiration_date', 'pending_payment');
					 
					 if($payment_method=="paypal" && $amount > 0)
					 {
						  $ipn = $xoouserultra->paypal->get_ipn_link($order_data);
						  
						  //redirect to paypal
						  header("Location: $ipn");exit;						  
						  exit;					  
						 
					 }else{						 
						 
						 //paid membership but free plan selected						 
						 //notify depending on status
					      $xoouserultra->login->user_account_notify($user_id, $_POST['user_email'],  $sanitized_user_login, $user_pass);
						  
						  //check if requires admin approvation
						  
						  if($package->package_approvation=="yes")
						  {
							  
							  
							 
						  }else{
							  
							  //this package doesn't require moderation
							   update_user_meta ($user_id, 'usersultra_account_status', 'active');
							  //notify user					   
		 					   $xoouserultra->messaging->welcome_email($_POST['user_email'], $sanitized_user_login, $user_pass);
							  
							   //login
							   $secure = "";		
							  //already exists then we log in
							  wp_set_auth_cookie( $user_id, true, $secure );	
							  //redirect
							  $xoouserultra->login->login_registration_afterlogin();
							  
						  
						  }
						 
						 
					 }
					 
				
				}else{
					
					//this is not a paid sign up
					
					//notify depending on status
					$xoouserultra->login->user_account_notify($user_id, $_POST['user_email'],  $sanitized_user_login, $user_pass);
										
				
				}	
				
				
				 //check if login automatically
				  $activation_type= $xoouserultra->get_option('registration_rules');
				  
				  if($activation_type==1)
				  {					  					  
					  //login
					   $secure = "";		
					  //already exists then we log in
					  wp_set_auth_cookie( $user_id, true, $secure );	
					  //redirect
		              $xoouserultra->login->login_registration_afterlogin();						
	  
	              } 
				
				
				
				
			} //end error link
			
	}
	
	/*Get errors display*/
	function get_errors() {
		global $xoouserultra;
		$display = null;
		if (isset($this->errors) && count($this->errors)>0) 
		{
		$display .= '<div class="usersultra-errors">';
			foreach($this->errors as $newError) {
				
				$display .= '<span class="usersultra-error usersultra-error-block"><i class="usersultra-icon-remove"></i>'.$newError.'</span>';
			
			}
		$display .= '</div>';
		} else {
		
			$this->registered = 1;
			$display .= '<div class="usersultra-success"><span><i class="usersultra-icon-ok"></i>'.__('Registration successful. Please check your email.','xoousers').'</span></div>';
			
			if (isset($_POST['redirect_to'])) {
				wp_redirect( $_POST['redirect_to'] );
			}
			
		}
		return $display;
	}

}

$key = "register";
$this->{$key} = new XooUserRegister();