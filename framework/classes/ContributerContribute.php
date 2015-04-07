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

            <input type="hidden" id="action" name="action" value="add_post" />

            <!-- post title -->
            <p>
                <label for="title">Title</label>
                <input id="title" name="title" type="text" value="" />
            </p>
		
            <!-- post formats -->
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
		
            <!-- featured image -->
            <div id="feat-img-field" class="field">
                <span>featured image</span>
                <div class="contributer-upload"> 
                    <p>drag 'n' drop <br/>
                        <input type="file" id="featured-image" name="featured-image" class="files" />
                    </p>
                </div>
            </div>
			
            <!-- gallery images -->
            <div id="gallery-field" class="field"><span>gallery images</span>
                <div class="contributer-upload"> 
                    <p>drag 'n' drop <br/>
                        <input type="file" id="gallery-images" name="gallery-images" class="files" multiple />
                    </p>
                </div>
            </div>
		
            <!-- post video url -->
            <p id="video-field" class="field">
                <label for="vid-url">Video URL</label>
                <input id="vid-url" name="video_url" type="text"/>
            </p>
		
            <!-- post content -->
            <p>
                <label for="content">Content</label>
                <textarea id="content" name="content" type="text"></textarea>
            </p>
            
            <!-- post tags -->
            <p>
                <label for="tags">Tags</label>
                <input id="tags" type="text" name="tags" />
            </p>

            <!-- post category -->
            <p>
                <span>Category</span>
                <?php wp_dropdown_categories( array(
                        'hide_empty' => 0,  
                        'taxonomy' => 'category',
                        'orderby' => 'name', 
                        'hierarchical' => true, 
                        'show_option_none' => __( 'Choose your Category' ),
                        'name' => 'cat',
                        'id' => 'cat',
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

        $post_format = 'standard';
        $allowed_formats = array( 'standard', 'video', 'image', 'gallery' );
        
        if ( isset( $_POST['post-format'] ) && in_array( $_POST['post-format'], $allowed_formats ) ) {
            $post_format = $_POST['post-format'];
        }
        
        $class_name = 'CC' . ucfirst( $post_format ) . 'Format';
        $post_creator = new $class_name();
        $post_creator->insert_post();
    }
    
}



//CLASSES BELLOW ARE JUST LIKE HELPERS
//RIGHT NOW THEY ARE PRETTY MUCH SAME, BUT WE ARE GOING TO SEPARATE IT RIGHT NOW, BECAUSE
//DIFFERENCE WILL INCREASE WITH VERSIONS
/**
 * Standard format class
 */
class CCStandardFormat {
    
    private $post_title = '';
    
    private $post_content = '';
    
    private $post_category = array();
    
    private $post_tags = array();
    
    
    public function __construct() {
        
        if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
            $this->post_title = $_POST['title'];
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = $_POST['content'];
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( $_POST['cat'] );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = array( $_POST['tags'] );
        }
        
    }
    
    
    public function insert_post() {
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => $current_user->ID,
            'tags_input' => $this->post_tags,
            'post_category' => $this->post_category
        ); 

        $post_id = wp_insert_post( $arguments, true );
        
        if ( is_wp_error( $post_id ) ) {
            $status = false;
            $message = $post_id->get_error_message();
        }
        else {
            $this->upload_featured_image( $post_id );
            $message = 'Post published';
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $wp_upload_dir = wp_upload_dir();
        
        //in this case we know that it will be only one
        foreach( $_FILES as $key => $file ) {
            
            if ( 'featured-image' != $key ) {
                continue;
            }
            
            $filename = $wp_upload_dir['path'] . '/' . basename( $post_id . '_' . $file['name'] );
            $filetype = wp_check_filetype( basename( $filename ), null );
            
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            
            wp_update_attachment_metadata( $attach_id, $attach_data );
            add_post_meta( $post_id, '_thumbnail_id', $attach_id );

        }
                
    }
    
}



/**
 * Image format class
 */
class CCImageFormat {
    
    private $post_title = '';
    
    private $post_content = '';
    
    private $post_category = array();
    
    private $post_tags = array();
    
    
    public function __construct() {
        
        if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
            $this->post_title = $_POST['title'];
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = $_POST['content'];
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( $_POST['cat'] );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = array( $_POST['tags'] );
        }
        
    }
    
    
    public function insert_post() {
        
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => $current_user->ID,
            'tags_input' => $this->post_tags,
            'post_category' => $this->post_category
        ); 

        $post_id = wp_insert_post( $arguments, true );
        
        if ( is_wp_error( $post_id ) ) {
            $status = false;
            $message = $post_id->get_error_message();
        }
        else {
            $this->upload_featured_image( $post_id );
            set_post_format( $post_id, 'image' );
            $message = 'Post published';
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $wp_upload_dir = wp_upload_dir();
        
        //in this case we know that it will be only one
        foreach( $_FILES as $key => $file ) {
            
            if ( 'featured-image' != $key ) {
                continue;
            }
            
            $filename = $wp_upload_dir['path'] . '/' . basename( $post_id . '_' . $file['name'] );
            $filetype = wp_check_filetype( basename( $filename ), null );
            
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            
            wp_update_attachment_metadata( $attach_id, $attach_data );
            add_post_meta( $post_id, '_thumbnail_id', $attach_id );

        }
                
    }
    
}



