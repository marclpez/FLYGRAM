<?php
/*
Plugin Name: Store Locator Plus®
Plugin URI: https://www.storelocatorplus.com/
Description: Add a location finder or directory to your site in minutes. Extensive add-on library available!
Author: Store Locator Plus®
Author URI: https://www.storelocatorplus.com
License: GPL3

Text Domain: store-locator-le

Copyright 2012 - 2021  Charleston Software Associates (info@storelocatorplus.com). All rights reserved worldwide.

Tested up to: 5.7
Version: 5.7
*/
defined( 'ABSPATH' ) || exit;
if ( defined( 'SLPLUS_VERSION' ) ) return;
defined( 'SLPLUS_VERSION'  ) || define( 'SLPLUS_VERSION'  , '5.7' );
defined( 'SLPLUS_NAME'     ) || define( 'SLPLUS_NAME'     , __( 'Store Locator Plus®', 'store-locator-le' ) );
defined( 'SLP_LOADER_FILE' ) || define( 'SLP_LOADER_FILE' , __FILE__ );

// Detect WP Heartbeat
defined( 'SLP_DETECTED_HEARTBEAT' ) || define( 'SLP_DETECTED_HEARTBEAT' , ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST[ 'action' ] ) && ( $_REQUEST[ 'action' ] === 'heartbeat' ) ) );

require_once( 'include/base/loader.php' );

if ( ! slp_passed_requirements() ) return;

slp_setup_environment();
require_once( 'include/SLPlus.php' );
