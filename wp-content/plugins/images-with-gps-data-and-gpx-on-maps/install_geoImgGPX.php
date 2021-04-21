<?php
function geoimggpx_install(){
	if ( ! defined('WP_INSTALL_PLUGIN')  )
	return;

		$options = get_option('geoimggpx_options');
		$options = array();
		if(!isset($options["googleAPIKey"])){
			$options['googleAPIKey']   					= "";
		}
		if (!isset($options['geoImgGPX_ExifTool_path'])){
			$options['geoImgGPX_ExifTool_path'] = "";
		}
		if (!isset($options['geoImgGPX_GPSCache'])){
			$options['geoImgGPX_GPSCache'] = array();
		}
		update_option('geoimggpx_options', $options);
}
?>
