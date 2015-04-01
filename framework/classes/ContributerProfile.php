<?php

class ContributerProfile {
	
	public $user;
	
	
	public function __construct() {
		add_action( 'wp_ajax_update_profile', array( $this, 'update_profile' ) );
	}
	
	
	public function contributer_profile() {
		
		if ( is_user_logged_in() ) {
			$this->user = wp_get_current_user();
			return $this->render_contributer_profile();
		}
		else {
			$contributer_login_rendered = new ContributerLogin();
			return $contributer_login_rendered->render_contributer_login();
		}
		
	}
	
	
	public function render_contributer_profile() {
		
		ob_start();
		?>
		<section class="profile_container">
			<form id="profile-form">
				<input type="hidden" name="action" value="update_profile" />
				<div class="content">
					<h1>Profile Information</h1>
					<div class="img"><img src="<?php echo CONTR_URL_PATH; ?>/assets/img/default-profile-pic.jpg"><a href="#"><i class="fa fa-pencil"></i> Edit</a></div>
					<div class="group">
						<textarea id="bio"></textarea>
						<span class="bar"></span>
						<label for="bio">Bio</label>
					</div>
					<div class="group">
						<input required id="mail" name="mail" type="text" value="<?php echo $this->user->user_email; ?>">
						<span class="bar"></span>
						<label for="mail">Email</label>
					</div>
					<div class="group">
						<input required id="dn" name="dn" type="text" value="<?php echo $this->user->display_name; ?>">
						<span class="bar"></span>
						<label for="dn">Display Name</label>
					</div>
					<div class="group">
						<input id="site" name="site" type="text" value="<?php echo $this->user->user_url; ?>" >
						<span class="bar"></span>
						<label for="site">Website URL</label>
					</div>
					<div class="group">
						<input id="fb" name="fb" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $this->user->ID ) ); ?>" type="text" />
						<span class="bar"></span>
						<label for="fb"><i class="fa fa-facebook"></i> Facebook URL</label>
					</div>
					<div class="group">
						<input id="twitter" name="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $this->user->ID ) ); ?>" type="text">
						<span class="bar"></span>
						<label for="twitter"><i class="fa fa-twitter"></i> Twitter URL</label>
					</div>
					<div class="group">
						<input id="flickr" name="flickr" value="<?php echo esc_attr( get_the_author_meta( 'flickr', $this->user->ID ) ); ?>" type="text">
						<span class="bar"></span>
						<label for="flickr"><i class="fa fa-flickr"></i> Flickr URL</label>
					</div>
					<nav>
						<input type="submit" value="Save" class="save" />
					</nav>
				</div>
			</form>
		</section>
		<?php
		$html_output = ob_get_clean();
		return $html_output;
	}
	
	
	public function update_profile() {
		
		$status = true;
		$message = '';
		$email = '';
		$display_name = '';
		$website_url = '';
		$current_user = wp_get_current_user();
		
		//email check
		$email_check = $this->check_email( $current_user->user_email );
		if ( ! $email_check['status'] ) {
			$this->send_json_output( $email_check['status'], $email_check['message'] );
		}
		else {
			$email = $email_check['email'];
		}
		
		//display name check
		$displayname_check = $this->check_display_name();
		if ( ! $displayname_check['status'] ) {
			$this->send_json_output( $displayname_check['status'], $displayname_check['message'] );
		}
		else {
			$display_name = $displayname_check['dn'];
		}
		
		//website url check
		$website_url_check = $this->url_check( 'site' );
		if ( ! $website_url_check['status'] ) {
			$this->send_json_output( $website_url_check['status'], $website_url_check['message'] );
		}
		else {
			$website_url = $website_url_check['url'];
		}
		
		//saving user properties
		$update_user_properties = false;
		$args = array(
			'ID' => $current_user->ID,
		);
		
		if ( $current_user->user_email != $email ) {
			$update_user_properties = true;
			$args['user_email'] = $email;
		}
		
		if ( $current_user->display_name != $display_name ) {
			$update_user_properties = true;
			$args['display_name'] = $display_name;
		}
		
		if ( $update_user_properties ) {
			wp_update_user( $args );
		}
		
		
		//saving user metadata
		
		
		$this->send_json_output( true, 'User updated' );
		
	}
	
	
	
	private function send_json_output( $status, $message ) {
		$return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
	}
	
	
	
	private function check_email( $old_email ) {
		
		$email =  filter_input( INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL );
		
		//check is email valid
		if ( FALSE === $email ) {
			return array( 
				'status' => false, 
				'message'=> 'Invalid email. Please insert valid email and try again.' 
			);
		}
		
		//check does email already exists
		if ( $old_email != $email && email_exists( $email ) ) {
			return array( 
				'status' => false, 
				'message'=> 'Email you inserted already exists. Please choose another email and try again.' 
			);
		}
		
		return array(
			'status' => true,
			'message' => '',
			'email' => $email
		);
	}
	
	
	
	private function check_display_name() {
		
		$display_name = filter_input( INPUT_POST, 'dn' );
		if ( empty( $display_name ) ) {
			return array( 
				'status' => false, 
				'message'=> 'You did not insert display name. Please insert your display name and try again.' 
			);
		}
		
		if ( ! preg_match('/^[a-z0-9 .\-]+$/i', $display_name ) ) {
			return array( 
				'status' => false, 
				'message'=> 'Invalid display name. Please insert different display name and try again.' 
			);
		}
			
		return array(
			'status' => true,
			'message' => '',
			'dn' => $display_name
		);
		
	}
	
	
	
	private function url_check( $field_name ) {
		
		$url = filter_input( INPUT_POST, $field_name, FILTER_VALIDATE_URL );
		
		//check is url valid
		if ( FALSE === $url ) {
			return array( 
				'status' => false, 
				'message'=> 'Invalid url. Please insert valid url and try again.' 
			);
		}
		
		return array(
			'status' => true,
			'message' => '',
			'url' => $url
		);
		
	}
	
	
}
