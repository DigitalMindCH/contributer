<?php
/*
Plugin Name: Contributer
Description: Dynamic Admin Panel
Author: Mersed Kahrimanovic
Version: 1.0
Author URI: http://www.mersed.info
 */

//including modules
require_once( 'framework/modules/sensei-options/sensei-options.php' );

//including shortcode renderers
require_once( 'framework/classes/ContributerProfile.php' );

//including other files
require_once( 'Contributer.php' );

new Contributer( __FILE__ );

