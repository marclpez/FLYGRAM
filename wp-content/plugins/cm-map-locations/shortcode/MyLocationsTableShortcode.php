<?php

namespace com\cminds\maplocations\shortcode;

use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Route;
use com\cminds\maplocations\controller\DashboardController;

class MyLocationsTableShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'my-locations-table';
	
	
	static function shortcode($atts = array()) {
		
		$atts = shortcode_atts(array(
			'controls' => 1,
			'addbtn' => 1,
		), $atts);
		
		DashboardController::embedAssets();
		
		$query = new \WP_Query(array(
			'author' => get_current_user_id(),
			'post_type' => Route::POST_TYPE,
			'posts_per_page' => 9999,
			'post_status' => array('publish', 'draft', 'pending'),
		));
		$routes = array_filter(array_map(array(App::namespaced('model\Route'), 'getInstance'), $query->posts));
		$out = DashboardController::loadFrontendView('index', compact('routes', 'atts'));
		
		return '<div class="cmloc-my-locations-shortcode">'. $out .'</div>';
		
	}
	
	
}
