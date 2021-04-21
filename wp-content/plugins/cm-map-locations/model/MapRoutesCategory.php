<?php

namespace com\cminds\maplocations\model;

class MapRoutesCategory extends TaxonomyTerm {

	const TAXONOMY = 'cmmrm_category';
	
	static function init() {
		parent::init();
		
		// Don't Register taxonomy
		
	}
	
    
    /**
	 * Get instance
	 * 
	 * @param object|int $term Term object or ID
	 * @return com\cminds\maplocations\model\MapRoutesCategory
	 */
	static function getInstance($term) {
		return parent::getInstance($term);
	}
	
    
}
