<?php
/*
Plugin Name: Images with GPS-Data and GPX on Maps
Plugin URI: https://wordpress.org/plugins/images-with-gps-data-and-gpx-on-maps/
Description: Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags.
Author: Severin Roth
Author URI: https://profiles.wordpress.org/severinroth
Text Domain: gm
Domain Path: /languages
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Version: 0.911
Tested up to: 5.6
*/


		if ( ! defined( 'ABSPATH' ) ) {
			exit;
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(!is_plugin_active( 'images-with-gps-data-and-gpx-on-maps/geoImgGPX_init.php' )) return;

//define('WP_DEBUG', true);   // Debug = true => development Mode

		define( "GMAPIKEY_VERSION", "1.1.0" );
		define('GEOIMAGEGPXPATH',    	plugin_dir_path(__FILE__));
		define('GEOIMAGEGPXURL',     	plugins_url('', __FILE__));
		include_once(GEOIMAGEGPXPATH . '/admin_geoImageAndGPX.php');
		include_once(GEOIMAGEGPXPATH . '/install_geoImgGPX.php');
		include_once(GEOIMAGEGPXPATH . '/uninstall_geoImgGPX.php');
		include_once(GEOIMAGEGPXPATH . '/geoImgGPXPagePostAdmin.php');
		include_once(GEOIMAGEGPXPATH . '/geoImgGPXMainPHP.php');
		include_once(GEOIMAGEGPXPATH . '/geoImgGPX_ShowShortCode.php');
		include_once(GEOIMAGEGPXPATH . '/geoImgGPXMainAjaxRequest.php');

		if(is_admin()) {
			add_action('admin_menu', 'geoimggpx_menu', 90);
		}
		function geoimggpx_menu() {
			// For Option Pages, see WordPress function: add_options_page()
			// For own Menu Pages, see WordPress function: add_menu_page() and add_submenu_page()
			add_options_page('Geo Routen Recodring', 'Image GPS & GPX in Map', 'manage_options', 'admin_geoImageAndGPX.php', 'geoImgGPXAdminPage');
		}


		/**
		 * DB Table erzeugen und löschen ----------------------------------------------------------------------------------------------
		 *
		*/

		register_activation_hook( 	__FILE__ , 'geoimggpx_install' );
//		register_deactivation_hook( __FILE__ , '');
		register_uninstall_hook( 	__FILE__ , 'geoimggpx_uninstall');
		/**
		 * Backend Form erzeugen zur Administration der GeoDaten ----------------------------------------------------------------------
		 *
		*/


		/**
		 * Adds a box to the main column on the Post and Page edit screens.
		 */
		function geoImgGPX_add_meta_box() {

			$screens = array( 'post', 'page' );
		//	$screens = array( 'post' );

			foreach ( $screens as $screen ) {
				add_meta_box(
					'geoImgGPX_sectionid',
					__( 'Images with GPS Data on Maps', 'gm' ),
					'geoImgGPX_meta_box_callback',
					$screen
				);
			}
		}
		add_action( 'add_meta_boxes', 'geoImgGPX_add_meta_box' );

		add_filter( 'clean_url', 'geoImgGPX_find_add_key', 99, 3 );
		function geoImgGPX_find_add_key( $url, $original_url, $_context ) {
			$geoImgGPXoptions 			= get_option('geoimggpx_options');
			if(!isset($geoImgGPXoptions["googleAPIKey"])){
				define('WP_INSTALL_PLUGIN', true);
				geoimggpx_install();
			}
			$key = $geoImgGPXoptions["googleAPIKey"];

			/* If no key added no point in checking	*/
			if ( ! $key ) {
				return $url;
			}

			if ( strstr( $url, "maps.google.com/maps/api/js" ) !== false || strstr( $url, "maps.googleapis.com/maps/api/js" ) !== false ) { /* it's a Google maps url */

				if ( strstr( $url, "key=" ) === false ) {	/* it needs a key	*/
					$url = add_query_arg( 'key', $key, $url );
					$url = str_replace( "&#038;", "&amp;", $url ); /* or $url = $original_url	*/
				}

			}

			return $url;
		}

		//Google Maps JavaScript einlesen
		function geoImgGPX_Widget_scripts() {
//			wp_enqueue_script( 'jQuery JS', 		GEOIMAGEGPXURL.'/js/jquery-3.1.1.js', 									array() );
//			wp_enqueue_script('jquery', false, array(), false, false);
				if ( ! wp_script_is( 'jquery-core', 'done' ) ) {
					wp_enqueue_script( 'jquery-core' );
				}
			//https://digwp.com/2011/09/using-instead-of-jquery-in-wordpress/

			$geoImgGPXoptions 			= get_option('geoimggpx_options');

//		 	wp_enqueue_script( 'Google Maps API', 	'https://maps.googleapis.com/maps/api/js?key='.$geoImgGPXoptions["googleAPIKey"].'&language=true', 	    array() );
			if ( !wp_script_is( 'google_js', 'done' ) ) {
				wp_register_script('google_js', 'https://maps.googleapis.com/maps/api/js?key='.$geoImgGPXoptions["googleAPIKey"].'&language=true', 	    array() );
				wp_enqueue_script( 'google_js' );
			}

			wp_enqueue_script( 'GeoImgGPX_JS', 		GEOIMAGEGPXURL.'/geoImgGPXMainJS.js', 									array() );
			wp_enqueue_style( 'geoImgGPX_CSS', 		GEOIMAGEGPXURL.'/css/style.css',												array(), 	1.0, 'screen' );
//			wp_enqueue_script( 'jQuery JS', 		GEOIMAGEGPXURL.'/js/jquery.min.js', 									array() );


		}
		add_action( 'wp_enqueue_scripts', 	'geoImgGPX_Widget_scripts' );
		add_action( 'admin_enqueue_scripts', 'geoImgGPX_Widget_scripts' );

		function gm_RegisterPluginLinks($links, $plugin_file) {
			if( strpos( $plugin_file, "images-with-gps-data-and-gpx-on-maps" ) === 0) {		/* images-with-gps-data-and-gpx-on-maps/geoImgGPX_init.php */
				$links[] = '<a href="../wp-admin/options-general.php?page=admin_geoImageAndGPX.php">'.__('Einstellungen', 'gm').'</a>';
				$links[] = '<a target="_blank" href="https://wordpress.org/plugins/images-with-gps-data-and-gpx-on-maps/">' . __('FAQ', 'gm') . '</a>';
				$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/images-with-gps-data-and-gpx-on-maps/">' . __('Support', 'gm') . '</a>';
			}
			return $links;
		}
		add_filter('plugin_row_meta', 'gm_RegisterPluginLinks', 10, 2);

		function gm_my_add_action_links( $links_array, $plugin_file ) {
			if( strpos( $plugin_file, "images-with-gps-data-and-gpx-on-maps" ) === 0) {		/* images-with-gps-data-and-gpx-on-maps/geoImgGPX_init.php */
				array_unshift( $links_array, '<a href="../wp-admin/options-general.php?page=admin_geoImageAndGPX.php">'.__('Einstellungen', 'gm').'</a>' );
			}
			return $links_array;
		}
		add_filter( 'plugin_action_links', 'gm_my_add_action_links', 10, 5 );

		function gm_plugin_loaded() {
			/* _e() __e() _x() _ex() usw das mo-File Laden Language File Internationalisierung */
			load_plugin_textdomain('gm', false, basename(dirname(__FILE__)).'/languages/');
		}
		add_action('plugins_loaded', 'gm_plugin_loaded');

		/* add ShortCode to Gutenberg Block */
		function loadGeoImgGPX_Block() {
		  wp_enqueue_script(
		    'geoImg_shortCodeBlock',
		    plugin_dir_url(__FILE__) . 'geoImgGPX_block.js',
		    array('wp-blocks','wp-editor'),
		    true
		  );
		}

		add_action('enqueue_block_editor_assets', 'loadGeoImgGPX_Block');
?>
