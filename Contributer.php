<?php

class Contributer {

    private $plugin_directory;

    private $plugin_url;


    public function __construct( $file ) {

        $this->plugin_directory = plugin_dir_path( $file );
        $this->plugin_url = plugin_dir_url( $file );

        //enque js scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'load_js' ) );

        //enqeue css styles
        add_action( 'wp_enqueue_scripts', array( $this, 'load_css' ) );

        //handling redirects
        add_action( 'template_redirect', array( $this, 'redirect' ) );

        //add filter for custom avatars
        add_filter( 'get_avatar' , array( $this, 'contributer_avatar' ) , 1 , 5 );

        $login_renderer = new Contributer_Login( $this->plugin_directory );
        add_shortcode( 'contributer_login', array( $login_renderer, 'contributer_login' ) );

        $profile_renderer = new Contributer_Profile( $this->plugin_directory );
        add_shortcode( 'contributer_profile', array( $profile_renderer, 'contributer_profile' ) );

        $contribute_renderer = new Contributer_Contribute( $this->plugin_directory );
        add_shortcode( 'contributer_contribute', array( $contribute_renderer, 'contributer_contribute' ) );

        new SenseiAdminPanel( $this->plugin_url.'/framework/modules/sensei-options', $this->define_page_options() );
        $this->register_user_custom_fields();
        $this->temp_google_login();
    }

        
        
    public function contributer_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        $user = false;