/**
 * Standard format class
 */
class CCVideoFormat {
    
    private $post_title = '';
    
    private $post_content = '';
    
    private $post_category = array();
    
    private $post_tags = array();
    
    private $video_url = '';
    
    
    public function __construct() {
        
        if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
            $this->post_title = $_POST['title'];
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = $_POST['content'];
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( $_POST['cat'] );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = array( $_POST['tags'] );
        }
        
        if ( isset( $_POST['video_url'] ) && ! empty( $_POST['video_url'] ) ) {
            $this->video_url = $_POST['video_url'];
        }
        
    }
    
    
    public function insert_post() {
        
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => $current_user->ID,
            'tags_input' => $this->post_tags,
            'post_category' => $this->post_category
        ); 

        $post_id = wp_insert_post( $arguments, true );
        
        if ( is_wp_error( $post_id ) ) {
            $status = false;
            $message = $post_id->get_error_message();
        }
        else {
            $this->upload_featured_image( $post_id );
            set_post_format( $post_id, 'video' );
            update_post_meta( $post_id, 'video_url', $this->video_url );
            $message = 'Post published';
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $wp_upload_dir = wp_upload_dir();
        
        //in this case we know that it will be only one
        foreach( $_FILES as $key => $file ) {
            
            if ( 'featured-image' != $key ) {
                continue;
            }
            
            $filename = $wp_upload_dir['path'] . '/' . basename( $post_id . '_' . $file['name'] );
            $filetype = wp_check_filetype( basename( $filename ), null );
            
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            
            wp_update_attachment_metadata( $attach_id, $attach_data );
            add_post_meta( $post_id, '_thumbnail_id', $attach_id );

        }
                
    }
    
}



/**
 * Standard format class
 */
class CCGalleryFormat {
    
    private $post_title = '';
    
    private $post_content = '';
    
    private $post_category = array();
    
    private $post_tags = array();
    
    
    public function __construct() {
        
        if ( isset( $_POST['title'] ) && ! empty( $_POST['title'] ) ) {
            $this->post_title = $_POST['title'];
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = $_POST['content'];
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( $_POST['cat'] );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = array( $_POST['tags'] );
        }
        
    }
    
    
    public function insert_post() {

        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => $current_user->ID,
            'tags_input' => $this->post_tags,
            'post_category' => $this->post_category
        ); 

        $post_id = wp_insert_post( $arguments, true );
        
        if ( is_wp_error( $post_id ) ) {
            $status = false;
            $message = $post_id->get_error_message();
        }
        else {
            $this->upload_featured_image( $post_id );
            $attachments = $this->upload_images( $post_id );
            $additional_content = implode( ',', $attachments );
            set_post_format( $post_id, 'gallery' );
            
            $arguments = array(
                'ID' => $post_id,
                'post_content' => $this->post_content . '<br />' . '[gallery ids="' . $additional_content . '"]'
            ); 
            wp_update_post( $arguments, true );
            
            $message = 'Post published';
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_images( $post_id ) {
        
        $wp_upload_dir = wp_upload_dir();
        $attachments = array();
        
        //in this case we know that it will be only one
        
        for ( $i = 0; $i <= 20; $i++ ) {
            
            if ( ! isset( $_FILES['gallery-image-'.$i] ) ) {
                continue;
            }
            
            $file = $_FILES['gallery-image-'.$i];
            
            $filename = $wp_upload_dir['path'] . '/' . basename( $post_id . '_' . $i . '_' .  $file['name'] );
            $filetype = wp_check_filetype( basename( $filename ), null );
            
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            $attachments[] = $attach_id;
            
        }
        
   
        
        return $attachments;
                
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $wp_upload_dir = wp_upload_dir();
        
        //in this case we know that it will be only one
        foreach( $_FILES as $key => $file ) {
            
            if ( 'featured-image' != $key ) {
                continue;
            }
            
            $filename = $wp_upload_dir['path'] . '/' . basename( $post_id . '_' . $file['name'] );
            $filetype = wp_check_filetype( basename( $filename ), null );
            
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
                'post_mime_type' => $filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $upload = wp_handle_upload( $file, array( 'test_form' => false ) );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            
            $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
            
            wp_update_attachment_metadata( $attach_id, $attach_data );
            add_post_meta( $post_id, '_thumbnail_id', $attach_id );

        }
                
    }
    
}