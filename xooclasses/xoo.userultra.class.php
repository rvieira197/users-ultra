<?php
//session_start();
class XooUserUltra
{
	public $classes_array = array();
	public $registration_fields;
	public $login_fields;
	public $fields;
	public $allowed_inputs;
	public $use_captcha = "no";
	
	
		
	public function __construct()
	{
		
		$this->logged_in_user = 0;
		$this->login_code_count = 0;
		$this->current_page = $_SERVER['REQUEST_URI'];
	  
    }
	

	public function plugin_init() 
	{
		
		/*Load Main classes*/
		$this->set_main_classes();
		$this->load_classes();
		
		
		/*Load Amin Classes*/		
		if (is_admin()) 
		{
			$this->set_admin_classes();
			$this->load_classes();					
		
		}
		
		//create standard fields
		$this->xoousers_create_standard_fields();
		
		//ini settings
		$this->intial_settings();
		
		
	}
	
	public function set_main_classes()
	{
		 $this->classes_array = array( "commmonmethods" =>"xoo.userultra.common" ,
		/* "captchamodule" =>"xoo.userultra.captchamodules", */
		
		  "htmlbuilder" =>"xoo.userultra.htmlbuilder" ,
		  "publisher" =>"xoo.userultra.publisher" ,
		  "activity" =>"xoo.userultra.activity" ,
		  "messaging" =>"xoo.userultra.messaging" ,  
		  "recaptchalib" =>"xoo.ulserultra.recaptchalib",  
		  "order" =>"xoo.userultra.order",
		  "subscribe" =>"xoo.userultra.newslettertool",
		  "paypal" =>"xoo.userultra.payment.paypal"  ,
		  "social" =>"xoo.userultra.socials", 
		  "shortocde" =>"xoo.userultra.shorcodes" , 
		  "login" =>"xoo.userultra.login" , 
		  "register" =>"xoo.userultra.register",
		  "mymessage" =>"xoo.userultra.mymessage" , 
		  "rating" =>"xoo.userultra.rating" , 
		  "statistc" =>"xoo.userultra.stat" ,		  
		  "photogallery" =>"xoo.userultra.photos"  , 
		  "userpanel" =>"xoo.userultra.user"  );  	
	
	}
	
	public function pluginname_ajaxurl() 
	{
		echo '<script type="text/javascript">var ajaxurl = "'. admin_url("admin-ajax.php") .'";
</script>';
	}
	
	
	public function intial_settings()
	{
		$this->include_for_validation = array('text','fileupload','textarea','select','radio','checkbox','password');	
		add_action('init',  array(&$this, 'xoousers_load_textdomain'));		
		add_action('wp_enqueue_scripts', array(&$this, 'add_front_end_styles'), 9); 
		
		/* Remove bar except for admins */
		add_action('init', array(&$this, 'userultra_remove_admin_bar'), 9);
		
		/*Create a generic profile page*/
		add_action( 'wp_loaded', array(&$this, 'create_initial_pages'), 9);
		
		/*Setup redirection*/
		add_action( 'wp_loaded', array(&$this, 'xoousersultra_redirect'), 9);
		add_action( 'wp_head', array(&$this, 'pluginname_ajaxurl'));
			 
		
	}
	
	function userultra_remove_admin_bar() 
	{
		if (!current_user_can('administrator') && !is_admin())
		{
			
			if ($this->get_option('hide_admin_bar')==1) 
			{
				
				show_admin_bar(false);
			}
		}
	}
	
	public function get_logout_url ()
	{
		
		/*$defaults = array(
		            'redirect_to' => $this->current_page
		    );
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args, EXTR_SKIP );*/
		
		$redirect_to = $this->current_page;
			
		return wp_logout_url($redirect_to);
	}
	
	public function get_redirection_link ($module)
	{
		$url ="";
		
		if($module=="profile")
		{
			//get profile url
			$url = $this->get_option('profile_page_id');			
		
		}
		
		return $url;
		
	}
	
	
	/*Setup redirection*/
	public function xoousersultra_redirect() 
	{
		global $pagenow;

		/* Not admin */
		if (!current_user_can('manage_options')) {
			
		    $option_name = '';
        // Check if current page is profile page
        if('profile.php' == $pagenow)
        {
            // If user have selected to redirect backend profile page            
            if($this->get_option('redirect_backend_profile') == '1')
            {
                $option_name = 'profile_page_id';
            }
        }  
            

        // Check if current page is login or not
        if('wp-login.php' == $pagenow && !isset($_REQUEST['action']))
        {
            if($this->get_option('redirect_backend_login') == '1')
            {
                $option_name = 'login_page_id';
            }
        }

        if('wp-login.php' == $pagenow && isset($_REQUEST['action']) && $_REQUEST['action'] == 'register')
        {
            if($this->get_option('redirect_backend_registration') == '1')
            {
                $option_name = 'registration_page_id';
            }
        }
        
        if($option_name != '')
        {
            if($this->get_option($option_name) > 0)
            {
                // Generating page url based on stored ID
                $page_url = get_permalink($this->get_option($option_name));
                
                // Redirect if page is not blank
                if($page_url != '')
                {
                    if($option_name == 'login_page_id' && isset($_GET['redirect_to']))
                    {
                        $url_data = parse_url($page_url);
                        $join_code = '/?';
                        if(isset($url_data['query']) && $url_data['query']!= '')
                        {
                            $join_code = '&';
                        }
                        
                        $page_url= $page_url.$join_code.'redirect_to='.$_GET['redirect_to'];
                    }
                    
                    wp_redirect($page_url);
                    exit;
                }
            }    
        }
		}

	}
	
	
	
	public function create_initial_pages ()
	{
		//create profile page
		$login_page_id  = $this->create_login_page();
		
		//create registration page		
		$login_page_id  = $this->create_register_page();
		
		//Create Main Page
		$main_page_id = $this->create_main_page();		

		//create profile page
		$profile_page_id  = $this->create_profile_page($main_page_id);	
		
		//directory page
		$directory_page_id  = $this->create_directory_page($main_page_id);	
		
				
		$this->create_rewrite_rules();
		
		/* Setup query variables */
	     add_filter( 'query_vars',   array(&$this, 'userultra_uid_query_var') );	
		
			
		
	
	}
	
	public function create_rewrite_rules() 
	{
		
		$slug = $this->get_option("usersultra_slug"); // Profile Slug
		$slug_login = $this->get_option("usersultra_login_slug"); //Login Slug		
		$slug_registration = $this->get_option("usersultra_registration_slug"); //Registration Slug		
		$slug_my_account = $this->get_option("usersultra_my_account_slug"); //My Account Slug
		
		// this rule is used to display the registration page
		add_rewrite_rule("$slug/$slug_registration",'index.php?pagename='.$slug.'/'.$slug_registration, 'top');
		
		//this rules is for displaying the user's profiles
		add_rewrite_rule("$slug/([^/]+)/?",'index.php?pagename='.$slug.'&uu_username=$matches[1]', 'top');
		
		//this rules is for photos
		//add_rewrite_rule("$slug/([^/]+)/?",'index.php?pagename='.$slug.'&uu_photokeyword=$matches[1]', 'top');
		
		flush_rewrite_rules();
	
	
	}
	
	
	
	/*Create profile page */
	public function create_profile_page($parent) 
	{
		if (!$this->get_option('profile_page_id')) 
		{
			$slug = $this->get_option("usersultra_slug");
			
			$new = array(
			  'post_title'    => __('View Profile','xoousers'),
			  'post_type'     => 'page',
			  'post_name'     => $slug,			 
			  'post_content'  => '[usersultra_profile]',
			  'post_status'   => 'publish',
			  'comment_status' => 'closed',
			  'ping_status' => 'closed',
			  'post_author' => 1
			);
			$new_page = wp_insert_post( $new, FALSE );
			
			
			if (isset($new_page))
			{
				
			  $current_option = get_option('userultra_options');
			  $page_data = get_post($new_page);

			
				if(isset($page_data->guid))
				{
					//update settings
					$this->userultra_set_option('profile_page_id',$new_page);
					
				}
				
			}
		}
	}
	
	/*Create Directory page */
	public function create_directory_page($parent) 
	{
		if (!$this->get_option('directory_page_id')) 
		{
			$slug = $this->get_option("usersultra_directory_slug");
			
			$new = array(
			  'post_title'    => __('Members Directory','xoousers'),
			  'post_type'     => 'page',
			  'post_name'     => $slug,			 
			  'post_content'  =>"[usersultra_directory list_per_page=8 optional_fields_to_display='social,country,description' pic_boder_type='rounded']",
			  'post_status'   => 'publish',
			  'comment_status' => 'closed',
			  'ping_status' => 'closed',
			  'post_author' => 1
			);
			$new_page = wp_insert_post( $new, FALSE );
			
			
			if (isset($new_page))
			{
				
			  $current_option = get_option('userultra_options');
			  $page_data = get_post($new_page);

			
				if(isset($page_data->guid))
				{
					//update settings
					$this->userultra_set_option('directory_page_id',$new_page);
					
				}
				
			}
		}
	}
	
