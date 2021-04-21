<?php

namespace com\cminds\maplocations\metabox;

use com\cminds\maplocations\controller\CategoryController;
use com\cminds\maplocations\model\Category;

class CategoryShortcodesTermField extends TermCustomField {
	
	const FIELD_COURSE_SHORTCODES = 'cmloc_category_shortcodes';

	static protected $supportedTaxonomies = array(Category::TAXONOMY);
	
	static protected $fields = array(
		self::FIELD_COURSE_SHORTCODES => 'Shortcodes',
	);
	
	
	static function displayFields($term = null) {
		if( empty( $term->term_id ) ) return;
		wp_enqueue_style('cmloc-backend');
		parent::displayFields($term);
	}
	
	
	static function get_field_cmloc_category_shortcodes($fieldName, $term = null) {
		if( empty( $term->term_id ) ) return false;
		$id = $term->term_id;
		return CategoryController::loadBackendView('metabox-shortcodes', compact('id'));
	}
	
}
