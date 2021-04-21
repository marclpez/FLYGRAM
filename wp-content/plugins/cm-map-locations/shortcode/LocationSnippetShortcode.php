<?php

namespace com\cminds\maplocations\shortcode;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\model\Settings;

use com\cminds\maplocations\controller\FrontendController;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Route;

class LocationSnippetShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmloc-snippet';
	
	const FEATURED_IMAGE = 'image';
// 	const FEATURED_MAP = 'map';
	const FEATURED_ICON = 'icon';
	const FEATURED_NONE = 'none';
	
	
	static function shortcode($atts) {
		
		$atts = shortcode_atts(array(
			'id' => null,
			'route' => null,
			'featured' => Settings::getOption(Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE),
		), $atts);
		
		if (!empty($atts['id'])) {
			$route = Route::getInstance($atts['id']);
		}
		else if (!empty($atts['route'])) {
			$route = $atts['route'];
		}
		
		$displayParams = Settings::getOption(Settings::OPTION_INDEX_ROUTE_PARAMS);
		
		if (!empty($route) AND $route instanceof Route AND $route->canView()) {
			FrontendController::enqueueStyle();
			return sprintf('<div class="cmloc-shortcode-snippet" %s>%s</div>',
				RouteView::getDisplayParams($displayParams),
				RouteController::loadFrontendView('snippet', compact('route', 'atts'))
			);
		}
		
	}
	
	
}
