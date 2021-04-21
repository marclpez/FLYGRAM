<?php

namespace com\cminds\maplocations\model;

class User extends Model {
	
	const META_LAST_GEOLOCATION_LAT = 'cmloc_last_geolocation_lat';
	const META_LAST_GEOLOCATION_LONG = 'cmloc_last_geolocation_long';
	
	
	static function roleExists( $role ) {
	
		if( ! empty( $role ) ) {
			return $GLOBALS['wp_roles']->is_role( $role );
		}
	
		return false;
	}
	
	
	static function generateRandomString($len = 20) {
		$str = '';
		while (strlen($str) < $len) {
			$str .= base_convert(floor(mt_rand(0, PHP_INT_MAX)), 10, 26);
		}
		return substr($str, 0, $len);
	}
	
	
	static function setUserRole($userId, $role) {
		if (User::roleExists($role)) {
			$wp_user_object = new \WP_User($userId);
			$wp_user_object->set_role($role);
		}
	}
	
	
	
	static function logout() {
		wp_destroy_current_session();
		wp_clear_auth_cookie();
	}
	
	
	static function hasRole($roles, $userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		if ($userId AND $user = get_userdata($userId)) {
			if (!is_array($roles)) $roles = array($roles);
			$inner = array_intersect($roles, $user->roles);
			return !empty($inner);
		}
		return false;
	}
	
	
	static function hasCapability($cap, $userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		return user_can($userId, $cap);
	}
	
	
	static function getUserData($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		return get_userdata($userId);
	}
	
	
	static function getByEmail($email) {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users u WHERE user_email = %s", $email));
	}
	
	
	static function loginById($userId) {
		wp_clear_auth_cookie();
		wp_set_auth_cookie($userId);
	}
	
	

	static function getSomeAdminUserId() {
		$admins = get_users(array('role' => 'administrator'));
		return ($admins[0]->ID);
	}
	
	
	
	static function registerLastGeolocation($lat, $long) {
		// For now keep only in session
		if (!session_id()) session_start();
		$_SESSION[static::META_LAST_GEOLOCATION_LAT] = $lat;
		$_SESSION[static::META_LAST_GEOLOCATION_LONG] = $long;
		// 		update_user_meta($userId, static::META_LAST_GEOLOCATION_LAT, $lat);
		// 		update_user_meta($userId, static::META_LAST_GEOLOCATION_LONG, $long);
	}
	
	
	static function getLastGeolocation() {
		if (!session_id()) session_start();
		$lat = (isset($_SESSION[static::META_LAST_GEOLOCATION_LAT]) ? $_SESSION[static::META_LAST_GEOLOCATION_LAT] : null);
		$long = (isset($_SESSION[static::META_LAST_GEOLOCATION_LONG]) ? $_SESSION[static::META_LAST_GEOLOCATION_LONG] : null);
		// 		$lat = '54.352';
		// 		$long = '18.6466';
		return array(
			'lat' => $lat,
			'long' => $long,
			0 => $lat,
			1 => $long,
		);
	}
	
}
