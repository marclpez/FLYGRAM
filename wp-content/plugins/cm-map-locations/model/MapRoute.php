<?php

namespace com\cminds\maplocations\model;

use com\cminds\maplocations\helper\PolylineEncoder;

use com\cminds\maplocations\controller\RouteController;

/**
 * This model is used  for the Map Routes integration.
 *
 */
class MapRoute extends PostType {
	
	const POST_TYPE = 'cmmrm_route';
	const LOCATION_POST_TYPE = 'cmmrm_location';
	const LOCATION_META_LAT = '_cmmrm_latitude';
	const LOCATION_META_LONG = '_cmmrm_longitude';
	const META_PATH_COLOR = '_cmmrm_path_color';
	const META_OVERVIEW_PATH = '_cmmrm_overview_path';
	
	protected $featuredImageUrlCache = array();
	protected $categoriesCache = null;
	protected $locationCache = null;
	
	static function registerPostType() {
		// Don't
	}
	
	
	function getCategories($fields = TaxonomyTerm::FIELDS_MODEL, $params = array()) {
		$atts = md5(serialize(func_get_args()));
		if (empty($this->categoriesCache[$atts])) {
			$this->categoriesCache[$atts] = MapRoutesCategory::getPostTerms($this->getId(), $fields, $params);
		}
		return $this->categoriesCache[$atts];
	}
	
	static function getIndexMapJSLocations() {
		global $wpdb;
		
		$joinString = '';
		$whereString = '';
	
		$sql = $wpdb->prepare("SELECT r.*,
			l.ID AS location_id,
			img_src.meta_value AS featured_image_url,
			lm_lat.meta_value AS lat, lm_lon.meta_value AS `long`,
			rm_pc.meta_value AS `pathColor`,
			rm_op.meta_value AS `path`
			FROM $wpdb->posts r
			LEFT JOIN $wpdb->posts l ON l.post_parent = r.ID AND l.post_type IN (%s, %s) AND l.menu_order IN (0,1)
			LEFT JOIN $wpdb->postmeta lm_lat ON lm_lat.post_id = l.ID AND lm_lat.meta_key IN (%s, %s)
			LEFT JOIN $wpdb->postmeta lm_lon ON lm_lon.post_id = l.ID AND lm_lon.meta_key IN (%s, %s)
			LEFT JOIN $wpdb->postmeta rm_pc ON rm_pc.post_id = r.ID AND rm_pc.meta_key IN (%s, %s)
			LEFT JOIN $wpdb->postmeta rm_op ON rm_op.post_id = r.ID AND rm_op.meta_key IN (%s, %s)
			LEFT JOIN $wpdb->posts img ON img.post_parent = r.ID AND img.post_type = %s AND img.post_mime_type LIKE '%%image%%'
			LEFT JOIN $wpdb->postmeta img_src ON img_src.post_id = img.ID AND img_src.meta_key = %s
			$joinString
			WHERE r.post_type IN (%s, %s) AND r.post_status = 'publish' $whereString
			GROUP BY r.ID
			",
			Location::POST_TYPE,
			self::LOCATION_POST_TYPE,
			Location::META_LAT,
			self::LOCATION_META_LAT,
			Location::META_LONG,
			self::LOCATION_META_LONG,
			Route::META_PATH_COLOR,
			self::META_PATH_COLOR,
			Route::META_OVERVIEW_PATH,
			self::META_OVERVIEW_PATH,
			Attachment::POST_TYPE,
			Attachment::META_WP_ATTACHED_FILE,
			Route::POST_TYPE,
			self::POST_TYPE
		);
	
		$routes = $wpdb->get_results($sql, ARRAY_A);
// 		var_dump($routes);
		
		$locationsIds = array();
		foreach ($routes as $route) {
			$id = $route['location_id'];
			$locationsIds[$id] = $id;
		}
		
		$locationsIds = array_filter($locationsIds);
		
		if ($locationsIds) {
			$locationsRows = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID IN (". implode(',', $locationsIds) .")");
			foreach ($locationsRows as $row) {
				$locations[$row->ID] = $row;
			}
		} else {
			$locations = array();
		}
	
		foreach ($routes as $i => &$row) {
			$routes[$i]['type'] = Location::TYPE_LOCATION;
			if ($row['post_type'] == Route::POST_TYPE) { // Map Location
				$locationId = $row['location_id'];
				if (empty($locationId)) {
					$row = null;
				} else {
					/* @var $route Route */
					$route = new Route($row);
					$routes[$i]['permalink'] = $route->getPermalink();
	// 				var_dump($locationId);
					if (isset($locations[$locationId]) AND $location = new Location($locations[$locationId])) {
						$route->setLocationCache($location);
					}
	// 				$route = Route::getInstance($row['ID']);
					$routes[$i]['icon'] = $route->getIconUrl();
					$routes[$i]['infowindow'] = RouteController::getInfoWindowView($route);
					$routes[$i]['categories'] = $route->getCategories(TaxonomyTerm::FIELDS_ID_NAME);
	// 				var_dump($row['ID']);
	// 				var_dump($routes[$i]['categories']);
					Route::clearInstances();
				}
			} else { // Maps Routes
				/* @var $route MapRoute */
// 				$route = MapRoute::getInstance($row['ID']);
				$route = new MapRoute($row);
				$routes[$i]['permalink'] = $route->getPermalink();
				$routes[$i]['infowindow'] = self::getInfoWindow($route);
// 				var_dump($row['ID']);
// 				var_dump($route->getId());
				$routes[$i]['categories'] = $route->getCategories(TaxonomyTerm::FIELDS_ID_NAME);
				$startingPoint = $route->getStartingPointCoords();
				if ($startingPoint) {
					$row['lat'] = $startingPoint[0];
					$row['long'] = $startingPoint[1];
				}
			}
			unset($row['post_type']);
		}
	
		return array_filter($routes);
	
	}
	
	
	
