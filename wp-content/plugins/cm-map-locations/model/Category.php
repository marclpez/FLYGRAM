<?php

namespace com\cminds\maplocations\model;
use com\cminds\maplocations\helper\GoogleMapsIcons;

class Category extends TaxonomyTerm {

	const TAXONOMY = 'cmloc_category';
	const CATEGORY_PERMALINK_PART = 'category';
	const OPTION_LOCATIONS_DEFAULT_ICONS = 'cmloc_category_icons';
	
	static function init() {
		parent::init();
		
		// Register taxonomy
		$args = array(
            'hierarchical' => TRUE,
            'labels' => static::getTaxonomyLabels(),
            'show_ui' => FALSE, // to override in pro
            'query_var' => TRUE,
			'show_admin_column' => true,
			'post_types' => array(Route::POST_TYPE),
			'public' => true,
			'rewrite' => array('slug' => self::getUrlPart()),
        );
		register_taxonomy(static::TAXONOMY, $args['post_types'], apply_filters('cmloc_category_term_args', $args));
		
		// Create General category if no categories exists
// 		global $wpdb;
// 		$count = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE taxonomy = %s", static::TAXONOMY)));
// 		if ($count == 0) \wp_insert_term('All Videos', static::TAXONOMY);
		
	}
	
	
	static function getUrlPart() {
		return Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) .'-' . self::CATEGORY_PERMALINK_PART;
	}
	
	
	static function getTaxonomyLabels() {
		$plural = ucfirst(Labels::getLocalized('categories'));
		$singular = ucfirst(Labels::getLocalized('category'));
        return array(
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => 'Search ' . $plural,
            'popular_items' => 'Popular ' . $plural,
            'all_items' => 'All ' . $plural,
            'parent_item' => 'Parent ' . $singular,
            'parent_item_colon' => 'Parent ' . $singular . ':',
            'edit_item' => 'Edit ' . $singular,
            'update_item' => 'Update ' . $singular,
            'add_new_item' => 'Add New ' . $singular,
            'new_item_name' => 'New ' . $singular . ' Name',
            'menu_name' => $plural,
        );
    }
    
    
    /**
	 * Get instance
	 * 
	 * @param object|int $term Term object or ID
	 * @return com\cminds\maplocations\model\Category
	 */
	static function getInstance($term) {
		return parent::getInstance($term);
	}
	

	/**
	 * Get instance by name
	 *
	 * @param string $name Term name
	 * @return com\cminds\maplocations\model\Category
	 */
	static function getByName($name) {
		if ($term = get_term_by('name', $name, static::TAXONOMY)) {
			return static::getInstance($term);
		}
	}
	
		
	function getEditUrl() {
		return admin_url(sprintf('edit-tags.php?action=edit&taxonomy=%s&tag_ID=%d&post_type=%s',
			Category::TAXONOMY,
			$this->getId(),
			Route::POST_TYPE
		));
	}
	
	
	function getIcon() {
		$options = get_option(self::OPTION_LOCATIONS_DEFAULT_ICONS, array());
		$id = $this->getId();
		if (isset($options[$id])) {
			return GoogleMapsIcons::fixHttps($options[$id]);
		}
	}
	
	
	function setIcon($icon) {
		$options = get_option(self::OPTION_LOCATIONS_DEFAULT_ICONS, array());
		$id = $this->getId();
		$options[$id] = $icon;
		update_option(self::OPTION_LOCATIONS_DEFAULT_ICONS, $options, true);
		return $this;
	}
	
    
}
