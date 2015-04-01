<?php

class ContributerProfile {
	
    public $user;


    public function __construct() {
        add_action( 'wp_ajax_update_profile', array( $this, 'update_profile' ) );
        add_action( 'wp_ajax_update_profile_image', array( $this, 'update_profile_image' ) );
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

        $profile_image_url = get_user_meta( $this->user->ID, 'profile_image_url', true );
        if ( empty( $profile_image_url ) ) {
            $profile_image_url = CONTR_URL_PATH . '/assets/img/default-profile-pic.jpg'; 
        }
        
        ob_start();
        ?>


        <p class="contributer-profile-picture">
            <h2 class="contributer-title contributer-image-title">Profile Picture</h2>
            
            
            <form id="file_form">
                <input type="hidden" name="action" value="update_profile_image">
                <div class="profile-image-container">
                    <img id="profile-image" src="<?php echo $profile_image_url; ?>" />
                    <input type="file" id="profile-image-upload" name="profile-image-upload" class="hidden-upload" >
                </div>
            </form>
            
            <p class="notice">Make sure to upload a square image for best-looking results.</p>
        </p>
        <!-- profile pic end-->

        <h2 class="contributer-title contributer-form-title">Profile Information</h2>
        <form id="profile-form" class="contributer-profile-container">

            <input type="hidden" name="action" value="update_profile" />

            <p>
              <label for="bio">Bio</label>
              <textarea name="bio" id="bio"><?php echo $this->user->description; ?></textarea>
            </p>

            <p>
              <label for="mail">Email</label>
              <input id="mail" name="mail" required="required" value="<?php echo $this->user->user_email; ?>" type="text"/>
            </p>

            <p>
              <label for="dn">Display Name</label>
              <input id="dn" name="dn" type="text" value="<?php echo $this->user->display_name; ?>" />
            </p>

            <p>
              <label for="site">Website URL</label>
              <input id="site" name="site" type="text" value="<?php echo $this->user->user_url; ?>" />
            </p>

            <p>
              <label for="facebook">Facebook URL</label>
              <input id="facebook" name="facebook" type="text" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $this->user->ID ) ); ?>" />
            </p>

            <p>
              <label for="twitter">Twitter URL</label>
              <input id="twitter" name="twitter" type="text" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $this->user->ID ) ); ?>" />
            </p>

            <p>
              <label for="flickr">Flickr URL</label>
              <input id="flickr" name="flickr" type="text" value="<?php echo esc_attr( get_the_author_meta( 'flickr', $this->user->ID ) ); ?>" />
            </p>

            <p>
              <input type="submit" value="Save" />
            </p>

        </form>
        <!-- form alt end -->


        <?php
        $html_output = ob_get_clean();
        return $html_output;
    }
	
	
    public function update_profile() {

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
        
        if ( isset( $_POST['bio'] ) && $_POST['bio'] != $current_user->description ) {
            $update_user_properties = true;
            $args['description'] = $_POST['bio'];
        }
        
        if ( isset( $_POST['site'] ) && $_POST['site'] != $current_user->user_url ) {
            $update_user_properties = true;
            $args['user_url'] = $_POST['site'];
        }

        if ( $update_user_properties ) {
            wp_update_user( $args );
        }


        //saving user metadata
        update_user_meta( $current_user->ID, 'facebook', $_POST['facebook'] ); 
        update_user_meta( $current_user->ID, 'twitter', $_POST['twitter'] ); 
        update_user_meta( $current_user->ID, 'flickr', $_POST['flickr'] ); 

        $this->send_json_output( true, 'User updated' );
    }
    
    
    
    public function update_profile_image() {

        $status = true;
        $message = '';
        $image_url = '';
        
        if ( ! ( is_array( $_POST ) && is_array( $_FILES ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
            return;
        }

        if ( ! function_exists( 'wp_handle_upload' ) ){
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        add_filter( 'upload_dir', array( $this, 'profile_image_upload_dir' ) );
        
        //in this case we know that we will have only one image
        foreach( $_FILES as $file ){
                 
            $file_info = wp_handle_upload( $file, array('test_form' => false) );
            if ( $file_info && ! isset( $file_info['error'] ) ) {
                $status = true;
                
                //resigin image
                $image = wp_get_image_editor( $file_info['file'] );
                if ( ! is_wp_error( $image ) ) {
                    $image->resize( 150, 150, true );
                    $image->save( $file_info['file'] );
                }
                
                $current_user = wp_get_current_user();
                $profile_image_dir = get_user_meta( $current_user->ID, 'profile_image_dir', true );
                if ( ! empty( $profile_image_dir ) ) {
                    unlink( $profile_image_dir );
                }
                
                $image_url = $file_info['url'];
                update_user_meta( $current_user->ID, 'profile_image_url', $image_url );
                update_user_meta( $current_user->ID, 'profile_image_dir', $file_info['file'] );
            } 
            else {
                $status = false;
                $message = $file_info['error'];
            }
        }
        
         // Set everything back to normal.
         remove_filter( 'upload_dir', array( $this, 'profile_image_upload_dir' ) );

        $return_array = array(
            'status' => $status,
            'message' => $message,
            'image_url' => $image_url
        );

        wp_send_json( $return_array );
    }
    
    
    
    public function profile_image_upload_dir( $dir ) {
        return array(
            'path'   => $dir['basedir'] . '/profile-images',
            'url'    => $dir['baseurl'] . '/profile-images',
            'subdir' => '/profile-images',
        ) + $dir;
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
