<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\RouteTag;
use com\cminds\maplocations\model\User;
use com\cminds\maplocations\model\Category;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\model\Route;

class FrontendController extends DummyPageController {
	
	const URL_DASHBOARD = 'locations';
	const QUERY_DASHBOARD_PAGE = 'cmloc_dashboard_page';
	const DASHBOARD_ADD = 'add';
	const DASHBOARD_EDIT = 'edit';
	const DASHBOARD_DELETE = 'delete';
	
	static $actions = array('init', 'admin_head');
	static $filters = array('query_vars', 'body_class');
	
	static function init() {
// 		flush_rewrite_rules(true);
		$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
		add_rewrite_rule( $slug . '/'. static::URL_DASHBOARD .'/(\w+)', add_query_arg(array(
			static::QUERY_DASHBOARD_PAGE => '$matches[1]'
		), 'index.php'), 'top' );
	}
	
	
	static function query_vars($vars) {
		$vars[] = static::QUERY_DASHBOARD_PAGE;
		return $vars;
	}
	
	
	
	static function isDummyPageRequired(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return static::isThePage($query);
	}
	
	
	static function isThePage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return (static::isRoutePostType($query) OR static::isDashboard($query));
	}
	
	
	static function getDummyPostTitle() {
		$title = Labels::getLocalized('location_index_title');
		if (static::isDashboard()) {
			switch (static::getDashboardPage()) {
				case static::DASHBOARD_ADD:
					$title = Labels::getLocalized('dashboard_add_location_title');
					break;
				case static::DASHBOARD_EDIT:
					$title = Labels::getLocalized('dashboard_edit_location_title');
// 					if ($route = self::getRoute()) {
// 						$title .= ' | ' . $route->getTitle();
// 					}
					break;
				default:
					$title = Labels::getLocalized('dashboard_my_locations_title');
			}
		}
		else if (static::$query AND static::$query->is_404()) {
			$title = Labels::getLocalized('location_not_found');
		}
		else if (static::$query AND static::$query->is_single()) {
			$title = static::$query->post->post_title;
		}
		else if ($category = static::getCategory()) {
			$title = $category->getName();
		}
		
		if ($tag = static::getTag()) {
			if (empty($category)) $title = '';
			else if (!empty($title)) $title .= ', ';
			$title .= 'Tag: ' . $tag->getName();
		}
		
		return $title;
		
	}
	
	
	static function getCategory($query = null) {
		if (empty($query)) $query = static::$query;
		if (!empty($query->query['cmloc_category']) AND $category = Category::getInstance($query->query['cmloc_category'])) {
			return $category;
		}
	}
	
	
	static function getTag($query = null) {
		if (empty($query)) $query = static::$query;
		if (!empty($query->query['tag']) AND $tag = RouteTag::getInstance($query->query['tag'])) {
			return $tag;
		}
	}
	
	
	static function the_content($content) {
		if (static::isDummyPageRequired()) {
			if (static::isDashboard()) {
				if (Route::canCreate()) {
					$method = array(App::namespaced('controller\DashboardController'), static::getDashboardPage() . 'View');
					if (method_exists($method[0], $method[1]) AND is_callable($method)) {
						return call_user_func($method, static::$query);
					} else {
						return Labels::getLocalized('dashboard_unknown_action_msg');
					}
				} else {
					return Labels::getLocalized('dashboard_access_denied_msg');
				}
			}
			else if (static::$query AND static::$query->is_404()) {
				return Labels::getLocalized('page_not_found');
			}
			else if (static::$query AND static::$query->is_single()) {
				return RouteController::singleView(static::$query);
			}
			else {
				return RouteController::indexView(static::$query);
			}
		}
		return $content;
	}
	
	
	static function isRoutePostType(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return (!empty($query) AND ($query->get('post_type') == Route::POST_TYPE OR $query->get(Category::TAXONOMY)));
	}
	
	
	static function getRoute(\WP_Query $query = null) {
		$route = null;
		if (empty($query)) $query = static::$query;
		if (self::isDashboard($query) AND isset($_GET['id'])) {
			$route = Route::getInstance($_GET['id']);
		}
		else if (self::isRoutePostType($query) AND $query->is_single() AND !empty($query->posts[0])) {
			$route = Route::getInstance($query->posts[0]);
		}
		return $route;
	}
	
	
	static function isRouteSinglePage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		return (self::isRoutePostType($query) AND $query->is_single());
	}
	
	
	static function isDashboard(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		$page = self::getDashboardPage($query);
		return (!empty($page));
	}
	
	
	static function getDashboardPage(\WP_Query $query = null) {
		if (empty($query)) $query = static::$query;
		if (empty($query)) return null;
		else return $query->get(static::QUERY_DASHBOARD_PAGE);
	}
	
	
	static function getUrl($action = '', $params = array()) {
		$slug = Settings::getOption(Settings::OPTION_PERMALINK_PREFIX);
		$url = home_url($slug . '/'. $action);
		return add_query_arg(urlencode_deep($params), trailingslashit($url));
	}
	
	
	static function wp_title($title, $sep = '', $seplocation = 'right') {
		if (static::isDummyPageRequired()) {
			$title = static::getDummyPostTitle();
			if (!FrontendController::isDashboard() AND (static::$query AND static::$query->is_single()) OR static::getCategory() OR static::getTag()) {
				$title .= ' | ' . Labels::getLocalized('single_location_title_part');
			}
			$title .= ' | ' . get_option('blogname');
		}
		return $title;
	}
	
	
	static function body_class($class) {
		global $wp_query;
	
		$isRoute = static::isRoutePostType();
		$isDashboard = static::isDashboard();
	
		if ($isRoute) {
			if (static::isRouteSinglePage()) {
				$class[] = 'cmloc-single';
			} else {
				$class[] = 'cmloc-archive';
			}
		}
	
		if ($isDashboard) {
			$class[] = 'cmloc-dashboard';
			if ($page = static::getDashboardPage()) {
				$class[] = 'cmloc-dashboard-' . $page;
			}
		}
	
		if ($isRoute OR $isDashboard) {
			// Divi theme fix:
			$class[] = 'et_right_sidebar';
		}
	
		return $class;
	}
	
	static function enqueueStyle() {
		wp_enqueue_style('cmloc-frontend');
		wp_enqueue_script('cmloc-frontend');
		add_action('wp_footer', array(__CLASS__, 'displayCustomCSS'));
	}
	
	
	static function displayCustomCSS() {
		echo '<style type="text/css">' . Settings::getOption(Settings::OPTION_CUSTOM_CSS) . '</style>';
	}
	
	static function admin_head() {
		$roles = Settings::getOption(Settings::OPTION_ACCESS_MEDIA_LIBRARY_ROLES);
		if (User::hasRole($roles)) return;
		echo '<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function() {
				if (top && top.document && top.document.body && top.document.body.className.indexOf("cmloc-dashboard") > -1) {
					document.getElementById("tab-library").style.display = "none";
				}
			});
		</script>';
	}
	
}
