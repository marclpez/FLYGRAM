<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Location;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\model\Settings;

class ZipController extends Controller {
	
	
	protected static $filters = array(
		'posts_search' => array('args' => 2),
	);
	protected static $actions = array(
		'cmloc_map_filter_after',
	);
	
	
	static function cmloc_map_filter_after() {
		
		if (!Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE)) return;
		
		$radiusOptions = array();
		$unitLen = Settings::getOption(Settings::OPTION_UNIT_LENGTH);
		$start = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_MIN);
		$max = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_MAX);
		$defaultRadius = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_DEFAULT);
		$step = Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_STEP);
		for ($val = $start; $val <= $max; $val += $step) {
			if ($unitLen == Settings::UNIT_FEET) {
				$valMeters = $val * Settings::FEET_IN_MILE * Settings::FEET_TO_METER;
			} else {
				$valMeters = $val * 1000;
			}
			$valKm = round($valMeters/1000);
			$radiusOptions[$valKm] = $val . ' ' . ($unitLen == Settings::UNIT_METERS ? Labels::getLocalized('length_km') : Labels::getLocalized('length_miles'));
		}
		
		$radiusValue = filter_input(INPUT_GET, 'zipradius');
		if (empty($radiusValue)) {
			$radiusValue = ($unitLen == Settings::UNIT_METERS ? $defaultRadius : $defaultRadius * Settings::FEET_IN_MILE * Settings::FEET_TO_METER);
		}
		
		$zipcodeValue = filter_input(INPUT_GET, 'zipcode');
		
		echo self::loadFrontendView('filter', compact('radiusOptions', 'zipcodeValue', 'radiusValue'));
	}
	
	
	static function posts_search($search, \WP_Query $query) {
		if (Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_FILTER_ENABLE) AND FrontendController::isRoutePostType($query)) {
			$zipcode = filter_input(INPUT_GET, 'zipcode');
			$radiusKm = filter_input(INPUT_GET, 'zipradius');
			if ($zipcode AND $radiusKm) {
				
				$radiusMeters = $radiusKm * 1000;
				$radiusMiles = $radiusMeters / Settings::FEET_TO_METER / Settings::FEET_IN_MILE;
				
				$coords = Route::findLocationByAddress($zipcode . ' ' . Settings::getOption(Settings::OPTION_INDEX_ZIP_RADIUS_COUNTRY));
// 				var_dump($coords);exit;
				if (!empty($coords)) {
					
					$query->cmlocRadiusMeters = $radiusMeters;
					$query->cmlocZipcode = $zipcode;
					$query->cmlocCoords = $coords;
					
					$sql = '(
				          acos(sin(cmloc_ziplat.meta_value * 0.0175) * sin('. $coords[0] .' * 0.0175) 
				               + cos(cmloc_ziplat.meta_value * 0.0175) * cos('. $coords[0] .' * 0.0175) *    
				                 cos(('. $coords[1] .' * 0.0175) - (cmloc_ziplng.meta_value * 0.0175))
				              ) * 3959 <= '. $radiusMiles .'
				      )';
					
					$search .= ' AND ' . $sql;
					
					// Add required joins
					add_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10, 2);
					
				}
				
			}
		}
		return $search;
	}
	
	
	static function posts_search_join($join, \WP_Query $query) {
		global $wpdb;
		// Additional joins to search by address and postal code
		$join .= PHP_EOL . "JOIN $wpdb->posts cmloc_ziploc ON cmloc_ziploc.post_parent = $wpdb->posts.ID";
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmloc_ziplat ON cmloc_ziplat.post_id = cmloc_ziploc.ID AND cmloc_ziplat.meta_key = %s", Location::META_LAT);
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmloc_ziplng ON cmloc_ziplng.post_id = cmloc_ziploc.ID AND cmloc_ziplng.meta_key = %s", Location::META_LONG);
		$join .= PHP_EOL;
		remove_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10);
		return $join;
	}
	
	
	
}