	/*Create login page */
	public function create_login_page() 
	{
		if (!$this->get_option('login_page_id')) {
			
			
			$slug = $this->get_option("usersultra_login_slug");
			
			$new = array(
			  'post_title'    => __('Login','xoousers'),
			  'post_type'     => 'page',
			  'post_name'     => $slug,
			 
			  'post_content'  => '[usersultra_login]',
			  'post_status'   => 'publish',
			  'comment_status' => 'closed',
			  'ping_status' => 'closed',
			  'post_author' => 1
			);
			$new_page = wp_insert_post( $new, FALSE );
			
			
			if (isset($new_page))
			{
				$page_data = get_post($new_page);

				
				if(isset($page_data->guid))
				{
					//update settings
					$this->userultra_set_option('login_page_id',$new_page);
					
				}
				
			}
		}
	}
	
	/*Create register page */
	public function create_register_page() 
	{
		if (!$this->get_option('registration_page_id')) {
			
			//get slug
			$slug = $this->get_option("usersultra_registration_slug");
			
			$new = array(
			  'post_title'    => __('Sign up','xoousers'),
			  'post_type'     => 'page',
			  'post_name'     => $slug,
			  			 
			  'post_content'  => '[usersultra_registration]',
			  'post_status'   => 'publish',
			  'comment_status' => 'closed',
			  'ping_status' => 'closed',
			  'post_author' => 1
			);
			$new_page = wp_insert_post( $new, FALSE );
			
			
			if (isset($new_page))
			{
				$page_data = get_post($new_page);

				if(isset($page_data->guid))
				{
					//update settings
					$this->userultra_set_option('registration_page_id',$new_page);
					
				}
				
			}
		}
	}
	
	
	
	/*Create My Account Page */
	public function create_main_page() 
	{
		if (!get_option('xoousersultra_my_account_page')) 
		{
			//get slug
			$slug = $this->get_option("usersultra_my_account_slug");
			
				
			
			$new = array(
				  'post_title'    => __('My Account','xoousers'),
				  'post_type'     => 'page',
				  'post_name'     => $slug,
				  'post_content'  => '[usersultra_my_account]',
				  'post_status'   => 'publish',
				  'comment_status' => 'closed',
				  'ping_status' => 'closed',
				  'post_author' => 1
				);
			
			$new_page = wp_insert_post( $new, FALSE );
			update_option('xoousersultra_my_account_page',$new_page);
		
		}else{
			
			$new_page=get_option('xoousersultra_my_account_page');
			
			
		
		}
		return $new_page;	
		
	}
	
	
	public function userultra_uid_query_var( $query_vars )
	{
		$query_vars[] = 'uu_username';
		$query_vars[] = 'searchuser';
		return $query_vars;
	}
	
	public function userultra_set_option($option, $newvalue)
	{
		$settings = get_option('userultra_options');
		$settings[$option] = $newvalue;
		update_option('userultra_options', $settings);
	}
	
	
	public function get_fname_by_userid($user_id) 
	{
		$f_name = get_user_meta($user_id, 'first_name', true);
		$l_name = get_user_meta($user_id, 'last_name', true);
		
		$f_name = str_replace(' ', '_', $f_name);
		$l_name = str_replace(' ', '_', $l_name);
		$name = $f_name . '-' . $l_name;
		return $name;
	}
	
