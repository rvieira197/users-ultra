(function() {
    tinymce.PluginManager.add('USERSULTRAShortcodes', function( editor, url ) {
        editor.addButton( 'uultra_shortcodes_button', {
            title: 'Users Ultra Shortcodes',
            type: 'menubutton',
            icon: 'icon mce_uultra_shortcodes_button',
            menu: [
                
                {
                    text: 'Login Forms',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Basic Login Form',
                            value: '[usersultra_login ]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },
                        {
                            text: 'Login Form with Custom Redirect',
                            value: '[usersultra_login  redirect_to="http://url_here"]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        }
                    ]
                },
               //registration form
			   {
                    text: 'Registration Form',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Front-end Registration Form',
                            value: '[usersultra_registration]',
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } 
                    ]
                },
				
				//member directory
			   {
                    text: 'Members Directory',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Basic Directory',
                            value: "[usersultra_directory list_per_page=6 optional_fields_to_display='social,rating,country,description' display_country_flag='both' pic_boder_type='rounded']",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } 
                    ]
                },
				
				//image grids
			   {
                    text: 'Image Grids',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Filter by Categories',
                            value: "[usersultra_images_grid box_border='rounded' box_shadow='shadow' searcher_type='advanced'] ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } ,
						
						{
                            text: 'Lightbox Gallery',
                            value: "[usersultra_images_grid box_border='rounded' box_shadow='shadow' searcher_type='advanced' photo_open_type='lightbox']  ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } 
                    ]
                },
				
				
				//users
			   {
                    text: 'Users Shortcodes',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Featured Users',
                            value: "[usersultra_users_featured users_list='55,59,60' optional_fields_to_display='rating']",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } ,
						
						{
                            text: 'Top Raged Users',
                            value: "[usersultra_users_top_rated optional_fields_to_display='friend,rating,social,country'  display_country_flag='both'] ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        },						
						{
                            text: 'Most Visited Users',
                            value: "[usersultra_users_most_visited optional_fields_to_display='friend,social' pic_size='80' ]",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        }					
						,						
						{
                            text: 'User Spotlights',
                            value: "[usersultra_users_promote optional_fields_to_display='rating,social' users_list='59'  display_country_flag='both']",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        }
						
						,						
						{
                            text: 'Latest Users',
                            value: "[usersultra_users_latest optional_fields_to_display='social' ]",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        }
						
						,						
						{
                            text: 'Custom Users Profiles',
                            value: "[usersultra_profile template_width='80%' user_id= '59' pic_boder_type='rounded' pic_type='avatar' optional_fields_to_display='rating,social,country,description' display_country_flag='both' display_private_message='yes' ]",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } 
						
						
                    ]
                },
				
				
				//images
			   {
                    text: 'Images',
                    value: 'Text from menu item II',
                    onclick: function() {
                        editor.insertContent(this.value());
                    },
                    menu: [
                        {
                            text: 'Top Rated',
                            value: "[usersultra_photo_top_rated ] ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } ,
						
						{
                            text: 'Photo Spotlights',
                            value: "[usersultra_photos_promote photo_list='95,91' photo_type='photo_large'] ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        }						
						,						
						{
                            text: 'Latest Uploaded Photos',
                            value: "[usersultra_photo_latest] ",
                            onclick: function(e) {
                                e.stopPropagation();
                                editor.insertContent(this.value());
                            }       
                        } 
                    ]
                },
				
				
			   
				
				
           ]
        });
    });
})();