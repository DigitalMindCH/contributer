<?php

class Contributer_Contribute {
	
	
    public function __construct() {
        add_action( 'wp_ajax_add_post', array( $this, 'add_post' ) );
    }
	
	
    
    public function contributer_contribute() {

        if ( is_user_logged_in() ) {
            return $this->render_contributer_contribute();
        }
        else {
            $contributer_login_rendered = new Contributer_Login();
            return $contributer_login_rendered->render_contributer_login();
        }

    }

    
	
    public function render_contributer_contribute() {

        ob_start();
        ?>

        <p id="contributer-failure" class="message-handler contributer-failure"></p>
        <p id="contributer-success" class="message-handler contributer-success"></p>
        <p id="contributer-notification" class="message-handler contributer-notification"></p>

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
                <div id="featured-image-upload-area" class="contributer-upload"> 
                    <div id="featured-image-upload-holder">
                        <div id="featured-image-uploaded"></div>
                        <div id="featured-image-upload-different">Click to select different image</div>
                    </div>
                    <p id="featured-image-upload-here">drag 'n' drop <br/>
                        <input type="file" id="featured-image" name="featured-image" class="files" />
                    </p>
                </div>
            </div>
			
            <!-- gallery images -->
            <div id="gallery-field" class="field">
                <span>gallery images</span>
                <div id="gallery-images-upload-area" class="contributer-upload">
                    <div id="gallery-images-upload-holder">
                        <div id="gallery-images-uploaded"></div>
                        <div id="gallery-images-upload-different">Click to select different images</div>
                    </div>
                    <div id="gallery-images-upload-here"> 
                        <p>drag 'n' drop <br/>
                            <input type="file" id="gallery-images" name="gallery-images" class="files" multiple />
                        </p>
                    </div>
                </div>
            </div>
		
            <!-- post video url -->
            <p id="video-field" class="field">
                <label for="vid-url">Video URL</label>
                <input id="vid-url" name="video_url" type="text"/>
            </p>
		
            <!-- post content -->
            <p>
                <label for="post-content">Content</label>
                <?php
                wp_editor( '', 'post-content', array(
                    'wpautop'       => true,
                    'media_buttons' => false,
                    'textarea_name' => 'content',
                    'textarea_rows' => 10,
                    'teeny'         => true
                ) );
                ?>
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

            <input type="submit" value="Save draft"/>

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
//RIGHT NOW THEY ARE PRETTY MUCH THE SAME, BUT WE ARE GOING TO SEPARATE THEM LITTLE BT LATER BECAUSE
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
            $this->post_title = wp_strip_all_tags( $_POST['title'] );
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = strip_tags( $_POST['content'], '<strong><p><div><em><a><blockquote><del><ins><img><ul><li><ol><!--more--><code>' );
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( wp_strip_all_tags ( $_POST['cat'] ) );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = explode( ",", wp_strip_all_tags( $_POST['tags'] ) );
        }
        
    }
    
    
    public function insert_post() {
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'draft',
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
            $upload_response = $this->upload_featured_image( $post_id );
            if ( $upload_response['status'] ) {
                $message = 'Post published';
            }
            else {
                $message = $upload_response['message'];
            }
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $return_array = array(
            'status' => true,
            'message' => ''
        );
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
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
            else {
                $return_array['status'] = false;
                $return_array['message'] = 'Post published with warnings <br /> Image: ' . $file['name'] . '--' . $upload['error'];
            }
        }    
        
        return $return_array;
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
            $this->post_title = wp_strip_all_tags( $_POST['title'] );
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = strip_tags( $_POST['content'], '<strong><p><div><em><a><blockquote><del><ins><img><ul><li><ol><!--more--><code>' );
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( wp_strip_all_tags ( $_POST['cat'] ) );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = explode( ",", wp_strip_all_tags( $_POST['tags'] ) );
        }
        
    }
    
    
    public function insert_post() {
        
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        
        if ( empty( $this->post_title ) ) {
            $this->send_json_output( false, 'Post title is empty. Please insert post title and try again.' );
        }
        
        if ( ! isset( $_FILES['featured-image'] ) ) {
            $this->send_json_output( false, 'Featured image is required for image posts.' );
        } 
        
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'draft',
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
            $upload_response = $this->upload_featured_image( $post_id );
            if ( $upload_response['status'] ) {
                set_post_format( $post_id, 'image' );
                $message = 'Post published';
            }
            else {
                wp_delete_post( $post_id, true );
                $status = false;
                $message = 'We were not able to upload your image. <br/ > Error: '.$upload_response['message'];
            }
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $return_array = array(
            'status' => true,
            'message' => ''
        );
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
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
            else {
                $return_array['status'] = false;
                $return_array['message'] = $upload['error'];
            }
        }
             
        return $return_array;
    }
    
    
    private function send_json_output( $status, $message ) {
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
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
            $this->post_title = wp_strip_all_tags( $_POST['title'] );
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = strip_tags( $_POST['content'], '<strong><p><div><em><a><blockquote><del><ins><img><ul><li><ol><!--more--><code>' );
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( wp_strip_all_tags ( $_POST['cat'] ) );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = explode( ",", wp_strip_all_tags( $_POST['tags'] ) );
        }
        
        if ( isset( $_POST['video_url'] ) && ! empty( $_POST['video_url'] ) ) {
            $this->video_url = wp_strip_all_tags( $_POST['video_url'] );
        }
        
    }
    
    
    public function insert_post() {
        
        if ( empty( $this->post_title ) ) {
            $this->send_json_output( false, 'Post title is empty. Please insert post title and try again.' );
        }
        
        if ( empty( $this->video_url ) ) {
            $this->send_json_output( false, 'Video url is empty. Please insert video url and try again.' );
        }
        
        if ( wp_oembed_get( $this->video_url ) === false ) {
            $this->send_json_output( false, 'Invalid video url. Please insert valid video url and try again.' );
        }
        
        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => wp_oembed_get( $this->video_url ). ' <div>' . $this->post_content .'</div>',
            'post_title' =>  $this->post_title,
            'post_status' => 'draft',
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
            $upload_response = $this->upload_featured_image( $post_id );
            set_post_format( $post_id, 'video' );
            update_post_meta( $post_id, 'video_url', $this->video_url );          
            if ( $upload_response['status'] ) {
                $message = 'Post published';
            }
            else {
                $message = $upload_response['message'];
            }
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $return_array = array(
            'status' => true,
            'message' => ''
        );
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
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
            else {
                $return_array['status'] = false;
                $return_array['message'] = 'Post published with warnings <br /> Image: ' . $file['name'] . '--' . $upload['error'];
            }

        }
          
        return $return_array;
        
    }
    
    
    
    private function send_json_output( $status, $message ) {
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
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
            $this->post_title = wp_strip_all_tags( $_POST['title'] );
        }

        if ( isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ) {
            $this->post_content = strip_tags( $_POST['content'], '<strong><p><div><em><a><blockquote><del><ins><img><ul><li><ol><!--more--><code>' );
        }
        
        if ( isset( $_POST['cat'] ) && ! empty( $_POST['cat'] ) && $_POST['cat'] != -1 ) {
            $this->post_category = array( wp_strip_all_tags ( $_POST['cat'] ) );
        }

        if ( isset( $_POST['tags'] ) && ! empty( $_POST['tags'] ) ) {
            $this->post_tags = explode( ",", wp_strip_all_tags( $_POST['tags'] ) );
        }
        
    }
    
    
    public function insert_post() {
        
        //required, post title needs to be set
        if ( empty( $this->post_title ) ) {
            $this->send_json_output( false, 'Post title is empty. Please insert post title and try again.' );
        }
        
        //required, at least one image needs to be set
        if ( ! isset( $_FILES['gallery-image-0'] ) ) {
            $this->send_json_output( false, 'You need to upload at least one image in order to publish gallery.' );
        }
        
            

        $status = true;
        $message = '';
        $current_user = wp_get_current_user();
        $arguments = array(
            'post_content' => $this->post_content,
            'post_title' =>  $this->post_title,
            'post_status' => 'draft',
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
            
            //upload feature image
            $featured_image_array = $this->upload_featured_image( $post_id );

            //uploading gallery images
            $attachments_array = $this->upload_images( $post_id );     
            if ( $attachments_array['status'] ) {
                $additional_content = implode( ',', $attachments_array['attachments'] );
                set_post_format( $post_id, 'gallery' );

                $arguments = array(
                    'ID' => $post_id,
                    'post_content' => $this->post_content . '<br />' . '[gallery ids="' . $additional_content . '"]'
                ); 
                wp_update_post( $arguments, true );
                
                if ( ! empty ( $attachments_array['warning_message'] ) ) {
                    $message = 'Post published with warnings: <br />' . $attachments_array['warning_message'].$featured_image_array['message'];
                }
                else {
                    if ( $featured_image_array['status'] ) {
                        $message = 'Post published <br />';
                    }
                    else {
                        $message = 'Post published with warnings <br />'.$featured_image_array['message'];
                    }
                }
            }
            else {
                $status = false;
                wp_delete_post( $post_id, true );
                $message = $attachments_array['error_message'];
            }
        }
        
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
        
    }
    
    
    
    private function upload_images( $post_id ) {
        
        $return_array = array(
            'error_message' => '',
            'warning_message' => '',
            'status' => true
        );
        $wp_upload_dir = wp_upload_dir();
        $attachments = array();
        $succeed_uploads = 0;
        
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
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                $attachments[] = $attach_id;
                $succeed_uploads++;
            }
            else {
                $return_array['warning_message'] = $return_array['warning_message'] . 'Image: ' . $_FILES['gallery-image-'.$i]['name'] . '--' . $upload['error'] . '<br />';
            }  
        }
        
        $return_array['attachments'] = $attachments;
        
        if ( $succeed_uploads == 0 ) {
            $return_array['error_message'] = 'Upload of images failed';
            $return_array['status'] = false;
        }
        
        return $return_array;     
    }
    
    
    
    private function upload_featured_image( $post_id ) {
        
        $return_array = array(
            'status' => true,
            'message' => ''
        );
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
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
            else {
                $return_array['status'] = false;
                $return_array['message'] = 'Featured image: ' . $file['name'] . '--' . $upload['error'] . '<br />';
            }

        }
        
        return $return_array;
                
    }
    
    
    
    private function send_json_output( $status, $message ) {
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
    }
    
}