	function getStartingPointCoords() {
		if ($location = $this->getFirstLocation()) {
			return array(
				get_post_meta($location->ID, static::LOCATION_META_LAT, 0),
				get_post_meta($location->ID, static::LOCATION_META_LONG, 0),
			);
		}
	}
	
	
	function getOverviewPath() {
		return (string)get_post_meta($this->getId(), self::META_OVERVIEW_PATH, $single = true);
	}
	
	
	function getImages() {
		if ($id = $this->getId()) {
			return array_values(array_filter(Attachment::getForPost($id), function($image) { return $image->isImage(); }));
		} else {
			return array();
		}
	}
	
	
	function generateFeaturedImageUrlCache($fullUrl) {
		$this->setFeaturedImageUrlCache($fullUrl);
		$thumbUrl = preg_replace('~(.+)(\.[a-z0-9]{2,4})~');
	}
	
	
	function setFeaturedImageUrlCache($url, $size = Attachment::IMAGE_SIZE_FULL) {
		$this->featuredImageUrlCache[$size] = $url;
		return $this;
	}
	
	
	function getFeaturedImageUrl($size = Attachment::IMAGE_SIZE_FULL, $icon = false) {
		if (empty($this->featuredImageUrlCache[$size])) {
			if ($images = $this->getImages()) {
				$image = reset($images);
				$this->featuredImageUrlCache[$size] = $image->getImageUrl($size, $icon);
			}
		}
		return (isset($this->featuredImageUrlCache[$size]) ? $this->featuredImageUrlCache[$size] : '');
	}
	
	
	static function getInfoWindow(MapRoute $route) {
		return RouteController::loadFrontendView('infowindow-map-route', compact('route'));
	}
	
	
	function getPermalink() {
		return site_url('/' . get_option('cmmrm_permalink_prefix', 'maps-routes') . '/' . $this->post->post_name);
	}
	
	
	/**
	 * Returns the location instance.
	 *
	 * @return Location
	 */
	function getFirstLocation() {
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
	
	
	function setLocationCache($location) {
		$this->locationCache = $location;
		return $this;
	}
	
	
	function getLocations() {
		if ($id = $this->getId()) {
			return get_posts(array(
				'post_type' => static::LOCATION_POST_TYPE,
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
	
	
}
