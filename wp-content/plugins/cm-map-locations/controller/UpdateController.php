<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\Route;
use com\cminds\maplocations\model\Location;

use com\cminds\maplocations\App;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmloc_update_methods';

	static function bootstrap() {
		global $wpdb;
		
		if (defined('DOING_AJAX') && DOING_AJAX) return;
		
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)(_.+)?$/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates, $autoload = true);
		}
		
	}
	

	static function update_1_0_2() {
		global $wpdb;
		
		// Update Route's postmeta views
		$routesIds = $wpdb->get_col($wpdb->prepare("SELECT route.ID FROM $wpdb->posts route
			LEFT JOIN $wpdb->postmeta m ON m.post_id = route.ID AND m.meta_key = %s
			WHERE route.post_type = %s AND (m.meta_value IS NULL OR m.meta_value = '')",
			Route::META_VIEWS, Route::POST_TYPE));
		
		foreach ($routesIds as $id) {
			if ($route = Route::getInstance($id)) {
				$route->setViews(0);
			}
			unset($route);
			Route::clearInstances();
		}
	}
	
	
	static function update_1_8_1() {
		global $wpdb;
		
		$icons = array();
		$path = App::path('asset/google-maps-icons.php');
		if (file_exists($path)) include $path;
		
		$sql = "SELECT pm.meta_id, pm.post_id, pm.meta_value FROM $wpdb->postmeta pm
			WHERE pm.meta_key = '_cmloc_icon' AND pm.meta_value LIKE 'http://maps.google.com%'";
		$results = $wpdb->get_results($sql, ARRAY_A);
// 		$results = array(array('meta_id' => 87482374234, 'meta_value' => 'http://maps.google.com/test'));
		
		foreach ($results as $row) {
			$url = $row['meta_value'];
			$url = str_replace('http://', 'https://', $url);
			$wpdb->update($wpdb->postmeta, array('meta_value' => $url), array('meta_id' => $row['meta_id']));
		}
		
	}
	
	
	static function update_1_9_0_update_route_coords() {
		global $wpdb;
		
		$sql = $wpdb->prepare("SELECT route.ID AS id, llat.meta_value AS location_lat, llong.meta_value AS location_long
				FROM $wpdb->posts route
				JOIN $wpdb->posts rloc ON rloc.post_parent = route.ID AND rloc.post_type = %s AND rloc.menu_order = 1
				JOIN $wpdb->postmeta llat ON llat.post_id = rloc.ID AND llat.meta_key = %s
				JOIN $wpdb->postmeta llong ON llong.post_id = rloc.ID AND llong.meta_key = %s
				WHERE route.post_type = %s", Location::POST_TYPE, Location::META_LAT, Location::META_LONG, Route::POST_TYPE);
		$routes = $wpdb->get_results($sql, ARRAY_A);
// 				var_dump($routes);exit;
		foreach ($routes as $route) {
			$id = $route['id'];
			if (!is_null($route['location_lat']) AND !is_null($route['location_long'])) {
				update_post_meta($id, Route::META_LATITUDE, $route['location_lat']);
				update_post_meta($id, Route::META_LONGITUDE, $route['location_long']);
			} else {
				// 				var_dump('no coords  ' . $id);
			}
		}
		
	}
	
	
	static function update_1_9_0_update_route_rating_cache() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", Route::POST_TYPE);
		$ids = $wpdb->get_col($sql);
		foreach ($ids as $id) {
			$rating = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value)/COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
					$id,
					Route::META_RATE
				));
			$rating = intval($rating);
			update_post_meta($id, Route::META_RATING_CACHE, $rating);
		}
	}
	
	
}
