<?php

namespace com\cminds\maplocations\helper;

use com\cminds\maplocations\App;

class GoogleMapsIcons {
	
	
	static function getAll() {
		
		$icons = array();
		include App::path('asset/google-maps-icons.php');
		return apply_filters('cmloc_google_maps_icons', $icons);
		
	}
	
	
	static function fixHttps($url) {
		$find = 'http://maps.google.com/';
		if (substr($url, 0, strlen($find)) == $find) {
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}
	
	
	private static function loadRemoteIcons() {
		
// 		$newIcons = array();
// 		$other = self::loadFromCSV();
// 		foreach ($other as $icon) {
// 			$found = false;
// 			foreach ($icons as $url) {
// 				if (strpos($url, $icon .'.png') !== false) {
// 					$found = true;
// 					break;
// 				}
// 			}
// 			if (!$found) {
// 				$context  = stream_context_create(array('http' => array('method'  => 'GET', 'timeout' => 5)));
// 				$url = 'http://maps.google.com/mapfiles/kml/shapes/'. $icon .'.png';
// 				$res = @file_get_contents($url, false, $context);
// 				if (strlen($res) > 0) {
// 					$newIcons[] = $url;
// 				}
// 			}
// 		}
		
// 		$newIcons = array_merge($newIcons, $icons);
// 		file_put_contents(App::path('asset/google-maps-icons.php'), '<?php'.PHP_EOL.'$icons = '. var_export($newIcons, true) .';');
		
// 		var_export($newIcons);exit;
		
// 		return $icons;
	}
	
	
	protected static function loadFromCSV() {
		$rows = str_getcsv(file_get_contents(App::path('asset/google-maps-icons.csv')), "\n");
		// Delete first title row:
		array_shift($rows);
		foreach ($rows as &$row) {
			$row = str_getcsv($row);
			// Get only first column
			$row = array_shift($row);
		}
		return $rows;
	}
	
	
}
