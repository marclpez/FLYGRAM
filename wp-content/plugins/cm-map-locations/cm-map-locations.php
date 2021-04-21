<?php
/*
  Plugin Name: CM Map Locations
  Plugin URI: https://www.cminds.com/
  Description: Manage locations and support location finder using Google Maps.
  Author: CreativeMindsSolutions
  Version: 1.9.6
 */

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\maplocations\App::bootstrap(__FILE__);

