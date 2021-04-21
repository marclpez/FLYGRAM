<?php

namespace com\cminds\maplocations\widget;

use com\cminds\maplocations\shortcode\SearchShortcode;
use com\cminds\maplocations\model\Settings;

class SearchWidget extends Widget {

	const WIDGET_NAME = 'CM Location Manager: Search';
	const WIDGET_DESCRIPTION = 'Displays CM Location Manager search form.';
	
	static protected $widgetFields = array(
		'title' => array(
			'type' => Settings::TYPE_STRING,
			'default' => 'Search Locations',
			'label' => 'Title',
		),
	);
	
	
	function getWidgetContent($args, $instance) {
		return '<div class="cmloc-search-widget-content">' . SearchShortcode::shortcode(array()) . '</div>';
	}
	

}
