<?php


use com\cminds\maplocations\model\Settings;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

?><div class="cmloc-locations-archive cmloc-layout-<?php echo $layout;
	?>"<?php echo RouteView::getDisplayParams($displayParams); ?>>
	
	<?php get_template_part('cmloc', 'route-index-filter'); ?>
	<?php get_template_part('cmloc', 'route-index-map'); ?>
	<?php get_template_part('cmloc', 'route-index-list'); ?>
	
</div>