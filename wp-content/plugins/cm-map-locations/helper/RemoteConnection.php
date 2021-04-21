<?php

namespace com\cminds\maplocations\helper;

class RemoteConnection {
	
	
	static function getRemoteJson($url, $args = array()) {
		$result = static::getRemoteBody($url, $args);
		if (strlen($result) > 0) {
			return json_decode($result, true);
		}
	}
	
	
	static function getRemoteFile($url, $args = array()) {
		return static::getRemoteBody($url, $args);
	}
	
	
	static function getRemoteBody($url, $args = array()) {
		$result = static::getRemote($url, $args);
		if (is_array($result) AND isset($result['body'])) {
			return $result['body'];
		} else {
			return null;
		}
	}
	
	
	static function getRemote($url, $args = array()) {
		if (empty($args['timeout'])) $args['timeout'] = 15;
		if (empty($args['sslverify'])) $args['sslverify'] = false;
		$result = wp_remote_get($url, $args);
		if (!is_wp_error($result) AND is_array($result)) {
			return $result;
		} else {
			return null;
		}
	}
	
}