<?php

namespace com\cminds\maplocations\controller;
use com\cminds\maplocations\model;

class RouteProximityOrderController extends Controller {
	
	const NONCE_REGISTER_LOCATION = 'cmloc_geolocation';
	
	static $filters = array(
		'posts_join' => array('args' => 2, 'priority' => PHP_INT_MAX),
		'posts_orderby' => array('args' => 2, 'priority' => PHP_INT_MAX),
// 		'template_include',
	);
	static $ajax = array(
		'cmloc_register_user_geolocation',
	);
	
	
	static function template_include($template) {
		global $wp_query;
		var_dump($wp_query->request);exit;
		return $template;
	}
	
	
	static function posts_join($join, \WP_Query $query) {
		if (static::isEnabled($query)) {
			global $wpdb;
			$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta AS cmloc_route_lat
				ON cmloc_route_lat.post_id = ID AND cmloc_route_lat.meta_key = %s", model\Route::META_LATITUDE);
			$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta AS cmloc_route_long
				ON cmloc_route_long.post_id = ID AND cmloc_route_long.meta_key = %s", model\Route::META_LONGITUDE);
			$join .= PHP_EOL;
// 			var_dump($join);exit;
		}
		return $join;
	}
	
	
	static function posts_orderby($orderby, \WP_Query $query) {
		if (static::isEnabled($query)) {
			
			$loc = model\User::getLastGeolocation();
			$order = model\Settings::getIndexOrder();
			
			$orderby = '(
			          acos(sin(cmloc_route_lat.meta_value * 0.0175) * sin('. $loc['lat'] .' * 0.0175)
			               + cos(cmloc_route_lat.meta_value * 0.0175) * cos('. $loc['lat'] .' * 0.0175) *
			                 cos(('. $loc['long'] .' * 0.0175) - (cmloc_route_long.meta_value * 0.0175))
			              ) * 3959
			      ) ' . $order;
// 				var_dump($orderby);exit;
			
			
		}
		
		return $orderby;
		
	}
	
	
	static function isEnabled(\WP_Query $query) {
		$loc = model\User::getLastGeolocation();
		return (!is_admin() AND FrontendController::isRoutePostType($query)
				AND model\Settings::ORDERBY_PROXIMITY == model\Settings::getIndexOrderBy()
				AND !is_null($loc['lat']) AND !is_null($loc['long']));
	}
	
	
	static function cmloc_register_user_geolocation() {
		$nonce = filter_input(INPUT_POST, 'nonce');
		$lat = filter_input(INPUT_POST, 'lat');
		$long = filter_input(INPUT_POST, 'long');
		if (wp_verify_nonce($nonce, static::NONCE_REGISTER_LOCATION) AND !is_null($lat) AND !is_null($long)) {
			
			model\User::registerLastGeolocation($lat, $long);
// 			var_dump($_SESSION);
			echo 'ok';
			exit;
			
		} else {
			die('invalid input');
		}
	}
	
}