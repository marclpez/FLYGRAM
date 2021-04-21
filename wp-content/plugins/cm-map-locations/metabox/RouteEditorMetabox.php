<?php

namespace com\cminds\maplocations\metabox;

use com\cminds\maplocations\model\Route;

class RouteEditorMetabox extends MetaBox {
	
	const SLUG = 'location-editor';
	const NAME = 'Location Editor';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	const META_BOX_PRIORITY = 5;
	const SAVE_POST_PRIORITY = 10;
	
	static protected $supportedPostTypes = array(Route::POST_TYPE);
	
	
	static function render($post) {
		$route = Route::getInstance($post);
		if (!empty($post->ID) AND $post->post_status == 'publish' AND $route) {
			$url = $route->getUserEditUrl();
			printf('<a href="%s" class="button">%s</a>', esc_attr($url), 'Open Route Editor');
		}
	}
	
}