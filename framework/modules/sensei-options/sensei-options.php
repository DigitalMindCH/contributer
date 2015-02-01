<?php
/*
 * Using sensei options is really easy.
 * You just need to require this file inside your functions.php file or inside plugin files
 *
 * After files are included, you just need to call
 * new SenseiAdminPanel( $url, $args )
 * $url - this url represents an url location of SenseiAdminPanel. So url, where sensei-options.php file resides
 *        using this url we will be able to load css and js properly
 * $args - this is an array, which will have:
 *     $args['page'] - reprensets admin page parameters
 *     $args['tabs'] - tabs for that page
 *     $args['tabs']['options'] - options which will rside within specific tab
 *
 * There is no need to provide any kind of additional explanation, except to provide you demo of $args.
 * Enjoy!
 *
 *
 *
 */


require_once( 'SenseiOptions.php' );
require_once( 'SenseiOptionsRenderer.php' );
require_once( 'SenseiAdminPanel.php' );