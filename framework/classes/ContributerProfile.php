<?php

class ContributerProfile {
	
	public $user;
	
	
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
				<div class="content">
					<h1>Profile Information</h1>
					<div class="img"><img src="<?php echo CONTR_URL_PATH; ?>/assets/img/default-profile-pic.jpg"><a href="#"><i class="fa fa-pencil"></i> Edit</a></div>
					<div class="group">
						<textarea id="bio"></textarea>
						<span class="bar"></span>
						<label for="bio">Bio</label>
					</div>
					<div class="group">
						<input required id="mail" type="text" value="<?php echo $this->user->user_email; ?>">
						<span class="bar"></span>
						<label for="mail">Email</label>
					</div>
					<div class="group">
						<input required id="dn" type="text" value="<?php echo $this->user->display_name; ?>">
						<span class="bar"></span>
						<label for="dn">Display Name</label>
					</div>
					<div class="group">
						<input id="site" type="text" value="<?php echo $this->user->user_url; ?>" >
						<span class="bar"></span>
						<label for="site">Website URL</label>
					</div>
					<div class="group">
						<input id="fb" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $this->user->ID ) ); ?>" type="text" />
						<span class="bar"></span>
						<label for="fb"><i class="fa fa-facebook"></i> Facebook URL</label>
					</div>
					<div class="group">
						<input id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $this->user->ID ) ); ?>" type="text">
						<span class="bar"></span>
						<label for="twitter"><i class="fa fa-twitter"></i> Twitter URL</label>
					</div>
					<div class="group">
						<input id="flickr" value="<?php echo esc_attr( get_the_author_meta( 'flickr', $this->user->ID ) ); ?>" type="text">
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
	
	
}
