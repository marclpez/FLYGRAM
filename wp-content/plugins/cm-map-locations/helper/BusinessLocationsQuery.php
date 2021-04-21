<?php

namespace com\cminds\maplocations\helper;

use com\cminds\maplocations\model\Business;
use com\cminds\maplocations\model\BusinessCategory;

class BusinessLocationsQuery {
	
	static function getResults($query) {
		
		$maxJoins = apply_filters('cmloc_business_locations_query_max_joins', 9999, $query);
		$metaJoins = array(
			Business::META_ADDRESS,
			Business::META_CITY_TOWN,
			Business::META_COUNTRY,
			Business::META_POSTAL_CODE,
			Business::META_REGION,
			Business::META_STATE_COUNTY,
		);
		
		$initialJoins = 2; // for lat & long
		if (!empty($query[BusinessCategory::TAXONOMY])) {
			$initialJoins += 3; // for terms, term_taxonomy, term_object
		}
		
		$results = array();
		$joins = array();
		if (empty($query['s']) OR $initialJoins >= $maxJoins) {
			
			// Don't add any meta JOINs because there's no search string or it will exceed the limit:
			$results[] = static::singleQuery($query, $joins = array());
			
		} else {
			
			while (!empty($metaJoins)) {
				while (count($joins) < $maxJoins-$initialJoins AND !empty($metaJoins)) {
					$joins[] = array_pop($metaJoins);
				}
				
				$results[] = static::singleQuery($query, $joins);
				$joins = array();
				
			}
			
		}
		
// 		var_dump(array_keys($results));
		
		return static::mergeResults($results);
		
	}
	
	
	
	protected static function singleQuery(array $query, array $joins) {
		global $wpdb;
		
		$whereString = $wpdb->prepare(" WHERE b.post_type = %s AND b.post_status = 'publish'", Business::POST_TYPE);
		
		if (!empty($query['s'])) {
			$like = '%' . $query['s'] .'%';
			$whereString .= $wpdb->prepare(' AND (
				b.post_title LIKE %s OR b.post_content LIKE %s',
				$like, $like
			);
			
			foreach ($joins as $joinMetaKey) {
				$whereString .= $wpdb->prepare(' OR bm_'. $joinMetaKey .'.meta_value LIKE %s ', $like);
			}
			
			$whereString .= ')';
		}
		
		$joinString = '';
		
		// Add taxonomy
		if (!empty($query[BusinessCategory::TAXONOMY])) {
			$joinString = $wpdb->prepare("JOIN $wpdb->term_relationships tr ON tr.object_id = b.ID
					JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = %s
					JOIN $wpdb->terms t ON tt.term_id = t.term_id", BusinessCategory::TAXONOMY);
			if (is_numeric($query[BusinessCategory::TAXONOMY])) {
				$whereString .= $wpdb->prepare(' AND t.term_id = %d', $query[BusinessCategory::TAXONOMY]);
			} else {
				$whereString .= $wpdb->prepare(' AND t.slug = %s', $query[BusinessCategory::TAXONOMY]);
			}
		}
		
		if (!empty($query['post__in'])) {
			$whereString .= ' AND b.ID IN ('. implode(',', $query['post__in']) .')';
		}
		
		$sql = $wpdb->prepare("SELECT b.ID AS id, b.post_title AS name,
			b.post_title AS name, bm_lat.meta_value AS lat, bm_lon.meta_value AS `long`
			FROM $wpdb->posts b
			JOIN $wpdb->postmeta bm_lat ON bm_lat.post_id = b.ID AND bm_lat.meta_key = %s
			JOIN $wpdb->postmeta bm_lon ON bm_lon.post_id = b.ID AND bm_lon.meta_key = %s",
			Business::META_LAT,
			Business::META_LONG
		);
		
		foreach ($joins as $joinMetaKey) {
			$sql .= $wpdb->prepare("LEFT JOIN $wpdb->postmeta bm_". $joinMetaKey ." ON bm_". $joinMetaKey .".post_id = b.ID AND bm_". $joinMetaKey .".meta_key = %s",
					$joinMetaKey);
		}
		
		$sql .= $joinString . $whereString;
		return $wpdb->get_results($sql, ARRAY_A);
		
	}
	
	
	
	protected static function mergeResults($results) {
		$final = array();
		foreach ($results as $resultArr) {
			$final = array_merge($final, $resultArr);
		}
		return static::array_unique($final, 'id');
	}
	
	
	protected static function array_unique($array, $key) {
		$res = array();
		foreach ($array as $record) {
			$res[$record[$key]] = $record;
		}
		return array_values($res);
	}
	
		
}