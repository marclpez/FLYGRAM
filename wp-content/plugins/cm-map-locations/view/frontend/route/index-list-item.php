<?php

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Settings;

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

use com\cminds\maplocations\model\Labels;

echo LocationSnippetShortcode::shortcode(array(
	'route' => $route,
	'featured' => Settings::getOption(Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE),
));
