<?php

class ContributerContribute {
	
	
	public function contributer_contribute() {

		if ( is_user_logged_in() ) {
			return $this->render_contributer_contribute();
		}
		else {
			$contributer_login_rendered = new ContributerLogin();
			return $contributer_login_rendered->render_contributer_login();
		}
		
	}
	
	
	public function render_contributer_contribute() {
		
		ob_start();
		?>
		<section class="newpost_container">
			<div class="content">
				<h1>New Post</h1>
				<p class="desc">Fill out all the fields and publish away</p>
				<div class="group">
					<input required id="title" type="text">
					<span class="bar"></span>
					<label for="title">Title</label>
				</div>

				<div class="group">
					<textarea required id="des"></textarea>
					<span class="bar"></span>
					<label for="des">Description</label>
				</div>

				<div class="group">
					<input required id="tags" type="text">
					<span class="bar"></span>
					<label for="tags">Tags</label>
				</div>

				<div class="group">
					<select>
						<option value="" disabled selected>Choose your Category</option>
						<option value="1">Category 1</option>
						<option value="2">Category 2</option>
						<option value="3">Category 3</option>
					</select>
				</div>

				<div class="upload">
					<span class="label">Images</span>
					<i class="fa fa-image fa-4x"></i>
					<p>Drag Images</p>
					<p class="button"><a href="#">Find Files</a></p>
				</div>
				<nav>
					<a href="#">Save Draft</a>
					<a href="#">Peview</a>
					<a href="#" class="publish">Publish</a>
				</nav>
			</div>
		</section>
		<?php
		$html_output = ob_get_clean();
		return $html_output;
	
	}

	
}
