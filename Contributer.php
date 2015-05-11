<?php

class Contributer {

    private $plugin_directory;

    private $plugin_url;


    public function __construct( $file ) {

        $this->plugin_directory = plugin_dir_path( $file );
        $this->plugin_directory_rel = dirname( plugin_basename( $file ) );
        $this->plugin_url = plugin_dir_url( $file );
        
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        //enque js scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'load_js' ) );

        //enqeue css styles
        add_action( 'wp_enqueue_scripts', array( $this, 'load_css' ) );

        //add filter for custom avatars
        add_filter( 'get_avatar' , array( $this, 'contributer_avatar' ) , 1 , 5 );

        $login_renderer = new Contributer_Login( $this->plugin_directory );
        add_shortcode( 'contributer_login', array( $login_renderer, 'contributer_login' ) );

        $profile_renderer = new Contributer_Profile();
        add_shortcode( 'contributer_profile', array( $profile_renderer, 'contributer_profile' ) );

        $contribute_renderer = new Contributer_Contribute();
        add_shortcode( 'contributer_contribute', array( $contribute_renderer, 'contributer_contribute' ) );

        new Sensei_Admin_Panel( $this->plugin_url.'/framework/modules/sensei-options', $this->define_page_options( $this->plugin_directory ) );
        Sensei_Options::get_instance()->set_option( 'plugin_dir', $this->plugin_directory );
        $this->register_user_custom_fields();
    }

       
    
    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( CONTR_PLUGIN_SLUG, false, $this->plugin_directory_rel . '/languages/' ); 
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
                'redirect_login_url' => Sensei_Options::get_instance()->get_option( 'redirect_login_url' ),
                'facebook_app_id' => Sensei_Options::get_instance()->get_option( 'facebook_app_id' ),
                'google_app_id' => Sensei_Options::get_instance()->get_option( 'google_app_id' )
            ));
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
                        array(
                            'name' => 'Chechbox button test',
                            'id' => 'option_id_checkbox',
                            'desc'  => 'Checkbox button description (olala)',
                            'type'  => 'checkbox',
                            'value'   => false,
                        ),
                        array(
                            'name' => 'Checkbox button 2 (depends on chekbox button from above)',
                            'id' => 'option_id_checkbox2',
                            'desc'  => 'Checkbox button which depends of other checkbox button',
                            'type'  => 'checkbox',
                            'value'   => false,
                            'condition' => array(
                                'type' => 'option',
                                'value' => 'option_id_checkbox',
                            )
                        ),
                        array(
                            'name' => 'Checkbox button 3 (depends on chekbox button from above with different disabled type)',
                            'id' => 'option_id_checkbox3',
                            'desc'  => 'Checkbox button which depends of other checkbox button',
                            'type'  => 'checkbox',
                            'value'   => false,
                            'condition' => array(
                                'type' => 'option',
                                'value' => 'option_id_checkbox',
                                'disabled_type' => 'hidden'
                            )
                        ),
                        array(
                            'name' => 'Select a page',
                            'id' => 'option_id_select_page',
                            'type' => 'select_posts',
                            'desc' => 'This page will be the page where user will be redirected after he/she clicks on the link which we are going to send to their email.',
                            'post_type' => 'post',
                            'condition' => array(
                                'type' => 'option',
                                'value' => 'option_id_checkbox'
                            )
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
        new User_Custom_Fields( $fields );
    }
	
	
    public static function anyone_can_register() {
        return true;
    }

}

