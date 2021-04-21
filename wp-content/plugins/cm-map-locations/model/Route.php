<?php

namespace com\cminds\maplocations\model;

use com\cminds\maplocations\controller\RouteController;
use com\cminds\maplocations\controller\DashboardController;
use com\cminds\maplocations\model\Category;
use com\cminds\maplocations\App;
use com\cminds\maplocations\helper\RemoteConnection;
use com\cminds\maplocations\helper\GoogleMapsIcons;

/**
 * This model represents the single location object itself.
 * 
 * However for the legacy reasons (since this plugin origins directly from the Map Route Manager)
 * there's also Location model that represents the single location marker.
 *
 */
class Route extends PostType {
	
	const POST_TYPE = 'cmloc_object';
	
	const META_RATE = '_cmloc_route_rate';
	const META_RATE_USER_ID = '_cmloc_route_rate_user_id';
	const META_RATE_TIME = '_cmloc_route_rate_time';
	const META_RATING_CACHE = 'cmloc_route_rating_cache';
	
	const META_ICON = '_cmloc_icon';
	const META_ICON_SIZE = '_cmloc_icon_size';
	const META_VIEWS = '_cmloc_views';
	const META_MODERATOR_ACCEPTED = '_cmloc_moderator_accepted';
	
	const META_LATITUDE = 'cmmrm_latitude';
	const META_LONGITUDE = 'cmmrm_longitude';
	
	// Leave this for the common shortcode:
	const META_OVERVIEW_PATH = '_cmloc_overview_path';
	const META_PATH_COLOR = '_cmloc_path_color';
	
	const ICON_SIZE_SMALL = 'small';
	const ICON_SIZE_NORMAL = 'normal';
	const ICON_SIZE_LARGE = 'large';
	
	const WAYPOINTS_LIMIT = 512;
	
	const DEFAULT_TRAVEL_MODE = 'WALKING';
	
	const TRANSIENT_GEOLOCATION_BY_ADDR_CACHE = 'cmloc_geoloc_by_addr_cache';
	
