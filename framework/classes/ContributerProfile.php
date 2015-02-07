<?php

class ContributerProfile {
	
	
	public function render_contributer_profile() {
		ob_start();
		?>
		<section class="profile_container">
			<form>
				<div class="content">
					<h1>Profile Information</h1>
					<div class="img"><img src="img/profile-pic.jpg"><a href="#"><i class="fa fa-pencil"></i> Edit</a></div>
					<div class="group">
						<textarea id="bio"></textarea>
						<span class="bar"></span>
						<label for="bio">Bio</label>
					</div>
					<div class="group">
						<input required id="mail" type="text">
						<span class="bar"></span>
						<label for="mail">Email</label>
					</div>
					<div class="group">
						<input required id="dn" type="text">
						<span class="bar"></span>
						<label for="dn">Display Name</label>
					</div>
					<div class="group">
						<input id="site" type="text">
						<span class="bar"></span>
						<label for="site">Website URL</label>
					</div>
					<div class="group">
						<input id="fb" type="text">
						<span class="bar"></span>
						<label for="fb"><i class="fa fa-facebook"></i> Facebook URL</label>
					</div>
					<div class="group">
						<input id="twitter" type="text">
						<span class="bar"></span>
						<label for="twitter"><i class="fa fa-twitter"></i> Twitter URL</label>
					</div>
					<div class="group">
						<input id="flickr" type="text">
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
