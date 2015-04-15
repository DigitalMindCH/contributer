<?php

class Contributer_Profile {
	
    public $user;
    private $plugin_dir;


    public function __construct( $plugin_dir  ) {
        $this->plugin_dir = $plugin_dir;
        add_action( 'wp_ajax_update_profile', array( $this, 'update_profile' ) );
        add_action( 'wp_ajax_update_profile_image', array( $this, 'ajax_update_profile_image' ) );
    }


    public function contributer_profile() {

        $this->google_plus_sign_in();
        
        if ( is_user_logged_in() ) {
            $this->user = wp_get_current_user();
            return $this->render_contributer_profile();
        }
        else {
            $contributer_login_rendered = new Contributer_Login( $this->plugin_dir );
            return $contributer_login_rendered->contributer_login();
        }

    }
	
	
    public function render_contributer_profile() {

        $profile_image_id = get_user_meta( $this->user->ID, 'profile_image_attachment_id', true );
        if ( empty( $profile_image_id ) ) {
            $profile_image_url = CONTR_URL_PATH . '/assets/img/default-profile-pic.jpg'; 
        }
        else {
            $profile_image_url = wp_get_attachment_url($profile_image_id  );
        }
        
        ob_start();
        
        ?>

        <div id="profile-loader" class="overlay hidden_loader">
            <div class="loader">
                  <div class="ball"></div>
                  <div class="ball"></div>
                  <div class="ball"></div>
                  <span>Updating profile</span>
            </div>
        </div>

        <p id="contributer-failure" class="message-handler contributer-failure"></p>
        <p id="contributer-success" class="message-handler contributer-success"></p>
        <p id="contributer-notification" class="message-handler contributer-notification"></p>

        <p class="contributer-profile-picture">
            <h2 class="contributer-title contributer-image-title">Profile Picture</h2>  
            <form id="file_form" action="" method="POST">
                <input type="hidden" name="action" value="update_profile_image">
                <?php wp_nonce_field( 'update-user'.$this->user->ID ); ?>
                <div class="profile-image-container">
                    <img id="profile-image" src="<?php echo $profile_image_url; ?>" />
                    <input type="file" id="profile-image-upload" name="profile-image-upload" class="hidden-upload" >
                </div>
            </form>
            <p class="notice">Make sure to upload a square image for best-looking results.</p>
        </p>
        <!-- profile pic end-->

        <h2 class="contributer-title contributer-form-title">Profile Information</h2>
        <form id="profile-form" class="contributer-profile-container" method="POST" action="">

            <input type="hidden" name="action" value="update_profile" />
            <?php wp_nonce_field( 'update-user'.$this->user->ID ); ?>

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
              <input id="dn" required="required" name="dn" type="text" value="<?php echo $this->user->display_name; ?>" />
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
        $fb_url = '';
        $twitter_url = '';
        $flickr_url = '';
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
        
        //website check
        $site_url_check = $this->url_check( 'site' );
        if ( ! $site_url_check['status'] ) {
            $this->send_json_output( $site_url_check['status'], $site_url_check['message'] );
        }
        else {
            $website_url = $site_url_check['url'];
        }
        
        //fb url check
        $fb_url_check = $this->url_check( 'facebook' );
        if ( ! $fb_url_check['status'] ) {
            $this->send_json_output( $fb_url_check['status'], $fb_url_check['message'] );
        }
        else {
            $fb_url = $fb_url_check['url'];
        }
        
        //twitter url check
        $twitter_url_check = $this->url_check( 'twitter' );
        if ( ! $twitter_url_check['status'] ) {
            $this->send_json_output( $twitter_url_check['status'], $twitter_url_check['message'] );
        }
        else {
            $twitter_url = $twitter_url_check['url'];
        }
        
        //twitter url check
        $flickr_url_check = $this->url_check( 'flickr' );
        if ( ! $flickr_url_check['status'] ) {
            $this->send_json_output( $flickr_url_check['status'], $flickr_url_check['message'] );
        }
        else {
            $flickr_url = $flickr_url_check['url'];
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
            $args['description'] = wp_strip_all_tags( $_POST['bio'] );
        }
        
        if ( $website_url != $current_user->user_url ) {
            $update_user_properties = true;
            $args['user_url'] = $_POST['site'];
        }

        if ( $update_user_properties ) {
            wp_update_user( $args );
        }


        //saving user metadata
        update_user_meta( $current_user->ID, 'facebook', $fb_url ); 
        update_user_meta( $current_user->ID, 'twitter', $twitter_url ); 
        update_user_meta( $current_user->ID, 'flickr', $flickr_url ); 

        $this->send_json_output( true, 'User updated' );
    }
    
    
    /**
     * Upadting profile image using ajax
     * 
     * @uses object $wpdb
     * @uses wp_upload_dir()
     * @uses is_writeable()
     * @uses send_json_output()
     * @uses wp_handle_upload()
     * @uses wp_get_image_editor()
     * @uses is_wp_error()
     * @uses wp_get_current_user()
     * @uses update_user_meta()
     * 
     * @return type
     */
    public function ajax_update_profile_image() {

        $status = true;
        $message = '';
        $image_url = '';
        $upload_dir = wp_upload_dir();
        global $wpdb;
        
        //checking ajax
        if ( ! ( is_array( $_POST ) && is_array( $_FILES ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
            return;
        }

        //loading wp_handle_upload if not
        if ( ! function_exists( 'wp_handle_upload' ) ){
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        
        
        if ( ! is_writeable( $upload_dir['path'] ) ) {
            $this->send_json_output( false, 'Upload directory is not writeable. Please update your permissions and try again.' );
        }
        
        foreach( $_FILES as $file ) {
                 
            if ( empty( $file['type'] ) || ! preg_match('/(jpe?g|gif|png)$/i', $file['type'] ) ) {
                $this->send_json_output( false, 'Invalid image format. Jpg, gif and png are allowed.' );
            }
            
            $file_info = wp_handle_upload( $file, array('test_form' => false) );

            if ( $file_info && ! isset( $file_info['error'] ) ) {
                $status = true;
                
                //resizing image
                $image = wp_get_image_editor( $file_info['file'] );
                if ( ! is_wp_error( $image ) ) {
                    $image->resize( 150, 150, true );
                    $image->save( $file_info['file'] );
                }
                
                $attachment = array(
                    'guid'           => $file_info['url'],
                    'post_mime_type' => $file_info['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_info['file'] ) ),
                    'post_content'   => ""
                );
                
                if ( isset( $attachment['ID'] ) ) {
                  unset( $attachment['ID'] );
                }

                $attach_id = wp_insert_attachment( $attachment,  $file_info['file'] );
                
                if( ! is_wp_error( $attach_id ) ) {

                    $attach_metadata = wp_generate_attachment_metadata( $attach_id, $file_info['file'] );
                    wp_update_attachment_metadata( $attach_id, $attach_metadata );
                    $current_user = wp_get_current_user();
                    update_user_meta( $current_user->ID, 'profile_image_attachment_id', $attach_id );
                } 
                else {
                    $status = false;
                    $message = 'Uploading service is currently unavailable. Please try again later.';
                }
            }
            else {
                $status = false;
                $message = $file_info['error'];
            }
        }

        $return_array = array(
            'status' => $status,
            'message' => $message,
            'image_url' => $file_info['url']
        );

        wp_send_json( $return_array );
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

        $url = '';
        
        $message_parameters = array(
            'site' => 'Website URL',
            'facebook' => 'Facebook URL',
            'twitter' => 'Twitter URL',
            'flickr' => 'Flickr URL'
        );
        
        if ( ! empty( $_POST[ $field_name ] ) ) {
            
            $url = filter_input( INPUT_POST, $field_name, FILTER_VALIDATE_URL );

            //check is url valid
            if ( FALSE === $url ) {
                return array( 
                    'status' => false, 
                    'message'=> 'Invalid ' . $message_parameters[$field_name] . '. Please insert valid url and try again.' 
                );
            }
        }
        
        return array(
                'status' => true,
                'message' => '',
                'url' => $url
        );

    }
    
    
    public function google_plus_sign_in() {
        
        $google_client_id = SenseiOptions::get_instance()->get_option( 'google_app_id' );
        $google_client_secret = SenseiOptions::get_instance()->get_option( 'google_app_secret' );
        $google_redirect_url = SenseiOptions::get_instance()->get_option( 'redirect_login_url' );
        
        //include google api files
        require_once $this->plugin_dir . '/framework/classes/google/autoload.php';
        require_once $this->plugin_dir . '/framework/classes/google/Service/Oauth2.php';
        
        $gClient = new Google_Client();
        $gClient->setApplicationName( 'Login to ' . home_url() );
        $gClient->setClientId( $google_client_id );
        $gClient->setClientSecret( $google_client_secret );
        $gClient->setRedirectUri( $google_redirect_url );
        $gClient->setScopes(array(
            'https://www.googleapis.com/auth/plus.login',
            'profile',
            'email',
            'openid',
        ));

        $google_oauthV2 = new Google_Service_OAuth2( $gClient );

        if ( isset( $_GET['code'] ) && ! empty( $_GET['code'] ) ) { 
            
        }
        else {
            return;
        }


        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) { 
                google_plus_login('<?php echo $_GET['code']; ?>');
            });
        </script>
        <?php
    }
	
	
}
