<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\model\Route;

class EmbedController extends Controller {
	
	static $actions = array(
// 		'plugins_loaded' => array('priority' => 100),
		'cmloc_route_single_toolbar_middle' => array('args' => 1),
		'cmloc_route_toolbar_after' => array('args' => 1),
// 		'wp_head',
	);
	static $filters = array(
		'cmloc_map_settings',
	);
	
	const PARAM_EMBED = 'embed';
	
	
	static function bootstrap() {
		parent::bootstrap();
		// We need to check settings before adding hooks
		add_action('plugins_loaded', array(get_called_class(), 'plugins_loaded'), 300);
	}
	

	static function addHooks() {
		// wait
	}
	
	static function plugins_loaded() {
		if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_EMBED_ENABLE)) {
			parent::addHooks();
			if (static::isEmbed()) {
				add_filter('template_include', array(get_called_class(), 'template_include'), PHP_INT_MAX-5);
				add_filter('the_content', array(get_called_class(), 'the_content'), PHP_INT_MAX-5);
				add_filter('body_class', array(get_called_class(), 'body_class'), PHP_INT_MAX-5);
				add_action('wp_head', array(get_called_class(), 'wp_head'));
			}
		}
	}
	
	
	static function isEmbed() {
		return (filter_input(INPUT_GET, static::PARAM_EMBED) == 1);
	}
	
	static function template_include($template) {
		$template = App::path('view/frontend/embed/blank-template.php');
		return $template;
	}
	
	static function wp_head() {
		wp_enqueue_style('cmloc-embed');
	}
	
	static function the_content($content) {
		if (FrontendController::isRouteSinglePage()) {
			$route = FrontendController::getRoute();
			$id = $route->getId();
			$mapId = mt_rand();
			$mapCanvas = RouteController::getMapCanvas($route, $mapId);
			$content = static::loadFrontendView('embed-single', compact('route', 'id', 'mapCanvas', 'mapId'));
		}
		return $content;
	}
	
	
	static function body_class($class) {
		$class[] = 'cmloc-embed';
		return $class;
	}
	
	
	static function cmloc_route_toolbar_after(Route $route) {
		$url = add_query_arg(static::PARAM_EMBED, 1, $route->getPermalink());
		$iframe = static::loadFrontendView('iframe-template', compact('route', 'url'));
		echo static::loadFrontendView('route-embed', compact('route', 'iframe'));
	}
	
	
	static function cmloc_route_single_toolbar_middle(Route $route) {
		$url = add_query_arg(static::PARAM_EMBED, 1, $route->getPermalink());
		$url = '#';
		printf('<li class="cmloc-embed-btn"><a href="%s" title="%s"><span class="dashicons dashicons-share-alt2"></span>%s</a></li>',
			esc_attr($url),
			esc_attr(Labels::getLocalized('location_embed_iframe')),
			Labels::getLocalized('location_embed_btn')
		);
	}
	
	
	static function cmloc_map_settings($settings) {
		
		if (static::isEmbed()) {
// 			$settings['routeMapLabelType'] = Settings::LABEL_TYPE_SHOW_BELOW;
			$settings['routeMapLocationsInfoWindow'] = 0;
			$settings['allowInfoWindowAutoOpen'] = 0;
		}
		
		return $settings;
	}
		
}