	static $travelModes = array('WALKING', 'BICYCLING', 'DRIVING', 'DIRECT');
	
	
	static protected $postTypeOptions = array(
		'label' => 'Route',
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_admin_bar' => true,
		'show_in_menu' => App::PREFIX,
		'hierarchical' => false,
		'supports' => array('title', 'editor'),
		'has_archive' => true,
		'taxonomies' => array(Category::TAXONOMY),
	);
	
	
	protected $locationCache = null;
	protected $categoriesCache = null;
	protected $iconUrlCache = null;
	protected $addressCache = null;
	
	
	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('location'));
		$plural = ucfirst(Labels::getLocalized('locations'));
		return array(
			'name' => $plural,
            'singular_name' => $singular,
            'add_new' => sprintf(__('Add %s', App::SLUG), $singular),
            'add_new_item' => sprintf(__('Add New %s', App::SLUG), $singular),
            'edit_item' => sprintf(__('Edit %s', App::SLUG), $singular),
            'new_item' => sprintf(__('New %s', App::SLUG), $singular),
            'all_items' => $plural,
            'view_item' => sprintf(__('View %s', App::SLUG), $singular),
            'search_items' => sprintf(__('Search %s', App::SLUG), $plural),
            'not_found' => sprintf(__('No %s found', App::SLUG), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', App::SLUG), $plural),
            'menu_name' => App::getPluginName()
		);
	}
	
	
	static function init() {
		static::$postTypeOptions['rewrite'] = array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX));
		parent::init();
	}
	
	
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\maplocations\model\Route
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	
	
	function getCategories($fields = TaxonomyTerm::FIELDS_MODEL, $params = array()) {
		$atts = md5(serialize(func_get_args()));
		if (empty($this->categoriesCache[$atts])) {
			$this->categoriesCache[$atts] = Category::getPostTerms($this->getId(), $fields, $params);
		}
		return $this->categoriesCache[$atts];
	}
	
	
	function getTags($fields = TaxonomyTerm::FIELDS_MODEL, $params = array()) {
		return RouteTag::getPostTerms($this->getId(), $fields, $params);
	}
	
	
	function setCategories($categoriesIds) {
		return wp_set_post_terms($this->getId(), $categoriesIds, Category::TAXONOMY, $append = false);
	}
	
	
	function setCategoriesNames($categoriesNames) {
		if (!is_array($categoriesNames)) {
			$categoriesNames = array_filter(array_map('trim', explode(',', $categoriesNames)));
		}
		$ids = array();
		foreach ($categoriesNames as $categoryName) {
			if ($category = Category::getByName($categoryName)) {
				$ids[] = $category->getId();
			}
		}
		return $this->setCategories($ids);
	}
	
	
	
	function importCategoriesNames($categoriesNames) {
		if (!is_array($categoriesNames)) {
			$categoriesNames = array_filter(array_map('trim', explode(',', $categoriesNames)));
		}
		$ids = array();
		foreach ($categoriesNames as $categoryName) {
			if ($category = Category::getByName($categoryName)) {
				$ids[] = $category->getId();
			} else {
				$term = wp_insert_term($categoryName, Category::TAXONOMY);
				if ($term AND !is_wp_error($term) AND is_array($term) AND isset($term['term_id'])) {
					$ids[] = $term['term_id'];
				}
			}
		}
		return $this->setCategories($ids);
	}
	
	
	function addDefaultCategory() {
		$term = get_term('General', Category::TAXONOMY);
		if (empty($term)) {
			$terms = get_terms(array(Category::TAXONOMY), array('hide_empty' => false));
			if (!empty($terms)) {
				$term = reset($terms);
			}
		}
		if (!empty($term)) {
			wp_set_post_terms($this->getId(), $term->term_id, Category::TAXONOMY);
		}
	}
	
	
	function getUserEditUrl() {
		return RouteController::getDashboardUrl('edit', array('id' => $this->getId()));
	}
	
	
	function getUserDeleteUrl() {
		return RouteController::getDashboardUrl('delete', array(
			'id' => $this->getId(),
			'nonce' => wp_create_nonce(DashboardController::DELETE_NONCE),
		));
	}
	
	
	function getImages() {
		if ($id = $this->getId()) {
			return Attachment::getForPost($id);
		} else {
			return array();
		}
	}
	
	
	function getImagesIds() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'posts_per_page' => -1,
				'post_type' => Attachment::POST_TYPE,
				'post_status' => 'any',
				'post_parent' => $id,
				'fields' => 'ids',
				'orderby' => 'menu_order',
				'order' => 'asc',
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			));
		} else {
			return array();
		}
	}
	
	
	function setImages($images) {
		global $wpdb;
		
		if (!is_array($images)) {
			$images = array_filter(explode(',', $images));
		}
		
		$currentIds = $this->getImagesIds();
		$postedImagesIds = array_filter(array_map('intval', array_map('trim', $images)));
		
		$toAdd = array_diff($postedImagesIds, $currentIds);
		$toDelete = array_diff($currentIds, $postedImagesIds);
		
		if (!empty($toAdd)) $wpdb->query("UPDATE $wpdb->posts SET post_parent = ". intval($this->getId()) ." WHERE ID IN (" . implode(',', $toAdd) . ")");
		if (!empty($toDelete)) $wpdb->query("UPDATE $wpdb->posts SET post_parent = 0 WHERE ID IN (" . implode(',', $toDelete) . ")");
		
		// Change the sorting order
		foreach ($images as $i => $id) {
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = ". intval($i) ." WHERE ID = ". intval($id) ." LIMIT 1");
		}
		
	}
	
	
	
	function getLocationsIds() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'fields' => 'ids',
				'post_type' => Location::POST_TYPE,
				'post_parent' => $id,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'asc',
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			));
		} else return array();
	}
	
	
	function getLocations() {
		if ($id = $this->getId()) {
			return array_map(array(App::namespaced('model\Location'), 'getInstance'), get_posts(array(
				'post_type' => Location::POST_TYPE,
				'post_parent' => $id,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'asc',
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			)));
		} else return array();
	}
	
	
	
	function getJSLocations() {
		$route = $this;
		return array_map(function(Location $location) use ($route) {
			return array(
				'id' => $location->getId(),
				'name' => $route->getTitle(),
				'lat' => $location->getLat(),
				'long' => $location->getLong(),
				'description' => $location->getContent(),
				'type' => $location->getLocationType(),
				'address' => $location->getAddress(),
				'postal_code' => $location->getPostalCode(),
				'phone_number' => $location->getPhoneNumber(),
				'website' => $location->getWebsite(),
				'email' => $location->getEmail(),
				'icon' => $location->getRoute()->getIconUrl(),
				'iconSize' => $location->getRoute()->getIconSize(),
				'images' => array_map(function(Attachment $image) {
					return array(
						'id' => $image->getId(),
						'url' => $image->getImageUrl(Attachment::IMAGE_SIZE_FULL),
						'thumb' => $image->getImageUrl(Attachment::IMAGE_SIZE_THUMB)
					);
				}, $location->getImages())
			);
		}, $this->getLocations());
	}
	
	
	static function getIndexMapJSLocations(\WP_Query $query) {
		global $wpdb;
		
// 		var_dump($query->request);
// 		var_dump($query->is_main_query());
// 		$query->get_posts();
// 		var_dump($query->is_main_query());
// 		var_dump($query->request);exit;
		
		$joinString = $wpdb->prepare("
			JOIN $wpdb->posts l ON l.post_parent = $wpdb->posts.ID AND l.post_type = %s
			JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key = %s
			JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key = %s",
			Location::POST_TYPE,
			Location::META_LAT,
			Location::META_LONG
		);
		
		$selectString = "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID AS id, $wpdb->posts.post_title AS name,
				lm_lat.meta_value AS lat, lm_lon.meta_value AS `long`";
		
		$sql = $query->request;
// 		var_dump($sql);
		$sql = preg_replace('~LIMIT [0-9]+, [0-9]+~i', '', $sql);
		$sql = preg_replace('~^SELECT SQL_CALC_FOUND_ROWS .+ FROM \w+ ~i', $selectString . " FROM $wpdb->posts ", $sql);
		$sql = str_replace('WHERE 1=1', $joinString . PHP_EOL . "WHERE 1=1", $sql);
// 		var_dump($sql);
		$routes = $wpdb->get_results($sql, ARRAY_A);
		
		
		foreach ($routes as $i => $row) {
			/* @var $route Route */
			$route = Route::getInstance($row['id']);
			$routes[$i]['permalink'] = $route->getPermalink();
			$routes[$i]['type'] = Location::TYPE_LOCATION;
			$routes[$i]['icon'] = $route->getIconUrl();
			$routes[$i]['iconSize'] = $route->getIconSize();
			$routes[$i]['infowindow'] = RouteController::getInfoWindowView($route);
			Route::clearInstances();
		}
		
		return $routes;
		
	}
	
	
	
	static function canCreate($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_CREATE);
		if (empty($access)) $access = Settings::ACCESS_USER;
		$result = self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_CREATE_CAP),
			$userId
		);
        return apply_filters('cmloc_route_can_create', $result, $userId);
	}
	
	
	function canEdit($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_EDIT);
		if (empty($access)) $access = Settings::ACCESS_USER;
		$result = self::checkAccess(
				$access,
				$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_EDIT_CAP),
				$userId
				);
		$result = (user_can($userId, 'manage_options') OR ($userId == $this->getAuthorId() AND $result));
		return apply_filters('cmmrm_route_can_edit', $result, $userId);
	}
	
	
	function canView($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_VIEW);
		if (empty($access)) $access = Settings::ACCESS_GUEST;
		return self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_VIEW_CAP),
			$userId
		);
	}
	
	
	static function canViewIndex($userId = null) {
		$access = Settings::getOption(Settings::OPTION_ACCESS_MAP_INDEX);
		if (empty($access)) $access = Settings::ACCESS_GUEST;
		return self::checkAccess(
			$access,
			$capability = Settings::getOption(Settings::OPTION_ACCESS_MAP_INDEX_CAP),
			$userId
		);
	}
	
	
	function canDelete($userId = null) {
		return $this->canEdit($userId);
	}
	
	
	function getRate() {
		return intval($this->getPostMeta(static::META_RATING_CACHE));
	}
	
	
	function updateRatingCache() {
		global $wpdb;
		$rating = $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value)/COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
				$this->getId(),
				self::META_RATE
				));
		$rating = intval($rating);
		return $this->setPostMeta(static::META_RATING_CACHE, $rating);
	}
	
	
	function canRate() {
		$userId = is_user_logged_in();
		return !empty($userId);
	}
	
	
	function didUserRate() {
		global $wpdb;
		$userId = get_current_user_id();
		if (empty($userId)) return null;
		$sql = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s AND meta_value = %d",
			$this->getId(),
			self::META_RATE_USER_ID .'%',
			$userId
		);
		$count = $wpdb->get_var($sql);
		return ($count > 0);
	}
	
	
	function rate($rate) {
		$id = add_post_meta($this->getId(), self::META_RATE, $rate, $unique= false);
		if ($id) {
			add_post_meta($this->getId(), self::META_RATE_TIME .'_'. $id, time());
			add_post_meta($this->getId(), self::META_RATE_USER_ID .'_'. $id, get_current_user_id());
			$this->updateRatingCache();
			return $id;
		}
	}
	
	
	function getVotesNumber() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s",
			$this->getId(),
			self::META_RATE
		));
	}
	
	
	function getRelatedRoutes($limit = 5) {
		return array_map(array(get_called_class(), 'getInstance'), get_posts(array(
			'posts_per_page' => $limit,
			'post_type' => static::POST_TYPE,
			'post_status' => 'publish',
			'orderby' => 'id',
			'order' => 'desc',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
// 			'category' => implode(',', $this->getCategories(Category::FIELDS_ID_SLUG)),
			'exclude' => $this->getId(),
			'tax_query' => array(
				array(
					'taxonomy' => Category::TAXONOMY,
					'field' => 'id',
					'terms' => $this->getCategories(Category::FIELDS_IDS),
					'include_children' => false,
				),
				array(
					'taxonomy' => Tag::TAXONOMY,
					'field' => 'id',
					'terms' => $this->getTags(Tag::FIELDS_IDS),
				),
				'relation' => 'OR',
			),
		)));
	}
	
	
	function updateLocationsAltitudes() {
		return;
		$locations = $this->getLocations();
		if (!empty($locations)) {
			$result = Location::downloadEvelations($locations);
			foreach ($locations as $i => $location) {
				if (isset($result['results'][$i]) AND $location->getAltitude() != $result['results'][$i]['elevation']) {
					$location->setAltitude($result['results'][$i]['elevation']);
				}
			}
		}
	}
	
	
	
	static function checkAccess($access, $capability, $userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		
		if (user_can($userId, 'manage_options')) {
			return true;
		}
		
		switch ($access) {
			case Settings::ACCESS_GUEST:
				return true;
				break;
			case Settings::ACCESS_USER:
				return !empty($userId);
				break;
			case Settings::ACCESS_CAPABILITY:
				return (!empty($userId) AND user_can($userId, $capability));
			default:
				if (!empty($userId) AND $user = get_userdata($userId)) {
					return in_array($access, $user->roles);
				}
				break;
		}
		return false;
	}
	
	
	
	function getShortDescription($maxlen = 100) {
		$content = preg_replace('/[\s\n\r\t]+/', ' ', strip_tags($this->getContent()));
		if (strlen($content) > $maxlen) {
			$content = substr($content, 0, $maxlen) . '...';
		}
		return $content;
	}
	
	
	function getFormattedDistance() {
		
		$dist = $this->getDistance();
		$useMinor = $this->useMinorLengthUnits();
		
		if (Settings::UNIT_FEET == Settings::getOption(Settings::OPTION_UNIT_LENGTH)) {
			$num = $dist/Settings::FEET_TO_METER;
			if (!$useMinor AND $num > Settings::FEET_IN_MILE) {
				return number_format(round($num/Settings::FEET_IN_MILE)) .' miles';
			} else {
				return number_format(floor($num)) .' ft';
			}
		} else {
			if (!$useMinor AND $dist > 2000) {
				return round($dist/1000) .' km';
			} else {
				return $dist .' m';
			}
		}
		
	}
	
	
	static function formatLength($dist) {
		if (Settings::UNIT_FEET == Settings::getOption(Settings::OPTION_UNIT_LENGTH)) {
			$num = $dist/Settings::FEET_TO_METER;
			if ($num > Settings::FEET_IN_MILE) {
				return number_format(round($num/Settings::FEET_IN_MILE)) .' miles';
			} else {
				return number_format(floor($num)) .' ft';
			}
		} else {
			if ($dist > 2000) {
				return round($dist/1000) .' km';
			} else {
				return $dist .' m';
			}
		}
	}
	
	
	static function formatElevation($dist) {
		if (Settings::UNIT_FEET == Settings::getOption(Settings::OPTION_UNIT_LENGTH)) {
			$num = round($dist/Settings::FEET_TO_METER);
			return number_format($num) .' ft';
		} else {
			return $dist .' m';
		}
	}
	
	
	static function formatSpeed($meterPerSec) {
		if (Settings::UNIT_FEET == Settings::getOption(Settings::OPTION_UNIT_LENGTH)) {
			return round($meterPerSec/Settings::FEET_TO_METER/Settings::FEET_IN_MILE*3600) . ' mph';
		} else {
			return round($meterPerSec * 3.6) . ' km/h';
		}
	}
	
	
	static function formatTime($sec) {
		$num = $sec;
		$label = round($num) .' s';
		if ($num > 60) {
			$num /= 60;
			$label = round($num) .' min';
		}
		if ($num > 60) {
			$label = floor($num/60) .' h '. ($num%60) .' min ';
		}
		return $label;
	}
	
	
	function getIcon() {
		return GoogleMapsIcons::fixHttps($this->getPostMeta(self::META_ICON));
	}
	
	
	function getIconUrl() {
		if (empty($this->iconUrlCache)) {
			$icon = $this->getPostMeta(self::META_ICON);
			if (empty($icon)) {
				if ($categories = $this->getCategories() AND $category = reset($categories)) {
					/* @var $category Category */
					$icon = $category->getIcon();
				}
			}
			if (empty($icon)) $icon = null;
			$this->iconUrlCache = apply_filters('cmloc_route_icon_url', $icon, $this);
		}
		return $this->iconUrlCache;
	}
	
	
	function setIconUrlCache($url) {
		$this->iconUrlCache = $url;
		return $this;
	}
	
	
	function setIcon($value) {
		return $this->setPostMeta(self::META_ICON, $value);
	}
	
	
	function getIconSize() {
		$val = $this->getPostMeta(self::META_ICON_SIZE);
		if (empty($val)) $val = static::ICON_SIZE_NORMAL;
		return $val;
	}
	
	function setIconSize($value) {
		return $this->setPostMeta(self::META_ICON_SIZE, $value);
	}
	
	
	function showWeatherPerLocation() {
		return (1 == $this->getPostMeta(self::META_SHOW_WEATHER_PER_LOCATION));
	}
	
	
	function setWeatherPerLocation($val) {
		return $this->setPostMeta(self::META_SHOW_WEATHER_PER_LOCATION, intval($val));
	}
	
	
	static function getPaginationLimit() {
		return Settings::getOption(Settings::OPTION_PAGINATION_LIMIT);
	}
	
	
	function getPostMetaKey($name) {
		return $name;
	}
	
	
	function getAddress() {
		if (empty($this->addressCache)) {
			if ($location = $this->getLocation()) {
				$this->addressCache = $location->getAddress();
			}
		}
		return $this->addressCache;
	}
	
	
	/**
	 * Returns the location instance.
	 * 
	 * @return Location
	 */
	function getLocation() {
		if (empty($this->locationCache)) {
			$locations = $this->getLocations();
			if ($location = reset($locations)) {
				$this->setLocationCache($location);
			} else {
				return null;
			}
		}
		return $this->locationCache;
	}
	
	
	function setLocationCache(Location $location) {
		$this->locationCache = $location;
		return $this;
	}
	
	
	function getPostalCode() {
		if ($location = $this->getLocation()) {
			return $location->getPostalCode();
		}
	}
	
	
	function setViews($val) {
		update_post_meta($this->getId(), self::META_VIEWS, $val);
		return $this;
	}
	
	
	function getViews() {
		return get_post_meta($this->getId(), self::META_VIEWS, $single = true);
	}
	
	
	function incrementViews() {
		$this->setViews($this->getViews() + 1);
	}
	
	
	function save() {
		$id = $this->getId();
		$result = parent::save();
		if ($result) {
			if (!$id) {
				$this->setViews(0);
			}
			$this->updateRatingCache();
		}
		return $result;
	}
	
	
	
	static function registerQueryOrder(\WP_Query $query, $orderby = null, $order = null) {
		$orderby = Settings::getIndexOrderBy();
		$order = Settings::getIndexOrder();
		switch ($orderby) {
			case Settings::ORDERBY_VIEWS:
				$query->set('meta_key', self::META_VIEWS);
				$orderby = 'meta_value_num';
				break;
			case Settings::ORDERBY_RATING:
				$query->set('meta_key', self::META_RATING_CACHE);
				$orderby = 'meta_value_num';
				break;
		}
		$query->set('orderby', $orderby);
		$query->set('order', $order);
	}
	
	
	static function findLocationByAddress($address) {
	
		if (empty($address)) return array();
		
		$cache = get_transient(static::TRANSIENT_GEOLOCATION_BY_ADDR_CACHE);
		if (is_array($cache) AND isset($cache[$address])) {
			return $cache[$address];
		}
	
		$url = 'https://maps.googleapis.com/maps/api/geocode/json';
	
		$url = add_query_arg(urlencode_deep(array(
			'address' => $address,
		)), $url);


		$result = RemoteConnection::getRemoteJson($url);
		if (is_array($result) AND !empty($result['results']) AND !empty($result['status']) AND $result['status'] == 'OK') {
			$coords = array($result['results'][0]['geometry']['location']['lat'], $result['results'][0]['geometry']['location']['lng']);
			$cache[$address] = $coords;
			set_transient(static::TRANSIENT_GEOLOCATION_BY_ADDR_CACHE, $cache);
			return $coords;
		}
	
	}
	
	
	function getPermalink() {
		return site_url('/' . Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) . '/' . $this->post->post_name);
	}
	

	function acceptByModerator() {
		$this->setStatus('publish')->save();
		do_action('cmloc_route_accepted_by_moderator', $this);
		return $this->setPostMeta(static::META_MODERATOR_ACCEPTED, 1);
	}
	
	
	function trashByModerator() {
		do_action('cmloc_route_trashed_by_moderator', $this);
		wp_trash_post($routeId);
	}
	
	
	function isAcceptedByModerator() {
		return ($this->getPostMeta(static::META_MODERATOR_ACCEPTED) == 1);
	}
	
	
	function setLat($lat) {
		return $this->setPostMeta(static::META_LATITUDE, $lat);
	}
	
	
	function getLat() {
		return $this->getPostMeta(static::META_LATITUDE);
	}
	
	
	function setLong($long) {
		return $this->setPostMeta(static::META_LONGITUDE, $long);
	}
	
	
	function getLong() {
		return $this->getPostMeta(static::META_LONGITUDE);
	}
	
	
	static function getShortcodeTokensFuncMap() {
		return array(
			'[name]' => 'getTitle',
			'[description]' => 'getContent',
			'[author]' => 'getAuthorDisplayName',
			'[permalink]' => 'getPermalink',
		);
	}
	
	
}
