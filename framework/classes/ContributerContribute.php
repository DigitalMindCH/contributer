<?php

class ContributerContribute {
	
	
	public function __construct() {
		add_action( 'wp_ajax_add_post', array( $this, 'add_post' ) );
	}
	
	
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

		<form id="contributer-editor" class="contributer-editor">

			<input type="hidden" name="action" value="add_post" />
			
			<p>
				<label for="title">Title</label>
				<input id="title" name="title" type="text" />
			</p>
			
			<?php
			$plugin_supported_formats = array( 'image', 'video', 'gallery' );
			
			if ( current_theme_supports( 'post-formats' ) ) {
				$post_formats = get_theme_support( 'post-formats' );

				if ( is_array( $post_formats[0] ) ) {
					?>
					<p>
						<span>Post Format</span>
						<input id="standard" type="radio" name="post-format" value="standard" checked="checked" />
						<label for="standard">Standard</label>
						<?php foreach ( $post_formats[0] as $post_format ) { ?>
							<?php if ( in_array( $post_format, $plugin_supported_formats ) ) { ?>
								<input id="<?php echo $post_format; ?>" type="radio" name="post-format" value="<?php echo $post_format; ?>"/>
								<label for="<?php echo $post_format; ?>"><?php echo ucfirst( $post_format ); ?></label>
							<?php } ?>
						<?php } ?>
					</p>
					<?php
				}
			}
			
			?>
			
			<div id="feat-img-field" class="field">
				<span>featured image</span>
				<div class="contributer-upload"> 
					<p>drag 'n' drop <br/>
						<input type="button" value="find files" class="files"/>
					</p>
				</div>
			</div>
			
			<div id="gallery-field" class="field"><span>gallery images</span>
				<div class="contributer-upload"> 
					<p>drag 'n' drop <br/>
						<input type="button" value="find files" class="files"/>
					</p>
				</div>
			</div>
			
			<p id="video-field" class="field">
				<label for="vid-url">Video URL</label>
				<input id="vid-url" type="text"/>
			</p>
			
			<p>
				<label for="content">Content</label>
				<textarea id="content" name="content" type="text"></textarea>
			</p>
			
			<p>
				<label for="tags">Tags</label>
				<input id="tags" type="text" name="tags" />
			</p>

			<p>
				<span>Category</span>
				<?php wp_dropdown_categories( array(
					'hide_empty' => 0,  
					'taxonomy' => 'category',
					'orderby' => 'name', 
					'hierarchical' => true, 
					'show_option_none' => __( 'Choose your Category' )
					)
				); ?>
			</p>

			<input type="submit" value="Save"/>

		  </form>
		  <!-- form editor end -->


		<?php
		$html_output = ob_get_clean();
		return $html_output;

    }
	
	

	public function add_post() {
		
		$status = true;
		$message = '';
		$post_title = '';
		$post_content = '';
		$current_user = wp_get_current_user();
		
		if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
			$post_title = $_POST['title'];
		}
		
		if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
			$post_content = $_POST['content'];
		}
		
		$arguments = array(
			'post_content' => $post_content,
			'post_title' =>  $post_title,
			'post_status' => 'publish',
			'post_type' => 'post',
			'post_author' => $current_user->ID,
			//'tags_input'     => [ '<tag>, <tag>, ...' | array ] // Default empty.
		); 
		
		if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
			$arguments['post_category'] = array( $_POST['cat'] );
		}
		
		if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
			$arguments['tags_input'] = array( $_POST['tags'] );
		}
		
		$post_id = wp_insert_post( $arguments, true );
		
		if ( is_wp_error( $post_id ) ) {
			$status = false;
			$message = $post_id->get_error_message();
		}
		else {
			
			if ( isset( $_POST['post-format'] ) && $_POST['post-format'] != 'standard' ) {
				set_post_format( $post_id, $_POST['post-format'] );
			}
			
			//
			$message = 'Post published';
		}
		
		
		$return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
		
	}

	
}
