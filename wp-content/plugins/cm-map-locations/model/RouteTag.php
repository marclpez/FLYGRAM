<?php

namespace com\cminds\maplocations\model;

use com\cminds\maplocations\controller\FrontendController;

class RouteTag extends Tag {

    /**
	 * Get instance
	 * 
	 * @param object|int $term Term object or ID
	 * @return com\cminds\maplocations\model\RouteTag
	 */
	static function getInstance($term) {
		return parent::getInstance($term);
	}
	
	
	function getPermalink() {
		return FrontendController::getUrl('', array('tag' => $this->getSlug()));
	}
	
	
	
	static function getAll($fields = self::FIELDS_MODEL, $params = array()) {
		global $wpdb;
		
		$sql = $wpdb->prepare("SELECT t.*, tt.taxonomy, COUNT(p.ID) AS routes_number
				FROM $wpdb->terms t
				JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id AND tt.taxonomy = %s
				JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
				JOIN $wpdb->posts p ON tr.object_id = p.ID AND p.post_type = %s AND p.post_status = 'publish'
			GROUP BY t.term_id
			ORDER BY t.name ASC",
			static::TAXONOMY,
			Route::POST_TYPE
		);
		
		if (!empty($params['number']) AND is_numeric($params['number'])) {
			$sql .= ' LIMIT '. $params['number'];
		}
		
// 		var_dump($sql);
		
		$terms = $wpdb->get_results($sql);
		
		if ($fields == self::FIELDS_MODEL) {
			foreach ($terms as &$term) {
				$obj = static::getInstance($term);
				$obj->routes_number = $term->routes_number;
				$term = $obj;
			}
		}
		return $terms;
		
	}
	
    
}