        if ( is_numeric( $id_or_email ) ) {
            $id = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );
        } 
        elseif ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->user_id ) ) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }
        }
        else {
            $user = get_user_by( 'email', $id_or_email );	
        }

        if ( $user && is_object( $user ) ) {
            $profile_image_id = get_user_meta( $user->ID, 'profile_image_attachment_id', true );
            if ( empty( $profile_image_id ) ) {
                $avatar = CONTR_URL_PATH . '/assets/img/default-profile-pic.jpg'; 
            }
            else {
                $avatar = wp_get_attachment_url( $profile_image_id  );
            }
            $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }

        return $avatar;
    }


    
    public function load_css() {
        wp_enqueue_style( 'contributer_login', $this->plugin_url.'/assets/css/main.css', false, '1.0' );
    }


    public function load_js() {

        if ( is_user_logged_in() ) {
            wp_enqueue_script( 'contributer_main', $this->plugin_url.'/assets/js/main.js', array( 'jquery', 'jquery-form' ), '1.0', true );
            wp_localize_script( 'contributer_main', 'contributer_object', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
            ));
        }
        else {
            wp_enqueue_script( 'contributer_login', $this->plugin_url.'/assets/js/login.js', array( 'jquery' ), '1.0', true );
            wp_localize_script( 'contributer_login', 'contributer_object', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'redirect_login_url' => SenseiOptions::get_instance()->get_option( 'redirect_login_url' ),
                'facebook_app_id' => SenseiOptions::get_instance()->get_option( 'facebook_app_id' ),
                'google_app_id' => SenseiOptions::get_instance()->get_option( 'google_app_id' )
            ));
        }
    }


	public function redirect() {
            if ( ! is_singular() ) {
                return;
            }

            // if user is registered and logged in, and if user wants to visit page where login form resides, 
            //in that case we are going to redirect user to the homepage
            global $post;
            if ( ! empty( $post->post_content ) ) {
                    $regex = get_shortcode_regex();
                    preg_match_all( '/'.$regex.'/', $post->post_content, $matches );
                    if ( 
                            ! empty( $matches[2] ) && 
                            in_array( 'contributer_login', $matches[2] ) && 
                            is_user_logged_in() 
                    ){
                            wp_redirect( home_url() );
                    }
            }
	}
	

    public function define_page_options() {
        return array(
            'page' => array(
                'page_title' => 'Contributer Panel',
                'menu_title' => 'Contributer Panel',
                'capability' => 'manage_options',
                'menu_slug' => 'contributer',
                'icon_url' => false,
            ),
            'tabs' => array(
                //tab general
                array(
                    'title' => 'General',
                    'id' => 'login',
                    'icon' => '',
                    'options' => array(
                        array(
                            'name' => 'Redirect after login',
                            'id' => 'redirect_login_url',
                            'desc'  => 'Redirect url is place where user will be transfered after loggin is successfull. Homepage is default.',
                            'type'  => 'text',
                            'value'   => home_url(),
                        ),
                    )
                ),
                //tab registration
                array(
                    'title' => 'Socials',
                    'id' => 'socials',
                    'icon' => '',
                    'options' => array(
                        array(
                            'name' => 'Facebok APP id',
                            'id' => 'facebook_app_id',
                            'desc'  => 'Please insert your facebook app id if you want to use facebook login.',
                            'type'  => 'text',
                            'value'   => ''
                        ),
                        array(
                            'name' => 'Facebok APP secret',
                            'id' => 'facebook_app_secret',
                            'desc'  => 'Please insert your facebook app secret if you want to use facebook login.',
                            'type'  => 'text',
                            'value'   => ''
                        ),
                        array(
                            'name' => 'Google APP id',
                            'id' => 'google_app_id',
                            'desc'  => 'Please insert your google app id if you want to use google+ login.',
                            'type'  => 'text',
                            'value'   => ''
                        ),
                        array(
                            'name' => 'Google APP secret',
                            'id' => 'google_app_secret',
                            'desc'  => 'Please insert your google app secret if you want to use google+ login.',
                            'type'  => 'text',
                            'value'   => ''
                        ),
                    )
                ),
            )
        );
    }


	private function register_user_custom_fields() {
		$fields = array( 
			array(
				'title' => 'Social Links',
				'fields' => array(
					array(
						'label' => 'Facebook',
						'id' => 'facebook',
						'type' => 'text',
						'desc' => 'Please enter your facebook link.'
					),
					array(
						'label' => 'Twitter',
						'id' => 'twitter',
						'type' => 'text',
						'desc' => 'Please enter your twitter link.'
					),
					array(
						'label' => 'Flickr',
						'id' => 'flickr',
						'type' => 'text',
						'desc' => 'Please enter your flickr link.'
					),
				),
			), 
		);
		new UserCustomFields( $fields );
	}
	
	
	public static function anyone_can_register() {
		return true;
	}
        
        
        public function temp_google_login() {
            
            $google_client_id = SenseiOptions::get_instance()->get_option( 'google_app_id' );
            $google_client_secret = SenseiOptions::get_instance()->get_option( 'google_app_secret' );
            $google_redirect_url = SenseiOptions::get_instance()->get_option( 'redirect_login_url' );

            //include google api files
            require_once $this->plugin_directory . '/framework/classes/google/autoload.php';
            require_once $this->plugin_directory . '/framework/classes/google/Service/Oauth2.php';

            $gClient = new Google_Client();
            $gClient->setApplicationName( 'Login to ' . home_url() );
            $gClient->setClientId( $google_client_id );
            $gClient->setClientSecret( $google_client_secret );
            $gClient->setRedirectUri( $google_redirect_url );
            $gClient->setScopes(array(
                'https://www.googleapis.com/auth/plus.login',
                'profile',
                'email',
                'openid',
            ));

            $google_oauthV2 = new Google_Service_OAuth2( $gClient );

            if ( isset( $_GET['code'] ) ) { 
                $gClient->authenticate( $_GET['code'] );
            }
            else {
                return;
            }


            if ( $gClient->getAccessToken() ) {
                //For logged in user, get details from google using access token
                $user                 = $google_oauthV2->userinfo->get();
                $email                = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

                //preform registration
                if ( email_exists( $email ) ) {
                    $user_info = get_user_by( 'email', $email );
                    wp_set_current_user( $user_info->ID, $user_info->user_login );
                    wp_set_auth_cookie( $user_info->ID );
                    do_action( 'wp_login', $user_info->user_login );
                }
                else {
                    $random_password = wp_generate_password( 20 );
                    $user_id = wp_create_user( $email, $random_password, $email );

                    if ( ! is_wp_error( $user_id ) ) {
                        $wp_user_object = new WP_User( $user_id );
                        $wp_user_object->set_role('subscriber');
                        
                        $creds = array();
                        $creds['user_login'] = $email;
                        $creds['user_password'] = $random_password;
                        $creds['remember'] = false;
                        $user_id = wp_signon( $creds, false );
                    }
                }

            } 
            else {
                return;
            }
        }

}

