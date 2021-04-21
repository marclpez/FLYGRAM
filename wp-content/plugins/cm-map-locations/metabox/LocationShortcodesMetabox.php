<?php

namespace com\cminds\maplocations\metabox;

use com\cminds\maplocations\controller\RouteController;
use com\cminds\maplocations\model\Route;

class LocationShortcodesMetabox extends MetaBox {
	
	const SLUG = 'location-shortcodes';
	const NAME = 'Shortcodes';
	const CONTEXT = 'side';
	const PRIORITY = 'high';
	const META_BOX_PRIORITY = 10;
	const SAVE_POST_PRIORITY = 10;
	
	static protected $supportedPostTypes = array(Route::POST_TYPE);
	
	
	static function render($post) {
        wp_enqueue_style('cmloc-backend');
        wp_enqueue_script('cmloc-backend');
		$id = $post->ID;
		echo RouteController::loadBackendView('metabox-shortcodes', compact('id'));
	}
	
}