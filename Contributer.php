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
		
		add_shortcode( 'contributer_login', array( $this, 'render_login_form' ) );
		
		$profile_renderer = new ContributerProfile();
		add_shortcode( 'contributer_profile', array( $profile_renderer, 'contributer_profile' ) );
		
		$contribute_renderer = new ContributerContribute();
		add_shortcode( 'contributer_contribute', array( $contribute_renderer, 'contributer_contribute' ) );

		new SenseiAdminPanel( $this->plugin_url.'/framework/modules/sensei-options', $this->define_page_options() );
		$this->register_user_custom_fields();
	}


	public function load_css() {
		wp_enqueue_style( 'contributer_login', $this->plugin_url.'/assets/css/main.css', false, '1.0' );
	}


	public function load_js() {
		wp_enqueue_script( 'contributer_login', $this->plugin_url.'/assets/js/main.js', array( 'jquery' ), '1.0', true );

		$sensei_options = SenseiOptions::get_instance();
		$redirect_after_login = $sensei_options->get_option( 'redirect_login_url' );
		if ( empty( $redirect_after_login ) ) {
			$redirect_after_login = home_url();
		}

		wp_localize_script( 'contributer_login', 'contributer_object', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'redirect_login_url' => $sensei_options->get_option( 'redirect_login_url' ),
			'redirect_registration_url' => $sensei_options->get_option( 'redirect_registration_url' )
		));
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


	public function login_form() {
		ob_start();
		?>
		
		<?php
		$html_output = ob_get_clean();
		$html_output = apply_filters( 'contributer_login_form_html', $html_output );
		return $html_output;
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
				//tab login
				array(
					'title' => 'Login',
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
					'title' => 'Registration',
					'id' => 'registration',
					'icon' => '',
					'options' => array(
						array(
							'name' => 'Redirect after registration',
							'id' => 'redirect_registration_url',
							'desc'  => 'Redirect url is place where user will be transfered after registration. Homepage is default.',
							'type'  => 'text',
							'value'   => home_url(),
							'condition' => array(
								'type' => 'custom',
								'value' => array( 'Contributer', 'anyone_can_register' ),
							),
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

}

