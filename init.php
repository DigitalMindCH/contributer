<?php
/*
Plugin Name: Contributer
Description: Dynamic Admin Panel
Author: Mersed Kahrimanovic
Version: 1.0
Author URI: http://www.mersed.info
 */

//defining plugin path and plugin url
define( 'CONTR_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CONTR_URL_PATH', plugin_dir_url( __FILE__ )  );

//including modules
require_once( 'framework/modules/sensei-options/sensei-options.php' );
require_once( 'framework/modules/user-custom-fields/init.php' );

//including shortcode renderers
require_once( 'framework/classes/ContributerProfile.php' );
require_once( 'framework/classes/ContributerContribute.php' );
require_once( 'framework/classes/ContributerLogin.php' );

//including other files
require_once( 'Contributer.php' );

new Contributer( __FILE__ );
