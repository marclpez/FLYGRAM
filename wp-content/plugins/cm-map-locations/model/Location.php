<?php

namespace com\cminds\maplocations\model;
use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\controller\DashboardController;

use com\cminds\maplocations\App;
use com\cminds\maplocations\helper\RemoteConnection;

/**
 * This model represents the single location marker.
 * 
 * It's post_parent is the actual location object itself.
 *
 */
class Location extends PostType {
	
	const POST_TYPE = 'cmloc_location';
	
	const META_LAT = '_cmloc_latitude';
	const META_LONG = '_cmloc_longitude';
	const META_ALTITUDE = '_cmloc_altitude';
	const META_LOCATION_TYPE = '_cmloc_loc_type';
	const META_ADDRESS = '_cmloc_address';
	const META_POSTAL_CODE = '_cmloc_postal_code';
	const META_PHONE_NUMBER = '_cmloc_phone_number';
	const META_WEBSITE = '_cmloc_website';
	const META_EMAIL = '_cmloc_email';
	
	const TYPE_LOCATION = 'location';
	const TYPE_WAYPOINT = 'waypoint';
	
	
	static protected $postTypeOptions = array(
		'label' => 'Location',
		'public' => false,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => false,
		'show_in_admin_bar' => false,
		'show_in_menu' => false,
		'hierarchical' => false,
		'supports' => array('title', 'editor'),
		'has_archive' => false,
	);
	
	
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
		static::$postTypeOptions['rewrite'] = array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) . '/location');
		parent::init();
	}
	
	
	static function registerPostType() {
		// do not register
	}
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\maplocations\model\Location
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	
	
	function getUserEditUrl() {
		return RouteController::getDashboardUrl('edit', array('id' => $this->getId()));
	}
	
	
	function getLat() {
		return $this->getPostMeta(self::META_LAT);
	}
	
	function setLat($lat) {
		$this->setPostMeta(self::META_LAT, $lat);
		// Keep parent post's longitude updated
		$this->getRoute()->setLat($lat);
		return $this;
	}
	
	function getLong() {
		return $this->getPostMeta(self::META_LONG);
	}
	
	function setLong($long) {
		$this->setPostMeta(self::META_LONG, $long);
		// Keep parent post's longitude updated
		$this->getRoute()->setLong($long);
		return $this;
	}
	
	function getAddress() {
		return $this->getPostMeta(self::META_ADDRESS);
	}
	
	function setAddress($address) {
		return $this->setPostMeta(self::META_ADDRESS, $address);
	}
	
	function getPostalCode() {
		return $this->getPostMeta(self::META_POSTAL_CODE);
	}
	
	function setPostalCode($code) {
		return $this->setPostMeta(self::META_POSTAL_CODE, $code);
	}
	
	function getPhoneNumber() {
		return $this->getPostMeta(self::META_PHONE_NUMBER);
	}
	
	function setPhoneNumber($val) {
		return $this->setPostMeta(self::META_PHONE_NUMBER, $val);
	}
	
	function getWebsite() {
		return $this->getPostMeta(self::META_WEBSITE);
	}
	
	function setWebsite($val) {
		return $this->setPostMeta(self::META_WEBSITE, $val);
	}
	
	function getEmail() {
		return $this->getPostMeta(self::META_EMAIL);
	}
	
	function setEmail($val) {
		return $this->setPostMeta(self::META_EMAIL, $val);
	}
	
	function getLocationType() {
		$type = $this->getPostMeta(self::META_LOCATION_TYPE);
		if (empty($type)) $type = static::TYPE_LOCATION;
		return $type;
	}
	
	function setLocationType($type) {
		return $this->setPostMeta(self::META_LOCATION_TYPE, $type);
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
	

	function getImages() {
		if ($id = $this->getId()) {
			return Attachment::getForPost($id);
		} else {
			return array();
		}
	}
	
	
	function getAltitude() {
		return get_post_meta($this->getId(), self::META_ALTITUDE, $single = true);
	}
	
	
	function formatAltitude() {
		$alt = $this->getAltitude();
		if (Settings::getOption(Settings::OPTION_UNIT_LENGTH) == Settings::UNIT_FEET) {
			$alt = $alt/Settings::FEET_TO_METER;
			$unit = 'ft';
		} else {
			$unit = 'm';
		}
		return round($alt) .' '. $unit;
	}
	
	
	function setAltitude($alt) {
		update_post_meta($this->getId(), self::META_ALTITUDE, $alt);
		return $this;
	}
	
	
	static function downloadEvelations(array $locations) {
		
		if (empty($locations)) return array();
		
		$url = 'https://maps.googleapis.com/maps/api/elevation/json?sensor=false';
		
		$loc = implode('|', array_map(function(Location $location) {
			return implode(',', array($location->getLat(), $location->getLong()));
		}, $locations));
		
		$url = add_query_arg(urlencode_deep(array(
			'locations' => $loc,
			'key' => Settings::getOption(Settings::OPTION_GOOGLE_MAPS_APP_KEY),
		)), $url);
		
		$result = RemoteConnection::getRemoteJson($url);
		if (is_array($result) AND !empty($result['results']) AND !empty($result['status']) AND $result['status'] == 'OK') {
			return $result;
		}
		
	}
	
	
	function getRoute() {
		return Route::getInstance($this->getParentId());
	}
	
	
	function getPostMetaKey($name) {
		return $name;
	}
	
		
}
