<?php

namespace com\cminds\maplocations;

use com\cminds\maplocations\core\Core;

use com\cminds\maplocations\controller\SettingsController;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\controller\AddSettingsTabsController;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\model\User;

require_once dirname(__FILE__) . '/core/Core.php';

class App extends Core {

	const VERSION = '1.9.2';
	const PREFIX = 'cmloc';
	const SLUG = 'cm-map-locations';
	const PLUGIN_NAME = 'CM Map Locations';
	const PLUGIN_WEBSITE = 'https://cminds.com/';



	static function bootstrap($pluginFile) {
		parent::bootstrap($pluginFile);
	}


	static protected function getClassToBootstrap() {
		$classToBootstrap = array_merge(
			parent::getClassToBootstrap(),
			static::getClassNames('controller'),
			static::getClassNames('model'),
			static::getClassNames('metabox')
		);
		if (static::isLicenseOk()) {
			$classToBootstrap = array_merge($classToBootstrap, static::getClassNames('shortcode'), static::getClassNames('widget'));
		}
		return $classToBootstrap;
	}


	static function init() {
		parent::init();

		Labels::initialize();

		wp_register_script('cmloc-utils', static::url('asset/js/utils.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-google-api-check', static::url('asset/js/google-maps-api-check.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-editor-images', App::url('asset/js/editor-images.js'), array('jquery', 'thickbox'), App::VERSION, true);
		wp_register_script('cmloc-google-jsapi', 'https://www.google.com/jsapi', null, static::VERSION, false);
		wp_register_script('cmloc-google-marker-clusterer', static::url('asset/js/markerclusterer.js'), null, static::VERSION, false);
		wp_register_script('cmloc-index-geolocation', static::url('asset/js/geolocation.js'), array(), App::getVersion(), true);
		if (Settings::getOption(Settings::OPTION_DONT_EMBED_GOOGLE_MAPS_JS_API)) {
			// However embed a dummy script to keep dependencies:
			wp_register_script('cmloc-google-maps', static::url('asset/js/google-maps-dummy.js'), array('cmloc-google-jsapi'), static::VERSION, false);
		} else {
			// Embed Google Maps API with the API key:
			$key = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY);
			wp_register_script('cmloc-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. urlencode($key) .'&libraries=places,geometry', array('cmloc-google-jsapi'), static::VERSION, false);
		}

// 		if ($key = Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY)) {
// 			wp_register_script('cmloc-google-jsapi', 'https://www.google.com/jsapi', null, static::VERSION, false);
// 			wp_register_script('cmloc-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. urlencode($key) .'&libraries=places', array('cmloc-google-jsapi'), static::VERSION, false);
// 		}

		wp_register_script('cmloc-backend', static::url('asset/js/backend.js'), array('jquery', 'cmloc-google-api-check'), static::VERSION, true);
		wp_register_style('cmloc-font-awesome', static::url('asset/vendor/font-awesome-4.4.0/css/font-awesome.min.css'), null, static::VERSION);
		wp_register_style('cmloc-settings', static::url('asset/css/settings.css'), null, static::VERSION);
		wp_register_style('cmloc-backend', static::url('asset/css/backend.css'), null, static::VERSION);
		wp_register_style('cmloc-frontend', static::url('asset/css/frontend.css'), array('cmloc-font-awesome', 'dashicons'), static::VERSION);
		wp_register_style('cmloc-editor', static::url('asset/css/editor.css'), array('cmloc-frontend'), static::VERSION);
		wp_register_style('cmloc-embed', static::url('asset/css/embed.css'), array('cmloc-frontend'), static::VERSION);

		wp_register_script('cmloc-ajax-upload', static::url('asset/js/ajax-upload.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-location-gallery', static::url('asset/js/location-gallery.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-index-filter', static::url('asset/js/index-filter.js'), array('jquery', 'cmloc-utils'), static::VERSION, true);
		wp_register_script('cmloc-map-marker', static::url('asset/js/map-marker.js'), array('cmloc-google-maps'), static::VERSION, true);
		wp_register_script('cmloc-map-tooltip', static::url('asset/js/map-tooltip.js'), array('cmloc-google-maps'), static::VERSION, true);
		wp_register_script('cmloc-map-markerwithlabel', static::url('asset/js/markerwithlabel.js'), array('cmloc-google-maps'), static::VERSION, true);
		wp_register_script('cmloc-map-abstract', static::url('asset/js/map-abstract.js'), array('jquery', 'cmloc-google-maps', 'cmloc-map-marker', 'cmloc-map-markerwithlabel', 'cmloc-location-gallery'), static::VERSION, true);
		wp_register_script('cmloc-index-map', static::url('asset/js/index-map.js'), array('cmloc-map-abstract', 'cmloc-google-marker-clusterer', 'cmloc-map-tooltip'), static::VERSION, true);
		wp_register_script('cmloc-location-map', static::url('asset/js/location-map.js'), array('cmloc-map-abstract'), static::VERSION, true);
		wp_register_script('cmloc-location-rating', static::url('asset/js/location-rating.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-editor', static::url('asset/js/editor.js'), array('cmloc-map-abstract', 'cmloc-editor-images', 'cmloc-ajax-upload'), static::VERSION, true);
		wp_register_script('cmloc-map-shortcode', static::url('asset/js/map-shortcode.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-business-map-shortcode', static::url('asset/js/business-map-shortcode.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-common-map-filter', static::url('asset/js/common-map-filter.js'), array('jquery'), static::VERSION, true);
		wp_register_script('cmloc-frontend', static::url('asset/js/frontend.js'), array('jquery', 'cmloc-utils'), static::VERSION, true);

		$userPosition = User::getLastGeolocation();
		wp_localize_script('cmloc-map-abstract', 'CMLOC_Map_Settings', apply_filters('cmloc_map_settings', array(
			'lengthUnits' => Settings::getOption(Settings::OPTION_UNIT_LENGTH),
			'feetToMeter' => Settings::FEET_TO_METER,
			'temperatureUnits' => Settings::getOption(Settings::OPTION_UNIT_TEMPERATURE),
			'feetInMile' => Settings::FEET_IN_MILE,
			'openweathermapAppKey' => Settings::getOption(Settings::OPTION_OPENWEATHERMAP_API_KEY),
			'googleMapAppKey' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY),
			'mapType' => Settings::getOption(Settings::OPTION_MAP_TYPE_DEFAULT),
			'zipFilterCountry' => Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY),
			'defaultZoom' => Settings::getOption(Settings::OPTION_MAP_DEFAULT_ZOOM),
			'scrollZoom' => Settings::getOption(Settings::OPTION_MAP_WHEEL_SCROLL_ZOOM),
			'indexMapMarkerClustering' => Settings::getOption(Settings::OPTION_INDEX_MAP_MARKER_CLUSTERING_ENABLE) ? 1 : 0,
			'mapShowGooglePlaces' =>  Settings::getOption(Settings::OPTION_MAP_SHOW_PLACES) ? 1 : 0,
			'showGeolocationErrors' => 0,
			'geolocationNonce' => wp_create_nonce('cmloc_geolocation'),
			'userLastPositionLat' => (is_null($userPosition[0]) ? '' : $userPosition[0]),
			'userLastPositionLong' => (is_null($userPosition[1]) ? '' : $userPosition[1]),
			'proximityOrderReloadMsg' => Labels::getLocalized('proximity_order_reload'),
			'ajaxurl' => admin_url('admin-ajax.php'),
		)));

		wp_localize_script('cmloc-index-map', 'CMLOC_Index_Map_Settings', array(
			'markerClickAction' => Settings::getIndexMapMarkerClick(),
			'itemClickAction' => Settings::getIndexListItemClick(),
// 			'showLabels' => intval(Settings::getOption(Settings::OPTION_INDEX_MAP_MARKER_LABEL_SHOW)),
			'indexLabelType' => Settings::getOption(Settings::OPTION_INDEX_MAP_LABEL_TYPE),
			'zipFilterGeolocation' => intval(Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_GEOLOCATION)),
			'zipFilterCountry' => Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY),
		));

		wp_localize_script('cmloc-location-map', 'CMLOC_Location_Map_Settings', array(
			'showLabels' => intval(Settings::getOption(Settings::OPTION_ROUTE_MAP_MARKER_LABEL_SHOW)),
		));

		wp_localize_script('cmloc-map-shortcode', 'CMLOC_Map_Shortcode_Settings', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
		));

	}


	static function admin_menu() {
		parent::admin_menu();
		$name = static::getPluginName(true);
		$page = add_menu_page($name, $name, 'manage_options', static::PREFIX, @create_function('$q', 'return;'), 'dashicons-location-alt', 5678);
	}


	static function getLicenseAdditionalNames() {
		return array(static::getPluginName(false), static::getPluginName(true));
	}


	static function activatePlugin() {
		parent::activatePlugin();
		if (App::isPro()) {
			call_user_func(array(App::namespaced('controller\BusinessController'), 'scheduleEvent'));
			SettingsController::fixPathesInSettings();
		}
	}


	static function deactivatePlugin() {
		parent::deactivatePlugin();
		if (App::isPro()) {
			call_user_func(array(App::namespaced('controller\BusinessController'), 'removeScheduledEvent'));
		}
	}


}

