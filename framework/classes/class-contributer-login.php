<?php

class Contributer_Login {
	
    private $plugin_dir;
    
    
    public function __construct( $plugin_dir ) {
        $this->plugin_dir = $plugin_dir;
        add_action( 'wp_ajax_nopriv_facebook_login', array( $this, 'facebook_login' ) );
        add_action( 'wp_ajax_nopriv_google_login', array( $this, 'google_login' ) );
        add_action( 'wp_ajax_nopriv_email_login', array( $this, 'email_login' ) );
        add_action( 'wp_ajax_nopriv_email_sign_up', array( $this, 'email_sign_up' ) );
    }
    
    
    public function contributer_login() {
        ob_start();
        ?>

        <div class="contributer-signup">
            <div id="login-loader" class="overlay hidden_loader">
                <div class="loader">
                      <div class="ball"></div>
                      <div class="ball"></div>
                      <div class="ball"></div>
                      <span>Please wait</span>
                </div>
            </div>
            
            <div class="login-container sign-toggle-container">
                <div id="face-button" class="contributer-connect contributer-facebook-login-button">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="facebook-logo" x="0px" y="0px" viewBox="0 0 113.62199 218.79501">
                        <path id="f" d="m 73.750992,218.795 v -99.803 h 33.498998 l 5.016,-38.895 H 73.750992 V 55.265 c 0,-11.261 3.127,-18.935 19.275,-18.935 L 113.62199,36.321 V 1.533 C 110.05999,1.059 97.833992,0 83.609992,0 c -29.695,0 -50.025,18.126 -50.025,51.413 V 80.097 H -8.1786701e-6 v 38.895 H 33.584992 v 99.803 h 40.166 z" style="fill:#fff;" />
                    </svg>
                    Login with Facebook
                </div>

                
                <?php
                    //WE ALL KNOW THAT THIS IS BAD IDEA
                    ########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
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
                    $authUrl = $gClient->createAuthUrl();
                ?>
                <a href="<?php echo $authUrl; ?>" class="contributer-connect contributer-google-login-button g-signin"
                    data-scope="https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email"
                    data-requestvisibleactions="http://schemas.google.com/AddActivity"
                    data-clientId="<?php echo SenseiOptions::get_instance()->get_option( 'google_app_id' ); ?>"
                    data-accesstype="offline"
                    data-callback="google_plus_callback"
                    data-theme="dark"
                    data-cookiepolicy="single_host_origin">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="google-icon" x="0px" y="0px" viewBox="0 0 82.578992 84.937998">
                        <g transform="translate(-26.927004,-23.354)">
                            <path d="m 70.479,71.845 -3.983,-3.093 c -1.213,-1.006 -2.872,-2.334 -2.872,-4.765 0,-2.441 1.659,-3.993 3.099,-5.43 4.64,-3.652 9.276,-7.539 9.276,-15.73 0,-8.423 -5.3,-12.854 -7.84,-14.956 h 6.849 l 7.189,-4.517 H 60.418 c -5.976,0 -14.588,1.414 -20.893,6.619 -4.752,4.1 -7.07,9.753 -7.07,14.842 0,8.639 6.633,17.396 18.346,17.396 1.106,0 2.316,-0.109 3.534,-0.222 -0.547,1.331 -1.1,2.439 -1.1,4.32 0,3.431 1.763,5.535 3.317,7.528 -4.977,0.342 -14.268,0.893 -21.117,5.103 -6.523,3.879 -8.508,9.525 -8.508,13.51 0,8.202 7.731,15.842 23.762,15.842 19.01,0 29.074,-10.519 29.074,-20.932 10e-4,-7.651 -4.419,-11.417 -9.284,-15.515 z M 56,59.107 c -9.51,0 -13.818,-12.294 -13.818,-19.712 0,-2.888 0.547,-5.87 2.428,-8.199 1.773,-2.218 4.861,-3.657 7.744,-3.657 9.168,0 13.923,12.404 13.923,20.382 0,1.996 -0.22,5.533 -2.762,8.09 -1.778,1.774 -4.753,3.096 -7.515,3.096 z m 0.109,44.543 c -11.826,0 -19.452,-5.657 -19.452,-13.523 0,-7.864 7.071,-10.524 9.504,-11.405 4.64,-1.561 10.611,-1.779 11.607,-1.779 1.105,0 1.658,0 2.538,0.111 8.407,5.983 12.056,8.965 12.056,14.629 0,6.859 -5.639,11.967 -16.253,11.967 z" style="fill:#fff;" />
                            <path d="m 98.393,58.938 0,-11.075 -5.47,0 0,11.075 -11.057,0 0,5.531 11.057,0 0,11.143 5.47,0 0,-11.143 11.113,0 0,-5.531 z" style="fill:#fff;" />
                        </g>
                    </svg>
                    Login with Google
                </a>

                <form id="email-sign-in">
                    <input type="hidden" name="action" value="email_login" />
                    <p>
                      <label for="username">Username</label>
                      <input id="username" name="username" required="required" type="text"/>
                    </p>

                    <p>
                      <label for="password">Password</label>
                      <input id="password" name="password" required="required" type="text"/>
                    </p>

                    <input type="submit" value="Sign In"/>
                </form>

                <p>Don't have an account yet? <a href="#signup" class="signlink">Sign Up.</a></p>
            </div>
            <div class="signup-container sign-toggle-container">
                <form id="email-sign-up" >
                    <input type="hidden" name="action" value="email_sign_up" />
                    <p>
                        <label for="email">E-Mail</label>
                        <input id="email" name="email" required="required" type="text"/>
                    </p>

                    <p>
                        <label for="username">Username</label>
                        <input id="username" name="username" required="required" type="text"/>
                    </p>

                    <p>
                        <label for="password">Password</label>
                        <input id="password" name="password" required="required" type="text"/>
                    </p>

                    <p>
                        <label for="password2">Password again</label>
                        <input id="password2" name="password2" required="required" type="text"/>
                    </p>

                    <input type="submit" value="Sign Up"/>
                </form>

                <p>Already have an account? <a href="#signin" class="signlink">Sign In.</a></p>
            </div>
        </div>
        <!-- contributer-signup end -->
        
        <?php
        $html_output = ob_get_clean();
        return $html_output;
    }
    
    
    public function facebook_login() {
        
        require $this->plugin_dir . '/framework/classes/facebook/facebook.php';
        
        //initialize facebook sdk
        $facebook = new Facebook(array(
            'appId' => SenseiOptions::get_instance()->get_option( 'facebook_app_id' ),
            'secret' => SenseiOptions::get_instance()->get_option( 'facebook_app_secret' )
        ));
        $fbuser = $facebook->getUser();
        
        if ( $fbuser ) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $me = $facebook->api('/me'); //user
            }
            catch ( FacebookApiException $e ) {
                //echo error_log($e);
                $fbuser = null;
            }
        }
        
        if ( ! $fbuser ){
            $this->send_json_output( false, 'We were not able to retrieve facebook user. Please try again.' );
        }
        
        //user details
        $email = $me['email'];
        
        if ( email_exists( $email ) ) {
            $user_info = get_user_by( 'email', $email );
            wp_set_current_user( $user_info->ID, $user_info->user_login );
            wp_set_auth_cookie( $user_info->ID );
            do_action( 'wp_login', $user_info->user_login );
        }
        else {
            $random_password = wp_generate_password( 20 );
            $user_id = wp_create_user( $email, $random_password, $email );

            if ( ! is_wp_error( $user_id ) ) {
                $wp_user_object = new WP_User( $user_id );
                $wp_user_object->set_role('subscriber');
                
                $creds['user_login'] = $email;
                $creds['user_password'] = $random_password;
                $creds['remember'] = false;
                $user = wp_signon( $creds, false );
                
                if ( is_wp_error( $user ) ) {
                    $this->send_json_output( false,  $user->get_error_message() );
                }
            }
            else {
                $this->send_json_output( false, 'Registration failed. Please try again.' );
            }
        }
        
        //if we are here, we are in
        $return_array = array(
            'status' => true,
            'message' => ''
        );

        wp_send_json( $return_array );
    }
    
    
    
    public function google_login() {
        
        require_once $this->plugin_dir . '/framework/classes/google/autoload.php';
        require_once $this->plugin_dir . '/framework/classes/google/Service/Oauth2.php';
        
        $google_client_id = SenseiOptions::get_instance()->get_option( 'google_app_id' );
        $google_client_secret = SenseiOptions::get_instance()->get_option( 'google_app_secret' );
        $google_redirect_url = SenseiOptions::get_instance()->get_option( 'redirect_login_url' );
        
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

        if ( isset( $_GET['code'] ) ) { 
            $gClient->authenticate( $_GET['code'] );
        }
        else {
            $this->send_json_output( false, 'Something went wrong. Please try again later.' );
        }


        if ( $gClient->getAccessToken() ) {
            //For logged in user, get details from google using access token
            $user = $google_oauthV2->userinfo->get();
            $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

            //preform registration
            if ( email_exists( $email ) ) {
                $user_info = get_user_by( 'email', $email );
                wp_set_current_user( $user_info->ID, $user_info->user_login );
                wp_set_auth_cookie( $user_info->ID );
                do_action( 'wp_login', $user_info->user_login );
            }
            else {
                $random_password = wp_generate_password( 20 );
                $user_id = wp_create_user( $email, $random_password, $email );

                if ( ! is_wp_error( $user_id ) ) {
                    $wp_user_object = new WP_User( $user_id );
                    $wp_user_object->set_role('subscriber');

                    $creds['user_login'] = $email;
                    $creds['user_password'] = $random_password;
                    $creds['remember'] = false;
                    $user = wp_signon( $creds, false );

                    if ( is_wp_error( $user ) ) {
                        $this->send_json_output( false,  $user->get_error_message() );
                    }
                }
                else {
                    $this->send_json_output( false, 'Registration failed. Please try again.' );
                }
            }  
        } 
        else {
            return;
        }
        
        $this->send_json_output( true, '' );
    }
    
    
    
    public function email_login() {
        
        $status = false;
        $remember_me = false;
        $message = '';

        if ( isset( $_POST['username'] ) ) {
            $username = $_POST['username'];
        }
        if ( isset($_POST['password'] ) ) {
            $password = $_POST['password'];
        }
        
        $creds = array();
        $creds['user_login'] = $username;
        $creds['user_password'] = $password;
        $creds['remember'] = $remember_me;
        $user_id = wp_signon( $creds, false );
        if( is_wp_error( $user_id ) ) {
            $status = false;
            $message = "Invalid username or password. Please try again.";
        }
        else {
            $status = true;
        }
        $this->send_json_output( $status,  $message );
        
    }
    
    
    
    public function email_sign_up() {
        
        $message = '';
        $status = true;
        $username = '';
        $email = '';
        $password = '';
        $password2 = '';

        //email checks
        if ( isset( $_POST['email'] ) && ! empty( $_POST['email'] ) ) {
            $email = $_POST['email'];
        }
        else {
            $this->send_json_output( false, 'Email field is empty. Please insert email and try again' );
        }
        
        $email =  filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );

        if ( FALSE === $email ) {
            $this->send_json_output( false, 'Invalid email. Please insert valid email and try again.' );
        }
        
        if ( email_exists( $email ) ) {
            $this->send_json_output( false, 'Email you inserted already exists. Please insert another email and try again.' );
        }
        
        //username checks
        if ( isset( $_POST['username'] ) && ! empty( $_POST['username'] ) ) {
            $username = $_POST['username'];
        }
        else {
            $this->send_json_output( false, 'Username field is empty. Please insert username and try again' );
        }

        if ( ! validate_username( $username ) ) {
            $this->send_json_output( false, 'Invalid username. Please try again.' );
        }
        
        if ( username_exists( $username ) ) {
            $this->send_json_output( false, 'Username you inserted already exists. Please insert another username and try again.' );
        }
        
        //password checks
        if ( isset( $_POST['password'] ) && ! empty( $_POST['password'] ) && strlen( $_POST['password'] ) > 3 ) {
            $password = $_POST['password'];
        }
        else {
            $this->send_json_output( false, 'Your password needs to contain at least 4 characters' );
        }
        
        $password2 = $_POST['password'];
        
        if ( $password != $password2 ) {
            $this->send_json_output( false, 'The password and confirmation password do not match.' );
        }
        
        //register user
    	$user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
        );
        $user_id = wp_create_user( $username, $password, $email );
    	if ( is_wp_error( $user_id ) ) {
           $this->send_json_output( false, 'Something wrong happened. Please try again later.' ); 
        } 
        else {
            $wp_user_object = new WP_User( $user_id );
            $wp_user_object->set_role('subscriber');
            $creds = array();
            $creds['user_login'] = $username;
            $creds['user_password'] = $password;
            $creds['remember'] = false;
            $user = wp_signon( $creds, false );
        }
                
        $this->send_json_output( true, '' );
    }
    
    
    private function send_json_output( $status, $message ) {
        $return_array = array(
            'status' => $status,
            'message' => $message,
        );

        wp_send_json( $return_array );
    }
	
}
