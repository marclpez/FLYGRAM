<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\Attachment;
use com\cminds\maplocations\shortcode\MapShortcode;
use com\cminds\maplocations\shortcode\LocationSnippetShortcode;
use com\cminds\maplocations\shortcode\SearchShortcode;
use com\cminds\maplocations\model\Location;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Route;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\model\Category;

class RouteController extends Controller {
	
	const PARAM_PAGE = 'page';
	const UPLOAD_ICON_NONCE = 'cmloc_upload_icon_nonce';
	const ICON_MAX_DIMENSION = 100;
	
	static $filters = array(
		'cmloc_route_index_single' => array('args' => 2),
		'posts_search' => array('args' => 2),
	);
	static $actions = array(
		array('name' => 'pre_get_posts', 'args' => 1, 'priority' => PHP_INT_MAX),
		array('name' => 'get_template_part_cmloc', 'args' => 2),
		'cmloc_route_index_pagination' => array('args' => 1, 'method' => 'displayPagination'),
		'cmloc_route_index_filter',
		'cmloc_route_single_before',
		'cmloc_route_single_map',
		'cmloc_route_single_details',
		'cmloc_route_single_locations',
		'cmloc_single_location_address' => array('args' => 1),
		'wp_enqueue_scripts',
	);
	static $ajax = array('cmloc_map_shortcode', 'cmloc_upload_icon');
	