	public function xoousers_create_standard_fields ()	
	{
		
		/* Allowed input types */
		$this->allowed_inputs = array(
			'text' => __('Text','xoousers'),
			'fileupload' => __('Image Upload','xoousers'),
			'textarea' => __('Textarea','xoousers'),
			'select' => __('Select Dropdown','xoousers'),
			'radio' => __('Radio','xoousers'),
			'checkbox' => __('Checkbox','xoousers'),
			'password' => __('Password','xoousers'),
		  'datetime' => __('Date Picker','xoousers')
		);
		
		/* Core registration fields */
		$set_pass = $this->get_option('set_password');
		if ($set_pass) 
		{
			$this->registration_fields = array( 
			50 => array( 
				'icon' => 'user', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_login', 
				'name' => __('Username','xoousers'),
				'required' => 1
			),
			100 => array( 
				'icon' => 'envelope', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_email', 
				'name' => __('E-mail','xoousers'),
				'required' => 1,
				'can_hide' => 1,
			),
			150 => array( 
				'icon' => 'lock', 
				'field' => 'password', 
				'type' => 'usermeta', 
				'meta' => 'user_pass',
				'name' => __('Password','xoousers'),
				'required' => 1,
				'can_hide' => 0,
				'help' => __('Password must be at least 7 characters long. To make it stronger, use upper and lower case letters, numbers and symbols.','xoousers')
			),
			200 => array( 
				'icon' => 0, 
				'field' => 'password', 
				'type' => 'usermeta', 
				'meta' => 'user_pass_confirm', 
				'name' => __('Confirm Password','xoousers'),
				'required' => 1,
				'can_hide' => 0,
				'help' => __('Type your password again.','xoousers')
			),
			250 => array(
				'icon' => 0,
				'field' => 'password_indicator',
				'type' => 'usermeta'
			)
		);
		} else {
			
		$this->registration_fields = array( 
			50 => array( 
				'icon' => 'user', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_login', 
				'name' => __('Username','xoousers'),
				'required' => 1
			),
			100 => array( 
				'icon' => 'envelope', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_email', 
				'name' => __('E-mail','xoousers'),
				'required' => 1,
				'can_hide' => 1,
				'help' => __('A password will be e-mailed to you.','xoousers')
			)
		);
		}
		
		/* Core login fields */
		$this->login_fields = array( 
			50 => array( 
				'icon' => 'user', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_login', 
				'name' => __('Username or Email','xoousers'),
				'required' => 1
			),
			100 => array( 
				'icon' => 'lock', 
				'field' => 'password', 
				'type' => 'usermeta', 
				'meta' => 'login_user_pass', 
				'name' => __('Password','xoousers'),
				'required' => 1
			)
		);
		
		/* These are the basic profile fields */
		$this->fields = array(
			80 => array( 
			  'position' => '50',
				'type' => 'separator', 
				'name' => __('Profile Info','xoousers'),
				'private' => 0,
				'show_in_register' => 1,
				'deleted' => 0
			),
			
			100 => array( 
			  'position' => '100',
				'icon' => 'user', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'first_name', 
				'name' => __('First Name','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 0,
				'deleted' => 0
			),
			120 => array( 
			  'position' => '101',
				'icon' => 0, 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'last_name', 
				'name' => __('Last Name','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 0,
				'deleted' => 0
			),
			
			130 => array( 
			  'position' => '130',
				'icon' => '0',
				'field' => 'select',
				'type' => 'usermeta',
				'meta' => 'age',
				'name' => __('Age','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'required' => 1,
				'private' => 0,
				'social' => 0,
				'predefined_options' => 'age',
				'deleted' => 0,				
				'allow_html' => 0
			),
			
			150 => array( 
			  'position' => '150',
				'icon' => 'user', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'display_name', 
				'name' => __('Display Name','xoousers'),
				'can_hide' => 0,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 0,
				'required' => 1,
				'deleted' => 0
			),
			170 => array( 
			  'position' => '200',
				'icon' => 'pencil',
				'field' => 'textarea',
				'type' => 'usermeta',
				'meta' => 'brief_description',
				'name' => __('Brief Description','xoousers'),
				'can_hide' => 0,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 0,
				'deleted' => 0,
				'allow_html' => 1
			),
			190 => array( 
			  'position' => '200',
				'icon' => 'pencil',
				'field' => 'textarea',
				'type' => 'usermeta',
				'meta' => 'description',
				'name' => __('About / Bio','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 0,
				'deleted' => 0,
				'allow_html' => 1
			),
			200 => array( 
			  'position' => '200',
				'icon' => '0',
				'field' => 'select',
				'type' => 'usermeta',
				'meta' => 'country',
				'name' => __('Country','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'required' => 1,
				'private' => 0,
				'social' => 0,
				'predefined_options' => 'countries',
				'deleted' => 0,				
				'allow_html' => 0
			),
			
			230 => array( 
			  'position' => '250',
				'type' => 'separator', 
				'name' => __('Contact Info','xoousers'),
				'private' => 0,
				'show_in_register' => 1,
				'deleted' => 0
			),
			
			
			430 => array( 
			  'position' => '400',
				'icon' => 'link', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'user_url', 
				'name' => __('Website','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'required' => 0,
				'private' => 0,
				'social' => 0,
				'deleted' => 0
			),
			470 => array( 
			  'position' => '450',
				'type' => 'separator', 
				'name' => __('Social Profiles','xoousers'),
				'private' => 0,
				'show_in_register' => 1,
				'deleted' => 0
			),
			520 => array( 
			  'position' => '500',
				'icon' => 'facebook', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'facebook', 
				'name' => __('Facebook','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'required' => 0,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 1,
				'tooltip' => __('Connect via Facebook','xoousers'),
				'deleted' => 0
			),
			560 => array( 
			  'position' => '510',
				'icon' => 'twitter', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'twitter', 
				'name' => __('Twitter Username','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'required' => 0,
				'show_in_register' => 1,
				'private' => 0,
				'social' => 1,
				'tooltip' => __('Connect via Twitter','xoousers'),
				'deleted' => 0
			),
			590 => array( 
			  'position' => '520',
				'icon' => 'google-plus', 
				'field' => 'text', 
				'type' => 'usermeta', 
				'meta' => 'googleplus', 
				'name' => __('Google+','xoousers'),
				'can_hide' => 1,
				'can_edit' => 1,
				'show_in_register' => 1,
				'private' => 0,
				'required' => 0,
				'social' => 1,
				'tooltip' => __('Connect via Google+','xoousers'),
				'deleted' => 0
			),
			600 => array( 
			  'position' => '550',
				'type' => 'separator', 
				'name' => __('Account Info','xoousers'),
				'private' => 0,
				'show_in_register' => 1,
				'deleted' => 0
			),
			690 => array(
			  'position' => '600',
				'icon' => 'lock',
				'field' => 'password',
				'type' => 'usermeta',
				'meta' => 'user_pass',
				'name' => __('New Password','xoousers'),
				'can_hide' => 0,
				'can_edit' => 1,
				'private' => 1,
				'social' => 0,
				'deleted' => 0
			),
			720 => array(
			  'position' => '700',
				'icon' => 0,
				'field' => 'password',
				'type' => 'usermeta',
				'meta' => 'user_pass_confirm',
				'name' => 0,
				'can_hide' => 0,
				'can_edit' => 1,
				'private' => 1,
				'social' => 0,
				'deleted' => 0
			)
		);
		
		/* Store default profile fields for the first time */
		if (!get_option('usersultra_profile_fields'))
		{
			update_option('usersultra_profile_fields', $this->fields);
		}	
		
		
	}
	
	public function xoousers_update_field_value($option, $newvalue) 
	{
		$fields = get_option('usersultra_profile_fields');
		$fields[$option] = $newvalue;
		update_option('usersultra_profile_fields', $settings);
	
	}
	
	
	
	public function xoousers_load_textdomain() 
	{
		load_plugin_textdomain( 'xoousers', false, xoousers_path.'/languages/');
    }
	
	function get_the_guid( $id = 0 )
	{
		$post = get_post($id);
		return apply_filters('get_the_guid', $post->guid);
	}
	   	
	function load_classes() 
	{	
		
		foreach ($this->classes_array as $key => $class) 
		{
			if (file_exists(xoousers_path."xooclasses/$class.php")) 
			{
				require_once(xoousers_path."xooclasses/$class.php");
						
					
			}
				
		}	
	}
	
	public function set_admin_classes()
	{
		
		 $this->classes_array = array("xooadmin" =>"xoo.userultra.admin", "adminshortcode" =>"xoo.userultra.adminshortcodes"); 		 
		 
		
	}
	
	/* register styles */
	public function add_front_end_styles()
	{
	
		

		/* Font Awesome */
		wp_register_style( 'xoouserultra_font_awesome', xoousers_url.'css/css/font-awesome.min.css');
		wp_enqueue_style('xoouserultra_font_awesome');
		
		/* Main css file */
		wp_register_style( 'xoouserultra_css', xoousers_url.'templates/'.xoousers_template.'/css/xoouserultra.css');
		wp_enqueue_style('xoouserultra_css');
		
		/* Butonize css file */
		wp_register_style( 'buttonize3_css', xoousers_url.'css/buttonize3.min.css');
		wp_enqueue_style('buttonize3_css');
		
		/* Custom style */		
		wp_register_style( 'xoouserultra_style', xoousers_url.'custom-styles/default.css');
		wp_enqueue_style('xoouserultra_style');
		
		
		/* Responsive */
		wp_register_style( 'xoouserultra_responsive', xoousers_url.'templates/'.xoousers_template.'/css/xoouserultra-responsive.css');
		wp_enqueue_style('xoouserultra_responsive');
		
		
		/* date_picker */
		wp_register_style( 'xoouserultra_date_picker', xoousers_url.'css/xoouserultra-datepicker.css');
		wp_enqueue_style('xoouserultra_date_picker');
		
		wp_register_script( 'xoouserultra_date_picker_js', xoousers_url.'js/xoouserultra-datepicker.js',array('jquery'));
		wp_enqueue_script('xoouserultra_date_picker_js');
		
		/*Expandible*/		
		wp_register_script( 'xoouserultra_expandible_js', xoousers_url.'js/expandible.js',array('jquery'));
		wp_enqueue_script('xoouserultra_expandible_js');
		
		/*Users JS*/		
		wp_register_script( 'uultra-front_js', xoousers_url.'js/uultra-front.js',array('jquery'));
		wp_enqueue_script('uultra-front_js');
		
		/*uploader*/			
		
		wp_enqueue_script('jquery-ui');	
		
		wp_enqueue_script('plupload-all');	
		wp_enqueue_script('jquery-ui-progressbar');		
		 
		wp_register_script( 'xoouserultra_uploader', xoousers_url.'libs/uploader/drag-drop-uploader.js',array('jquery','media-upload'));
		wp_enqueue_script('xoouserultra_uploader');
		
		wp_register_style( 'xoouserultra_uploader_css', xoousers_url.'libs/uploader/drag-drop-uploader.css');
		wp_enqueue_style('xoouserultra_uploader_css');
		
		//lightbox
		wp_register_style( 'xoouserultra_lightbox_css', xoousers_url.'js/lightbox/css/lightbox.css');
		wp_enqueue_style('xoouserultra_lightbox_css');
		
		wp_register_script( 'xoouserultra_lightboxjs', xoousers_url.'js/lightbox/js/lightbox-2.6.min.js',array('jquery'));
		wp_enqueue_script('xoouserultra_lightboxjs');
		
		
		//front end style
		
		if (!is_admin()) 
		{		
			wp_register_style( 'xoouserultra_frontend_css', xoousers_url.'/templates/'.xoousers_template."/css/".'front-styles.css');
			wp_enqueue_style('xoouserultra_frontend_css');
			
			wp_register_style( 'xoouserultra_shortcoddes_css', xoousers_url.'/templates/'.xoousers_template."/css/".'resp-shortcodes.css');
			wp_enqueue_style('xoouserultra_shortcoddes_css');
		}
		
		
		
		$date_picker_array = array(
		            'closeText' => 'Done',
		            'prevText' => 'Prev',
		            'nextText' => 'Next',
		            'currentText' => 'Today',
		            'monthNames' => array(
		                        'Jan' => 'January',
    		                    'Feb' => 'February',
    		                    'Mar' => 'March',
    		                    'Apr' => 'April',
    		                    'May' => 'May',
    		                    'Jun' => 'June',
    		                    'Jul' => 'July',
    		                    'Aug' => 'August',
    		                    'Sep' => 'September',
    		                    'Oct' => 'October',
    		                    'Nov' => 'November',
    		                    'Dec' => 'December'
		                    ),
		            'monthNamesShort' => array(
		                        'Jan' => 'Jan',
    		                    'Feb' => 'Feb',
    		                    'Mar' => 'Mar',
    		                    'Apr' => 'Apr',
    		                    'May' => 'May',
    		                    'Jun' => 'Jun',
    		                    'Jul' => 'Jul',
    		                    'Aug' => 'Aug',
    		                    'Sep' => 'Sep',
    		                    'Oct' => 'Oct',
    		                    'Nov' => 'Nov',
    		                    'Dec' => 'Dec'
		                    ),
		            'dayNames' => array(
		                        'Sun' => 'Sunday',
    		                    'Mon' => 'Monday',
    		                    'Tue' => 'Tuesday',
    		                    'Wed' => 'Wednesday',
    		                    'Thu' => 'Thursday',
    		                    'Fri' => 'Friday',
    		                    'Sat' => 'Saturday'
		                    ),
		            'dayNamesShort' => array(
		                        'Sun' => 'Sun',
    		                    'Mon' => 'Mon',
    		                    'Tue' => 'Tue',
    		                    'Wed' => 'Wed',
    		                    'Thu' => 'Thu',
    		                    'Fri' => 'Fri',
    		                    'Sat' => 'Fri'
		                    ),
		            'dayNamesMin' => array(
		                        'Sun' => 'Su',
    		                    'Mon' => 'Mo',
    		                    'Tue' => 'Tu',
    		                    'Wed' => 'We',
    		                    'Thu' => 'Th',
    		                    'Fri' => 'Fr',
    		                    'Sat' => 'Sa'
		                    ),
		            'weekHeader' => 'Wk'
		        );
		wp_localize_script('xoouserultra_date_picker_js', 'XOOUSERULTRA', $date_picker_array);
		
		
		
	}
	
	/* Display Front End Directory*/
	public function show_users_directory( $atts ) 
	{						
		return $this->userpanel->show_users_directory($atts);	
		
	
	}
	
	/* Display Front End Mini Directory*/
	public function show_users_directory_mini( $atts ) 
	{						
		return $this->userpanel->show_users_directory_mini($atts);	
		
	
	}
	
	/* Custom WP Query*/
	public function get_results( $query ) 
	{
		$wp_user_query = new WP_User_Query($query);						
		return $wp_user_query;
		
	
	}
	
		/* Password Reset */
	public function password_reset( $args=array() ) {

		global $xoouserultra;
		
		// Increasing Counter for Shortcode number
		$this->login_code_count++;
		
		// Check if redirect to is not set and redirect to is availble in URL
		$default_redirect = $this->current_page;
		if(isset($_GET['redirect_to']) && $_GET['redirect_to']!='')
		    $default_redirect = $_GET['redirect_to'];
		
		/* Arguments */
		$defaults = array(
		        'use_in_sidebar' => null,
		        'redirect_to' => $default_redirect
		);		

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		// Default set to no captcha
		$this->captcha = 'no';
		
		
		
		$sidebar_class = null;
		if ($use_in_sidebar) $sidebar_class = 'xoouserultra-sidebar';
		
		$display = null;
		$display .= '<div class="xoouserultra-wrap xoouserultra-login '.$sidebar_class.'">
					<div class="xoouserultra-inner xoouserultra-login-wrapper">';
		
		$display .= '<div class="xoouserultra-head">';
		    $display .='<div class="xoouserultra-left">';
		        $display .='<div class="xoouserultra-field-name xoouserultra-field-name-wide login-heading" id="login-heading-'.$this->login_code_count.'">'.__('Password Reset','').'</div>';
		    $display .='</div>';
		    $display .='<div class="xoouserultra-right"></div><div class="xoouserultra-clear"></div>';
		$display .= '</div>';
						
						$display .='<div class="xoouserultra-main">';
						
						/*Display errors*/
						if (isset($_GET['resskey']) && $_GET['resskey']!="")
						{
							
							//check if valid 
							$valid = $xoouserultra->userpanel->get_user_with_key($_GET['resskey']);
							
							if($valid)
							{
								$display .= $this->show_password_reset_form( $sidebar_class,  $args, $_GET['resskey']);
							
							}else{
								
								$display .= '<p>'.__('Oops! The link is not correct! ', 'xoousers').'</p>';
							
							
							}
							
							
						}
						
						
						
						

						$display .= '</div>
						
					</div>
				</div>';

		return $display;
		
	}
	
	/* Show login forms */
	public function show_password_reset_form( $sidebar_class=null, $args, $key) 
	{
		global $xoousers_login, $xoousers_captcha_loader;
		
		$display = null;		
		$display .= '<form action="" method="post" id="xoouserultra-passwordreset-form">';
		$display .= '<input type="hidden" class="xoouserultra-input" name="uultra_reset_key" id="uultra_reset_key" value="'.$key.'"/>';
		
		
		$meta="preset_password";
		$meta_2="preset_password_2";
		$placeholder = "";
		$login_btn_class = "";
		
		//field 1
		$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';		
		$display .= '<label class="xoouserultra-field-type" for="'.$meta.'">'; 		 
		$display .= '<span>'.__('Type New Password:', 'xoousers').'</span></label>';		
		$display .= '<div class="xoouserultra-field-value">';		
		$display .= '<input type="password" class="xoouserultra-input" name="'.$meta.'" id="'.$meta.'" value="" '.$placeholder.' />';		
		$display .= '</div>';		
		$display .= '</div><div class="xoouserultra-clear"></div>';
		
		//field 2
		$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';		
		$display .= '<label class="xoouserultra-field-type" for="'.$meta_2.'">'; 		 
		$display .= '<span>'.__('Re-type Password:', 'xoousers').'</span></label>';		
		$display .= '<div class="xoouserultra-field-value">';		
		$display .= '<input type="password" class="xoouserultra-input" name="'.$meta_2.'" id="'.$meta_2.'" value="" '.$placeholder.' />';		
		$display .= '</div>';		
		$display .= '</div><div class="xoouserultra-clear"></div>';
		
		$display .= '<div class="xoouserultra-clear"></div>';
		
		
		$display .= '<input type="submit" name="xoouserultra-login" class="xoouserultra-button xoouserultra-reset-confirm'.$login_btn_class.'" value="'.__('Reset Password','xoousers').'" id="xoouserultra-reset-confirm-pass-btn" />';
		
		$display .= '</br></br>';	
		
		$display.='<div class="xoouserultra-signin-noti-block" id="uultra-reset-p-noti-box"> </div>';	
		
		$display .= '</form>';		
		
		return $display;
	}

	/* Login Form on Front end */
	public function login( $args=array() ) {

		global $xoousers_login;
		
		// Increasing Counter for Shortcode number
		$this->login_code_count++;
		
		// Check if redirect to is not set and redirect to is availble in URL
		$default_redirect = $this->current_page;
		if(isset($_GET['redirect_to']) && $_GET['redirect_to']!='')
		    $default_redirect = $_GET['redirect_to'];
		
		/* Arguments */
		$defaults = array(
		        'use_in_sidebar' => null,
		        'redirect_to' => $default_redirect
		);		

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		// Default set to no captcha
		$this->captcha = 'no';
		
		if(isset($captcha))
		    $this->captcha = $captcha;

		
		$sidebar_class = null;
		if ($use_in_sidebar) $sidebar_class = 'xoouserultra-sidebar';
		
		$display = null;
		$display .= '<div class="xoouserultra-wrap xoouserultra-login '.$sidebar_class.'">
					<div class="xoouserultra-inner xoouserultra-login-wrapper">';
		
		$display .= '<div class="xoouserultra-head">';
		    $display .='<div class="xoouserultra-left">';
		        $display .='<div class="xoouserultra-field-name xoouserultra-field-name-wide login-heading" id="login-heading-'.$this->login_code_count.'">'.__('Login','').'</div>';
		    $display .='</div>';
		    $display .='<div class="xoouserultra-right"></div><div class="xoouserultra-clear"></div>';
		$display .= '</div>';
						
						$display .='<div class="xoouserultra-main">';
						
						/*Display errors*/
						if (isset($_POST['xoouserultra-login']))
						{
							$display .= $this->login->get_errors();
						}
						
						$display .= $this->show_login_form( $sidebar_class, $redirect_to , $args);

						$display .= '</div>
						
					</div>
				</div>';

		return $display;
		
	}
	
	/* Show login forms */
	public function show_login_form( $sidebar_class=null, $redirect_to=null, $args) 
	{
		global $xoousers_login, $xoousers_captcha_loader;
		
		$display = null;		
		$display .= '<form action="" method="post" id="xoouserultra-login-form-'.$this->login_code_count.'">';
		
		
		//get social sign up methods
		$display .= $this->get_social_buttons(__("Sign in ",'xoousers' ),$args);
		
		$display .='<h2>Sign in with email</h2>';	

		foreach($this->login_fields as $key=>$field) 
		{
			extract($field);
			
			if ( $type == 'usermeta') {
				
				$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';
				
				
				
				/* Show the label */
				$placeholder = '';
				$icon_name = '';
				$input_ele_class='';
				
				    if (isset($this->login_fields[$key]['name']) && $name) 
					{
				        $display .= '<label class="xoouserultra-field-type" for="'.$meta.'">'; 
						
						//icon
						if (isset($this->login_fields[$key]['icon']) && $icon) 
						{
							$display .= '<i class="fa fa-'.$icon.'"></i>';
						} else {
							$display .= '<i class="fa fa-none"></i>';
						}
					
						     
				        $display .= '<span>'.$name.'</span></label>';
				    
					} else {
						
				        $display .= '<label class="xoouserultra-field-type">&nbsp;</label>';
				    } 
								
				
				
				$display .= '<div class="xoouserultra-field-value">';
					
				$display .=$icon_name;
				
					switch($field) {
						case 'textarea':
							$display .= '<textarea class="xoouserultra-input'.$input_ele_class.'" name="'.$meta.'" id="'.$meta.'" '.$placeholder.'>'.$this->get_post_value($meta).'</textarea>';
							break;
						case 'text':
							$display .= '<input type="text" class="xoouserultra-input'.$input_ele_class.'" name="'.$meta.'" id="'.$meta.'" value="'.$this->get_post_value($meta).'" '.$placeholder.' />';
							
							if (isset($this->login_fields[$key]['help']) && $help != '') {
								$display .= '<div class="xoouserultra-help">'.$help.'</div><div class="xoouserultra-clear"></div>';
							}
							
							break;
						case 'password':
							$display .= '<input type="password" class="xoouserultra-input'.$input_ele_class.'" name="'.$meta.'" id="'.$meta.'" value="" '.$placeholder.' />';
							break;
					}
					
					if ($field == 'password') {
						
					}
					
					
					
				$display .= '</div>';

				$display .= '</div><div class="xoouserultra-clear"></div>';
			}
						
		}

		
		//$display.=$xoousers_captcha_loader->load_captcha($this->captcha);
		
		$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">
						<label class="xoouserultra-field-type xoouserultra-field-type-'.$sidebar_class.'">&nbsp;</label>
						<div class="xoouserultra-field-value">';
		
		if (isset($_POST['rememberme']) && $_POST['rememberme'] == 1)
		 {
		    $class = 'xoouserultra-icon-check';
		} else {
			
			 $class = 'xoouserultra-icon-check-empty';
		}
		
		
		// this is the Forgot Pass Link
		$forgot_pass = '<a href="#uultra-forgot-link" id="xoouserultra-forgot-pass-'.$this->login_code_count.'" class="xoouserultra-login-forgot-link" title="'.__('Forgot Password?','xoousers').'">'.__('Forgot Password?','xoousers').'</a>';
		
		// this is the Register Link
		$register_link = site_url('/wp-login.php?action=register');
		
		if ($this->get_option('register_redirect') != '') 
		    $register_link =  $this->get_option('register_redirect');
		
		$register_link = '<a href="'.$register_link.'" class="xoouserultra-login-register-link">'.__('Register','xoousers').'</a>';
    		
		$remember_me_class = '';
		$login_btn_class = '';
		
		if($sidebar_class != null)
		{
		    $login_btn_class = ' in_sidebar';
		    $remember_me_class = ' in_sidebar_remember';
		}
		    
		
		$display .= '<div class="xoouserultra-rememberme'.$remember_me_class.'">
		<i class="'.$class.'"></i>'.__('Remember me','xoousers').'
		<input type="hidden" name="rememberme" id="rememberme-'.$this->login_code_count.'" value="0" />
		</div><input type="submit" name="xoouserultra-login" class="xoouserultra-button xoouserultra-login'.$login_btn_class.'" value="'.__('Log In','xoousers').'" /><br />'.$forgot_pass.' | '.$register_link;
		
		
		$display .= ' </div>
					</div><div class="xoouserultra-clear"></div>';
		
		$display .= '<input type="hidden" name="redirect_to" value="'.$redirect_to.'" />';
		
		$display .= '</form>';
		
		
		
		
		// this is the forgot password form
		$forgot_pass = '';
		
		$forgot_pass .= '<div class="xoouserultra-forgot-pass" id="xoouserultra-forgot-pass-holder">';
		
		
		$forgot_pass .= "<div class='notimessage'>";
		
		$forgot_pass .= "<div class='uupublic-ultra-warning'>".__(" A quick access link will be sent to your email that will let you get in your account and change your password. ", 'xoousers')."</div>";
		
		$forgot_pass .= "</div>";
		
		
		$forgot_pass .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';
		
		
		
		$forgot_pass .= '<label class="xoouserultra-field-type" for="user_name_email-'.$this->login_code_count.'"><i class="fa fa-user"></i><span>'.__('Username or Email','xoousers').'</span></label>';
		$forgot_pass .= '<div class="xoouserultra-field-value">';
		
		
		$forgot_pass .= '<input type="text" class="xoouserultra-input" name="user_name_email" id="user_name_email" value=""></div>';
		$forgot_pass .= '</div>';
		    
		$forgot_pass.='<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';
		$forgot_pass.='<label class="xoouserultra-field-type xoouserultra-blank-lable">&nbsp;</label>';
		$forgot_pass.='<div class="xoouserultra-field-value">';
		$forgot_pass.='<div class="xoouserultra-back-to-login">';
		$forgot_pass.='<a href="javascript:void(0);" title="'.__('Back to Login','xoousers').'" id="xoouserultra-back-to-login-'.$this->login_code_count.'">'.__('Back to Login','xoousers').'</a> | '.$register_link;
		
		$forgot_pass.='</div>';
		
	
		            
		            $forgot_pass.='<input type="button" name="xoouserultra-forgot-pass" id="xoouserultra-forgot-pass-btn-confirm" class="xoouserultra-button xoouserultra-login" value="'.__('Send me Password','xoousers').'">';
					
					$forgot_pass.='<div class="xoouserultra-signin-noti-block" id="uultra-signin-ajax-noti-box"> ';
		
		$forgot_pass.='</div>';
		            
		        $forgot_pass.='</div>';
				
					
		
		
		    $forgot_pass.='</div>';
		    
		    
		    
		$forgot_pass .= '</div>';	
		
		$display.=$forgot_pass;
		
		
		return $display;
	}
	
	/* Show registration form */
	function show_registration_form( $args=array() )
	{

		global $post, $xoousers_register;		
		
		// Loading scripts and styles only when required
		/* Password Stregth Checker Script */
		if(!wp_script_is('form-validate'))
		{
		    wp_register_script('form-validate', xoousers_url.'js/form-validate.js', array('jquery') );
		    wp_enqueue_script('form-validate');
		    
        $validate_strings = array(
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'ErrMsg'   => array(
                        'similartousername' => __('Your password is too similar to your username.','xoousers'),
                        'mismatch' => __('Both passwords do not match.','xoousers'),
                        'tooshort' => __('Your password is too short.','xoousers'),
                        'veryweak' => __('Your password strength is too weak.','xoousers'),
                        'weak' => __('Your password strength weak.','xoousers'),
                        'usernamerequired' => __('Please provide username.','xoousers'),
                        'emailrequired' => __('Please provide email address.','xoousers'),
                        'validemailrequired' => __('Please provide valid email address.','xoousers'),
                        'usernameexists' => __('That username is already taken, please try a different one.','xoousers'),
                        'emailexists' => __('The email you entered is already registered. Please try a new email or log in to your existing account.','xoousers')
                    ),
            'MeterMsg' => array(
                        'similartousername' => __('Your password is too similar to your username.','xoousers'),
                        'mismatch' => __('Both passwords do not match.','xoousers'),
                        'tooshort' => __('Your password is too short.','xoousers'),
                        'veryweak' => __('Your password strength is too weak.','xoousers'),
                        'weak' => __('Your password strength weak.','xoousers'),
                        'good' => __('Good','xoousers'),
                        'strong' => __('Strong','xoousers')
                    ),
            'Err'     => __('ERROR','xoousers')
        );

        wp_localize_script( 'form-validate', 'Validate', $validate_strings );
		}
		

		
		
		/* Arguments */
		$defaults = array(
        'use_in_sidebar' => null,
        'redirect_to' => null
        		    
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		$pic_class = 'xoouserultra-pic';
		if(is_safari())
		    $pic_class = 'xoouserultra-pic safari';
		
		// Default set to blank
		$this->captcha = '';
		if(isset($captcha))
		    $this->captcha = $captcha;
		
		$sidebar_class = null;
		if ($use_in_sidebar) $sidebar_class = 'xoouserultra-sidebar';
		
		$display = null;
		
		
		if(get_option('users_can_register') == '1')
		{
		
		    $display .= '<div class="xoouserultra-wrap xoouserultra-registration '.$sidebar_class.'">
					<div class="xoouserultra-inner">
						
						<div class="xoouserultra-head">
							
							<div class="xoouserultra-left">
								<div class="'.$pic_class.'">';
								
								if (isset($_POST['xoouserultra-register']) && $_POST['user_email'] != '' ) {
									//$display .= $this->pic($_POST['user_email'], 50);
								} else {
									//$display .= $this->pic('john@doe.com', 50);
								}
								
								$display .= '</div>';
								
								$display .= '<div class="xoouserultra-name">
								
												<div class="xoouserultra-field-name xoouserultra-field-name-wide">';
												
												
								$display .= __('Sign Up','xoousers');
											
												
								$display .= '</div>
												
										</div>';
								
							$display .= '</div>';
							
							
							$display .= '<div class="xoouserultra-right">';
								
							$display .= '</div><div class="xoouserultra-clear"></div>
							
						</div>
						
						<div class="xoouserultra-main">
							
							<div class="xoouserultra-errors" style="display:none;" id="pass_err_holder">
							    <span class="xoouserultra-error xoouserultra-error-block" id="pass_err_block">
							        <i class="xoouserultra-icon-remove"></i><strong>ERROR:</strong> Please enter a username.
							    </span>
							</div>
							';
							
						/*Display errors*/
						if (isset($_POST['xoouserultra-register-form'])) 
						{
							$display .= $this->register->get_errors();
						}
						
						$display .= $this->display_the_registeration_form( $sidebar_class, $redirect_to, $args );

						$display .= '</div>
						
					</div>
				</div>';
		}else{
			
			//the registration is disabled
			
		    $display .= '<div class="xoouserultra-wrap xoouserultra-registration '.$sidebar_class.'"><div class="xoouserultra-inner"><div class="xoouserultra-head">';
		    if($this->get_option('html_registration_disabled') != '')
			{
				
		        $display.=$this->get_option('html_registration_disabled');
				
			}else{
				
		        $display.=__('User registration is currently not allowed.','xoousers');
			
			}
		    $display .= '</div></div></div>';
		}
		
		return $display;
		
	}
	
	/* This is the Registration Form */
	function display_the_registeration_form( $sidebar_class=null, $redirect_to=null , $args)
	{
		global $xoousers_register, $predefined, $xoousers_captcha_loader;
		$display = null;
		
		// Optimized condition and added strict conditions
		if (!isset($xoousers_register->registered) || $xoousers_register->registered != 1)
		{
		
		$display .= '<form action="" method="post" id="xoouserultra-registration-form">';
		
		
		//get social sign up methods
		$display .= $this->get_social_buttons(__("Sign up ",'xoousers'), $args);		
		
		$display .= '<div class="xoouserultra-field xoouserultra-seperator xoouserultra-edit xoouserultra-edit-show">'.__('Account Info','xoousers').'</div>';
			
		/* These are the basic registrations fields */
		
		foreach($this->registration_fields as $key=>$field) 
		{
			extract($field);
			
			if ( $type == 'usermeta') {
				
				$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';
				
				/* Show the label */
				if (isset($this->registration_fields[$key]['name']) && $name) 
				{
					$display .= '<label class="xoouserultra-field-type" for="'.$meta.'">';
					
					if (isset($this->registration_fields[$key]['icon']) && $icon)
					 {
						$display .= '<i class="fa fa-'.$icon.'"></i>';
					} else {
						$display .= '<i class="fa fa-none"></i>';
					}
					$display .= '<span>'.$name.'</span></label>';
				} else {
					$display .= '<label class="xoouserultra-field-type">&nbsp;</label>';
				}
				
				if(!isset($required))
				    $required = 0;
				
				$required_class = '';
				
				if($required == 1 && in_array($field, $this->include_for_validation))
				{
					$required_class = ' required';
				}
				
				$display .= '<div class="xoouserultra-field-value">';
					
					switch($field) {
						
						case 'textarea':
							$display .= '<textarea class="xoouserultra-input'.$required_class.'" name="'.$meta.'" id="reg_'.$meta.'" title="'.$name.'">'.$this->get_post_value($meta).'</textarea>';
							break;
						
						case 'text':
							$display .= '<input type="text" class="xoouserultra-input'.$required_class.'" name="'.$meta.'" id="reg_'.$meta.'" value="'.$this->get_post_value($meta).'" title="'.$name.'" />';
							
							if (isset($this->registration_fields[$key]['help']) && $help != '') {
								$display .= '<div class="xoouserultra-help">'.$help.'</div><div class="xoouserultra-clear"></div>';
							}
							
							break;
							
							case 'datetime':
							    
							    $display .= '<input type="text" class="xoouserultra-input'.$required_class.' xoouserultra-datepicker" name="'.$meta.'" id="reg_'.$meta.'" value="'.$this->get_post_value($meta).'" title="'.$name.'" />';
							    
							    if (isset($this->registration_fields[$key]['help']) && $help != '') {
							        $display .= '<div class="xoouserultra-help">'.$help.'</div><div class="xoouserultra-clear"></div>';
							    }
							    break;
							
						case 'password':

							$display .= '<input type="password" class="xoouserultra-input password'.$required_class.'" name="'.$meta.'" id="reg_'.$meta.'" value="" autocomplete="off" title="'.$name.'" />';
							
							if (isset($this->registration_fields[$key]['help']) && $help != '') {
								$display .= '<div class="xoouserultra-help">'.$help.'</div><div class="xoouserultra-clear"></div>';
							}

							break;
							
						case 'password_indicator':
							$display .= '<div class="password-meter"><div class="password-meter-message" id="password-meter-message">&nbsp;</div></div>';
							break;
							
					}
					
					/*User can hide this from public*/
					if (isset($this->registration_fields[$key]['can_hide']) && $can_hide == 1) 
					{
						
						$display .= '<div class="xoouserultra-hide-from-public">
										<i class="fa fa-check-empty"></i>'.__('Hide from Public','xoousers').'
										<input type="hidden" name="hide_'.$meta.'" id="hide_'.$meta.'" value="" />
									</div>';

					}
					
					
					
				$display .= '</div>';

				$display .= '</div><div class="xoouserultra-clear"></div>';
			}
			
								
		}

		
		/* Get end of array */
		$array = get_option('usersultra_profile_fields');

		foreach($array as $key=>$field) 
		{
		     
		    $exclude_array = array('user_pass', 'user_pass_confirm', 'user_email');
		    if(isset($field['meta']) && in_array($field['meta'], $exclude_array))
		    {
		        unset($array[$key]);
		    }
		}
		
		$i_array_end = end($array);
		
		if(isset($i_array_end['position']))
		{
		    $array_end = $i_array_end['position'];
		    
			if (isset($array[$array_end]['type']) && $array[$array_end]['type'] == 'seperator') 
			{
				if(isset($array[$array_end]))
				{
					unset($array[$array_end]);
				}
			}
		}
		
		
		/*Display custom profile fields added by the user*/
		
		foreach($array as $key => $field) 
		{

			extract($field);
			
			// WP 3.6 Fix
			if(!isset($deleted))
			    $deleted = 0;
			
			if(!isset($private))
			    $private = 0;
			
			if(!isset($required))
			    $required = 0;
			
			$required_class = '';
			if($required == 1 && in_array($field, $this->include_for_validation))
			{
			    $required_class = ' required';
			}
			
			
			/* This is a Fieldset seperator */
						
			/* separator */
            if ($type == 'separator' && $deleted == 0 && $private == 0 && isset($array[$key]['show_in_register']) && $array[$key]['show_in_register'] == 1) 
			{
                   $display .= '<div class="xoouserultra-field xoouserultra-seperator xoouserultra-edit xoouserultra-edit-show">'.$name.'</div>';
				   
            }
			
			//this hack will be removed soon
			
			if ($type == 'seperator' && $deleted == 0 && $private == 0 && isset($array[$key]['show_in_register']) && $array[$key]['show_in_register'] == 1) 
			{
                   $display .= '<div class="xoouserultra-field xoouserultra-seperator xoouserultra-edit xoouserultra-edit-show">'.$name.'</div>';
				   
            }
				
			
			
			//if ( $type == 'usermeta' && $deleted == 0 && $private == 0) // this is a meta
			
			//{
				
			if ($type == 'usermeta' && $deleted == 0 && $private == 0 && isset($array[$key]['show_in_register']) && $array[$key]['show_in_register'] == 1) 
			{
				
				
				$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">';
				
				/* Show the label */
				if (isset($array[$key]['name']) && $name)
				 {
					$display .= '<label class="xoouserultra-field-type" for="'.$meta.'">';						
					$display .= '<span>'.$name.'</span></label>';
				} else {
					$display .= '<label class="xoouserultra-field-type">&nbsp;</label>';
				}
				
				$display .= '<div class="xoouserultra-field-value">';
					
					switch($field) {
					
						case 'textarea':
							$display .= '<textarea class="xoouserultra-input'.$required_class.'" name="'.$meta.'" id="'.$meta.'" title="'.$name.'">'.$this->get_post_value($meta).'</textarea>';
							break;
							
						case 'text':
							$display .= '<input type="text" class="xoouserultra-input'.$required_class.'" name="'.$meta.'" id="'.$meta.'" value="'.$this->get_post_value($meta).'"  title="'.$name.'" />';
							break;							
							
						case 'datetime':
						    $display .= '<input type="text" class="xoouserultra-input'.$required_class.' xoouserultra-datepicker" name="'.$meta.'" id="'.$meta.'" value="'.$this->get_post_value($meta).'"  title="'.$name.'" />';
						    break;
							
						case 'select':
						
							if (isset($array[$key]['predefined_options']) && $array[$key]['predefined_options']!= '' && $array[$key]['predefined_options']!= '0' )
							
							{
								$loop = $this->commmonmethods->get_predifined( $array[$key]['predefined_options'] );
								
							}elseif (isset($array[$key]['choices']) && $array[$key]['choices'] != '') {
								
								$loop = explode(PHP_EOL, $choices);
							}
							
							if (isset($loop)) 
							{
								$display .= '<select class="xoouserultra-input'.$required_class.'" name="'.$meta.'" id="'.$meta.'" title="'.$name.'">';
								
								foreach($loop as $option)
								{
									
								$option = trim($option);
								    
								$display .= '<option value="'.$option.'" '.selected( $this->get_post_value($meta), $option, 0 ).'>'.$option.'</option>';
								}
								$display .= '</select>';
							}
							$display .= '<div class="xoouserultra-clear"></div>';
							break;
							
						case 'radio':
						
							if (isset($array[$key]['choices']))
							{
								$loop = explode(PHP_EOL, $choices);
							}
							if (isset($loop) && $loop[0] != '') 
							{
							  $counter =0;
							  
								foreach($loop as $option)
								{
								    if($counter >0)
								        $required_class = '';
								    
								    $option = trim($option);
									$display .= '<label class="xoouserultra-radio"><input type="radio" class="'.$required_class.'" title="'.$name.'" name="'.$meta.'" value="'.$option.'" '.checked( $this->get_post_value($meta), $option, 0 );
									$display .= '/> '.$option.'</label>';
									
									$counter++;
									
								}
							}
							$display .= '<div class="xoouserultra-clear"></div>';
							break;
							
						case 'checkbox':
						
							if (isset($array[$key]['choices'])) 
							{
								$loop = explode(PHP_EOL, $choices);
							}
							
							if (isset($loop) && $loop[0] != '') 
							{
							  $counter =0;
							  
								foreach($loop as $option)
								{
								   
								   if($counter >0)
								        $required_class = '';
								  
								  $option = trim($option);
									$display .= '<label class="xoouserultra-checkbox"><input type="checkbox" class="'.$required_class.'" title="'.$name.'" name="'.$meta.'[]" value="'.$option.'" ';
									if (is_array($this->get_post_value($meta)) && in_array($option, $this->get_post_value($meta) )) {
									$display .= 'checked="checked"';
									}
									$display .= '/> '.$option.'</label>';
									
									$counter++;
								}
							}
							$display .= '<div class="xoouserultra-clear"></div>';
							break;
							
						case 'password':
						
							$display .= '<input type="password" class="xoouserultra-input'.$required_class.'" title="'.$name.'" name="'.$meta.'" id="'.$meta.'" value="'.$this->get_post_value($meta).'" />';
							
							if ($meta == 'user_pass') 
							{
								
							$display .= '<div class="xoouserultra-help">'.__('If you would like to change the password type a new one. Otherwise leave this blank.','xoousers').'</div>';
							
							} elseif ($meta == 'user_pass_confirm') {
								
							$display .= '<div class="xoouserultra-help">'.__('Type your new password again.','xoousers').'</div>';
							
							}
							break;
							
					}
					
					/*User can hide this from public*/
					if (isset($array[$key]['can_hide']) && $can_hide == 1)
					{
						
						$display .= '<div class="xoouserultra-hide-from-public">
										<i class="fa fa-check-empty"></i>'.__('Hide from Public','xoousers').'
										<input type="hidden" name="hide_'.$meta.'" id="hide_'.$meta.'" value="" />
									</div>';

					} elseif ($can_hide == 0 && $private == 0) {
					   
					}
					
					//validation message
					
					$display .= '<div class="xoouserultra-warning-validation-message" id="uultra-val-message-'.$meta.'">
										<div class="uupvalidation-ultra-warning">'.__('This field is required','xoousers').'</div>
								</div>';
					
					
				$display .= '</div>';
				$display .= '</div><div class="xoouserultra-clear"></div>';
			}
		}
		
		//$display.=$xoousers_captcha_loader->load_captcha($this->captcha);
		
		if($this->use_captcha == 'yes')
		{
		    
		}else{
			
		  $display.='<input type="hidden" name="no_captcha" value="yes" />';
		}
		
		
		/*If we are using Paid Registration*/		
		if($this->get_option('registration_rules')==4)
		{
			$display .= '<div class="xoouserultra-field xoouserultra-seperator xoouserultra-edit xoouserultra-edit-show">'.__('Payment Information','xoousers').'</div>';
			
			
			$display .= '<div class="xoouserultra-package-list">';
			
			$display .= $this->paypal->get_packages();
			
			$display .= '</div>';
			
			
		
		}
		
		/*If mailchimp*/		
		if($this->get_option('mailchimp_active')==1 && $this->get_option('mailchimp_api')!="")
		{
			$display .= '<div class="xoouserultra-field xoouserultra-seperator xoouserultra-edit xoouserultra-edit-show"></div>';
						
			$display .= '<input type="checkbox"  title="Receive Daily Updates" name="uultra-mailchimp-confirmation" value="1" > '.$this->get_option('mailchimp_text').' ';
			
			
		
		}
		
		
		$display .= '<div class="xoouserultra-field xoouserultra-edit xoouserultra-edit-show">
						<label class="xoouserultra-field-type xoouserultra-field-type-'.$sidebar_class.'">&nbsp;</label>
						<div class="xoouserultra-field-value">
						    <input type="hidden" name="xoouserultra-register-form" value="xoouserultra-register-form" />
							<input type="button" name="xoouserultra-register" id="xoouserultra-register-btn" class="xoouserultra-button" value="'.__('Register','xoousers').'" />
						</div>
					</div><div class="xoouserultra-clear"></div>';
					
					
		if ($redirect_to != '' )
		{
			$display .= '<input type="hidden" name="redirect_to" value="'.$redirect_to.'" />';
		}
		
		$display .= '</form>';
		
		} 
		
		
		return $display;
	}
	
	/**
	 * Users Dashboard
	 */
	public function show_usersultra_my_account()
	{
		global $wpdb, $current_user;		
		$user_id = get_current_user_id();
		
		$display = $this->usersultra_get_template('dashboard');	
		
		return $display;
	
	}
	
	/**
	 * Display Minified Profile
	 */
	public function show_minified_profile($atts)
	{
		 return $this->userpanel->show_minified_profile($atts);		
			
	}	
	
	/**
	 * Display Front Publisher
	 */
	public function show_front_publisher($atts)
	{
		 return $this->publisher->show_front_publisher($atts);		
			
	}	
	
	/**
	 * Top Rated Photos
	 */
	public function show_top_rated_photos($atts)
	{
		 return $this->photogallery->show_top_rated_photos($atts);		
			
	}
	
	/**
	 * Top Rated Photos
	 */
	public function show_latest_photos($atts)
	{
		 return $this->photogallery->show_latest_photos($atts);		
			
	}
	
	/**
	 * Photo Grid
	 */
	public function show_photo_grid($atts)
	{
		 return $this->photogallery->show_photo_grid($atts);		
			
	}
	
	/**
	 * Featured Users
	 */
	public function show_featured_users($atts)
	{
		 return $this->userpanel->show_featured_users($atts);		
			
	}
	
	
	/**
	 * Promoted Users
	 */
	public function show_promoted_users($atts)
	{
		 return $this->userpanel->show_promoted_users($atts);		
			
	}
	
	/**
	 * Promoted Photos
	 */
	public function show_promoted_photos($atts)
	{
		 return $this->photogallery->show_promoted_photos($atts);		
			
	}
	
	/**
	 * Latest Users
	 */
	public function show_latest_users($atts)
	{
		 return $this->userpanel->show_latest_users($atts);		
			
	}
	
	/**
	 * Featured Users
	 */
	public function show_top_rated_users($atts)
	{
		 return $this->userpanel->show_top_rated_users($atts);		
			
	}
	
	/**
	 * Top Most Visited Users
	 */
	public function show_most_visited_users($atts)
	{
		 return $this->userpanel->show_most_visited_users($atts);		
			
	}
	
	/**
	 * Public Profile
	 */
	public function show_pulic_profile($atts)
	{
		 $this->userpanel->show_public_profile($atts);		
			
	}
	
	/**
	 * Get Templates
	 */
	
	public function usersultra_get_template($template)
	{
		$display = "";
		$display .= require_once(xoousers_path.'/templates/'.xoousers_template."/".$template.".php");	
	
	}
	
	

	
	public function get_social_buttons ($action_text, $atts)
	{
		require_once(xoousers_path."libs/fbapi/src/facebook.php");
		
		$display ="";
		
		extract( shortcode_atts( array(
			'social_conect' => '',
			
		), $atts ) );
		
		
		if($this->get_option('registration_rules')!=4) // Social media is not able when using paid registrations
		{
		
			$FACEBOOK_APPID = $this->get_option('social_media_facebook_app_id');  
			$FACEBOOK_SECRET = $this->get_option('social_media_facebook_secret');
							
			$config = array();
			$config['appId'] = $FACEBOOK_APPID;
			$config['secret'] = $FACEBOOK_SECRET;
			
			$web_url = site_url()."/"; 
			
			
			//Yahoo
			require_once(xoousers_path."libs/openid/openid.php");
			
			$openid = new LightOpenID($web_url);
			$openid_yahoo = new LightOpenID($web_url);
			
			//google
			$openid->identity = 'https://www.google.com/accounts/o8/id';
			$openid->required = array(
			  'namePerson/first',
			  'namePerson/last',
			  'contact/email',
			);
			$openid->returnUrl = $web_url;
			$auth_url_google = $openid->authUrl();
			
			//yahoo
			
			$openid_yahoo->identity = 'https://me.yahoo.com';
			$openid_yahoo->required = array(
			  'namePerson',
			  'namePerson/first',
			  'namePerson/last',
			  'contact/email',
			);
			
			$openid_yahoo->returnUrl = $web_url;
			$auth_url_yahoo = $openid_yahoo->authUrl();
			
			$atleast_one = false;
			
			
			if($this->get_option('social_media_fb_active')==1)
			{
				$atleast_one = true;
				$facebook = new Facebook($config);			
				
				
				
				$params = array(
						  'scope' => 'read_stream, email, friends_likes',
						  'redirect_uri' => $web_url
						);
						
				$loginUrl = $facebook->getLoginUrl($params);
			
				//Facebook
				$display .='<div class="txt-center FacebookSignIn">
				
				       	               	
						<a href="'.$loginUrl.'" class="btn-facebook" >
							<span class="icon-facebook"> <img src="'.xoousers_url.'img/socialicons/facebook.png" ></span>'.$action_text.' with Facebook </a>
					
					</div>';
					
			}
			
			if($this->get_option('social_media_yahoo')==1)
			{
				$atleast_one = true;
			
				//Yahoo
				$display .='<div class="txt-center YahooSignIn">	               	
							<a href="'.$auth_url_yahoo.'" class="btn-yahoo" >
							<span class="icon-yahoo"><img src="'.xoousers_url.'img/socialicons/yahoo.png" ></span>'.$action_text.' with Yahoo </a>
					
					</div>';
		     }
			 
			if($this->get_option('social_media_google')==1)
			{
				$atleast_one = true;
			
				//Google
				$display .='<div class="txt-center GoogleSignIn">	               	
						<a href="'.$auth_url_google.'" class="btn-google" ">
							<span class="icon-google"><img src="'.xoousers_url.'img/socialicons/googleplus.png" ></span>'.$action_text.' with Google </a>
					
					</div>';
			}
			
			if($this->get_option('social_media_linked_active')==1)
			{
				$atleast_one = true;
				
				if (!isset($_REQUEST['oauth_token']))
				{
					$requestlink = $this->get_linkein_auth_link();	
				
				}
				
				
				//LinkedIn
				$display .='<div class="txt-center LinkedSignIn">	               	
							<a href="'.$requestlink.'" class="btn-linkedin" >
								<span class="icon-linkedin"><img src="'.xoousers_url.'img/socialicons/linkedin.png" ></span>'.$action_text.' with LinkedIn </a>
					
					</div>';
			}	
			
			if($atleast_one)
			{
				$display .='<div class="xoouserultra-or-divider">	<div>or</div>	</div>';
			
			}
		
		
		}
	return $display;
		
	}
	
	public function get_linkein_auth_link ()
	{
		$requestlink ="";
		
		//LinkedIn lib
		 require_once(xoousers_path."libs/linkedin/oauth/linkedinoauth.php");		 
		 
		 $oauthstate = $this->get_linkedin_oauth_token();		 
		 $tokenpublic =  $oauthstate['request_token'];
		 
		 
		 $to = new LinkedInOAuth($this->get_option('social_media_linkedin_api_public'), $this->get_option('social_media_linkedin_api_private'));
		 $requestlink = $to->getAuthorizeURL($tokenpublic, $this->get_current_url());
	
		 
		 return $requestlink;
				
	
	
	}
	
	//used only once we've got a oauth_token and oauth_verifier	
	function get_linkedin_access_token($oauthstate)
	{
		require_once(xoousers_path."libs/linkedin/oauth/linkedinoauth.php");
		
		$requesttoken = $oauthstate['request_token'];
		$requesttokensecret = $oauthstate['request_token_secret'];
		
		$urlaccessverifier = $_REQUEST['oauth_verifier'];
	
		error_log("Creating API with $requesttoken, $requesttokensecret");			
		
		$to = new LinkedInOAuth(
				$this->get_option('social_media_linkedin_api_public'), 
				$this->get_option('social_media_linkedin_api_private'),
				$requesttoken,
				$requesttokensecret
		);
			
		$tok = $to->getAccessToken($urlaccessverifier);
		
		//print_r($tok);
		
		$accesstoken = $tok['oauth_token'];
		$accesstokensecret = $tok['oauth_token_secret'];
		
		$oauthstate['access_token'] =  $accesstoken;
		$oauthstate['access_token_secret'] =  $accesstokensecret;
		
		return $oauthstate;
			
	
	}
	
	function get_linkedin_oauth_token()
	{
		
		require_once(xoousers_path."libs/linkedin/oauth/linkedinoauth.php");
		$oauthstate = $this->get_linkedin_oauth_state();
		
			//echo "not set aut state";
			error_log("No OAuth state found");
	
			$to = new LinkedInOAuth($this->get_option('social_media_linkedin_api_public'), $this->get_option('social_media_linkedin_api_private'));
			
			// This call can be unreliable for some providers if their servers are under a heavy load, so
			// retry it with an increasing amount of back-off if there's a problem.
			$maxretrycount = 1;
			$retrycount = 0;
			while ($retrycount<$maxretrycount)
			{		
				$tok = $to->getRequestToken();
				if (isset($tok['oauth_token'])&&
					isset($tok['oauth_token_secret']))
					break;
				
				$retrycount += 1;
				sleep($retrycount*5);
			}
			
			$tokenpublic = $tok['oauth_token'];
			$tokenprivate = $tok['oauth_token_secret'];
			$state = 'start';
			
			// Create a new set of information, initially just containing the keys we need to make
			// the request.
			$oauthstate = array(
				'request_token' => $tokenpublic,
				'request_token_secret' => $tokenprivate,
				'access_token' => '',
				'access_token_secret' => '',
								
				'state' => $state,
			);
			
			//SET IN DB TEMP TOKEN
			$temp_user_session_id = session_id();			
			update_option('uultra_linkedin_'.$temp_user_session_id, $oauthstate);				
			$oauthstate =  get_option('uultra_linkedin_'.$temp_user_session_id);
	
			$this->set_linkedin_oauth_state($oauthstate);
			
			
			
			return $oauthstate;
	
	}
	
	function get_linkedin_oauth_state()
	{
		if (empty($_SESSION['linkedinoauthstate']))
			return null;
			
		$result = $_SESSION['linkedinoauthstate'];
	
		error_log("Found state ".print_r($result, true));
		
		//print_r($_SESSION);
		
			
		return $result;
	}
	
	// Updates the information about the user's progress through the oAuth process.
	function set_linkedin_oauth_state($state)
	{
		error_log("Setting OAuth state to - ".print_r($state, true));
		$_SESSION['linkedinoauthstate'] = $state;
		
		
	}
	
		
	public function get_current_url()
	{
		$result = 'http';
		$script_name = "";
		if(isset($_SERVER['REQUEST_URI'])) 
		{
			$script_name = $_SERVER['REQUEST_URI'];
		} 
		else 
		{
			$script_name = $_SERVER['PHP_SELF'];
			if($_SERVER['QUERY_STRING']>' ') 
			{
				$script_name .=  '?'.$_SERVER['QUERY_STRING'];
			}
		}
		
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') 
		{
			$result .=  's';
		}
		$result .=  '://';
		
		if($_SERVER['SERVER_PORT']!='80')  
		{
			$result .= $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$script_name;
		} 
		else 
		{
			$result .=  $_SERVER['HTTP_HOST'].$script_name;
		}
	
		return $result;
	}
	
	/* get setting */
	function get_option($option) 
	{
		$settings = get_option('userultra_options');
		if (isset($settings[$option])) 
		{
			return $settings[$option];
			
		}else{
			
		    return '';
		}
		    
	}
	
	/*Post value*/
	function get_post_value($meta) {
				
		if (isset($_POST['xoouserultra-register-form'])) {
			if (isset($_POST[$meta]) ) {
				return $_POST[$meta];
			}
		} else {
			if (strstr($meta, 'country')) {
			return 'United States';
			}
		}
	}
	
		

	
		
		
	
}
?>