	static $mapId = null;
	
	
	static function indexView(\WP_Query $query) {
		if (Route::canViewIndex()) {
			$routes = array_map(array(App::namespaced('model\Route'), 'getInstance'), $query->posts);
			$totalRoutesNumber = FrontendController::$query->found_posts;
// 			echo FrontendController::$query->request;exit;
			$displayParams = Settings::getOption(Settings::OPTION_INDEX_ROUTE_PARAMS);
			$layout = Settings::getOption(Settings::OPTION_INDEX_LOCATIONS_LIST_LAYOUT);
			if (empty($layout)) $layout = Settings::INDEX_LIST_BOTTOM;
			wp_enqueue_script('cmloc-index-geolocation');
			return self::loadFrontendView('index', compact('routes', 'totalRoutesNumber', 'displayParams', 'layout'));
		} else {
			return Labels::getLocalized('location_index_access_denied');
		}
	}
	
	
	static function singleView(\WP_Query $query) {
		global $id, $post, $withcomments;
		
		if (!empty($query->posts[0]) AND $route = Route::getInstance($query->posts[0])) {
			if ($route->canView()) {
				
				$post = $query->posts[0];
				$mapId = static::$mapId = 'cmloc-route-'. mt_rand();
				
				$id = $route->getId();
				$route->incrementViews();
				$withcomments = true;
				$displayParams = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PARAMS);
				
				return self::loadFrontendView('single', compact('route', 'mapId', 'displayParams'));
				
			} else {
				return Labels::getLocalized('location_access_denied');
			}
			
		} else {
			return Labels::getLocalized('location_not_found');
		}
	}
	
	
	static function wp_enqueue_scripts() {
		if (FrontendController::isRoutePostType()) {
			FrontendController::enqueueStyle();
		}
		if (FrontendController::isRouteSinglePage()) {
			self::loadSinglePageScripts();
		}
	}
	
	
	static function loadSinglePageScripts() {
		wp_enqueue_script('cmloc-location-map');
		wp_enqueue_script('cmloc-frontend');
		do_action('cmloc_load_single_page_scripts');
	}
	
	
	static function get_template_part_cmloc($slug, $name) {
		switch ($name) {
			case 'route-index-filter':
				wp_enqueue_script('cmloc-index-filter');
				self::displayIndexTop();
				do_action('cmloc_route_index_filter');
				break;
			case 'route-index-map':
				self::displayIndexMap();
				break;
			case 'route-index-list':
				echo self::getIndexList();
				break;
			case 'route-single-before':
				do_action('cmloc_route_single_before');
				break;
			case 'route-single-map':
				do_action('cmloc_route_single_map');
				break;
			case 'route-single-details':
				do_action('cmloc_route_single_details');
				break;
			case 'route-single-locations':
				do_action('cmloc_route_single_locations');
				break;
		}
	}
	
	
	static function displayIndexTop() {
		$text = trim(Settings::getOption(Settings::OPTION_INDEX_TEXT_TOP));
		echo self::loadFrontendView('index-top', compact('text'));
	}
	
	
	static function displayIndexMap() {
		$routes = Route::getIndexMapJSLocations(FrontendController::$query);
// 		if (!empty($routes)) {
			wp_enqueue_script('cmloc-index-map');
			echo self::loadFrontendView('index-map', compact('routes'));
// 		}
	}
	
	
	static function getIndexList(\WP_Query $query = null) {
		if (empty($query)) $query = FrontendController::$query;
		$routes = array_map(array(App::namespaced('model\Route'), 'getInstance'), $query->posts);
		$totalRoutesNumber = $query->found_posts;
		$displayParams = Settings::getOption(Settings::OPTION_INDEX_ROUTE_PARAMS);
		return self::loadFrontendView('index-list', compact('routes', 'totalRoutesNumber', 'displayParams', 'query'));
	}
	
	
	static function displayPagination($query = null) {
		if (empty($query)) $query = FrontendController::$query;
		$limit = Route::getPaginationLimit();
		if ($query->found_posts > $limit) {
			$total_pages = $query->max_num_pages;
			$page = $query->get('paged');
			if (empty($page)) $page = 1;
			$base_url = static::getPaginationBaseUrl();
			echo self::loadView('frontend/common/pagination', compact('total_pages', 'page', 'base_url'));
		}
	}
	
	
	static function getPaginationBaseUrl() {
		return preg_replace('~/page/[0-9]+/~', '/', $_SERVER['REQUEST_URI']);
	}
	
	
	static function cmloc_route_index_filter() {
		if ($category = FrontendController::getCategory()) {
			$searchFormUrl = $category->getPermalink();
		} else {
			$searchFormUrl = FrontendController::getUrl();
		}
		if (App::isPro()) {
			echo SearchShortcode::shortcode(array(
				'searchformurl' => $searchFormUrl,
				'searchstring' => filter_input(INPUT_GET, 's'),
			));
// 			echo self::loadFrontendView('index-filter', compact('searchFormUrl'));
		}
	}
	
	
	static function cmloc_route_single_before() {
		$route = FrontendController::getRoute();
		echo self::loadFrontendView('single-before', compact('route'));
	}
	
	static function cmloc_route_single_map() {
		$route = FrontendController::getRoute();
		$mapId = static::$mapId;
		$atts = array();
		static::displaySingleMap($route, $atts, $mapId);
// 		$mapId = static::$mapId;
// 		$route = FrontendController::getRoute();
// 		$mapCanvas = static::getMapCanvas($route, $mapId);
// 		echo self::loadFrontendView('single-map', compact('route', 'mapId', 'mapCanvas'));
	}
	
	
	static function displaySingleMap(Route $route, $atts = array(), $mapId = null) {
		$mapCanvas = static::getMapCanvas($route, $mapId);
		echo self::loadFrontendView('single-map', compact('route', 'mapId', 'mapCanvas'));
	}
	
	
	static function getMapCanvas($route, $mapId) {
		return static::loadFrontendView('single-map-canvas', compact('route', 'mapId'));
	}
	
	static function cmloc_route_single_details() {
		$route = FrontendController::getRoute();
		echo self::loadFrontendView('single-details', compact('route'));
	}
	
	static function cmloc_route_single_locations() {
		$route = FrontendController::getRoute();
		echo self::loadFrontendView('single-locations', compact('route'));
	}
	
	
	static function cmloc_route_index_single($output, $route) {
		return self::loadFrontendView('index-single', compact('route'));
	}
	
	
	static function getDashboardUrl($action = 'index', $params = array()) {
		return FrontendController::getUrl(FrontendController::URL_DASHBOARD . '/' . $action, $params);
	}
	
	
	static function pre_get_posts(\WP_Query $query) {
		if (is_admin()) return;
		if ($query->is_main_query() AND FrontendController::isRoutePostType($query)) {
			if (!$query->is_single()) {
				$query->set('posts_per_page', Route::getPaginationLimit());
				Route::registerQueryOrder($query);
			}
			if (!FrontendController::isDashboard($query)) {
				$query->set('post_status', 'publish');
			}
		}
		if ($query->is_main_query() AND $categorySlug = $query->get(Category::TAXONOMY)) {
			$query->set('post_type', Route::POST_TYPE);
		}
	}
	
	
	static function posts_search($search, \WP_Query $query) {
		global $wpdb;
		$str = $query->get('s');
		if($str == '') { $str = (isset($_GET['s']))?$_GET['s']:''; }
		if (strlen($str) > 0 AND $query->is_main_query() AND FrontendController::isRoutePostType($query)) {
			// Additional search by address and postal code
			$str = '%' . $str .'%';
			$sql = $wpdb->prepare(') OR (cmloc_addr.meta_value LIKE %s OR cmloc_postcode.meta_value LIKE %s) OR (', $str, $str);
			$search = str_replace(') OR (', $sql, $search);
			// Add required joins
			add_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10, 2);
		}
		return $search;
	}
	
	
	static function posts_search_join($join, \WP_Query $query) {
		global $wpdb;
		// Additional joins to search by address and postal code
		$join .= PHP_EOL . "JOIN $wpdb->posts cmloc_location ON cmloc_location.post_parent = $wpdb->posts.ID";
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmloc_addr ON cmloc_addr.post_id = cmloc_location.ID AND cmloc_addr.meta_key = %s", Location::META_ADDRESS);
		$join .= PHP_EOL . $wpdb->prepare("JOIN $wpdb->postmeta cmloc_postcode ON cmloc_postcode.post_id = cmloc_location.ID AND cmloc_postcode.meta_key = %s", Location::META_POSTAL_CODE);
		$join .= PHP_EOL;
		remove_filter('posts_join', array(__CLASS__, 'posts_search_join'), 10);
		return $join;
	}
	
	
	static function getInfoWindowView(Route $route) {
		$snippet = LocationSnippetShortcode::shortcode(array('route' => $route));
		
		if ($maxlen = Settings::getTooltipDescriptionCharsNumber()) {
			$description = $route->getShortDescription($maxlen);
		} else {
			$description = null;
		}
		
		return self::loadFrontendView('infowindow', compact('snippet', 'description', 'route'));
		
	}
	
	
	static function cmloc_map_shortcode() {
		if (!empty($_POST['params'])) {
			$params = $_POST['params'];
			echo MapShortcode::shortcode($params);
		}
	}
	
	
	static function cmloc_upload_icon() {
		$response = array('success' => false, 'msg' => 'An error occurred.');
		if (!empty($_FILES['cmloc-upload-icon']) AND !empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::UPLOAD_ICON_NONCE)) {
			try {
				$targetDirectory = Attachment::getUploadDir(Attachment::UPLOAD_DIR_LOCATION_ICONS);
				if (!Attachment::validateExtension($_FILES['cmloc-upload-icon']['name'], Attachment::$imageExtensions)) {
					throw new \Exception('File must be an image.');
				}
				$filePath = Attachment::upload($_FILES['cmloc-upload-icon'], $targetDirectory);
				Attachment::imageResizeCrop($filePath, self::ICON_MAX_DIMENSION, self::ICON_MAX_DIMENSION);
				$url = Attachment::getUrlByPath($filePath);
				$response = array('success' => true, 'msg' => 'OK', 'iconUrl' => $url);
			} catch (\Exception $e) {
				$response['error'] = $e->getMessage();
			}
		}
		header('content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
	
	static function cmloc_single_location_address(Location $location) {
		echo static::loadFrontendView('location-address', compact('location'));
	}
	
	
}
