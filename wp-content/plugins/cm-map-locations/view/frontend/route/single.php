<?php

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\model\TaxonomyTerm;
use com\cminds\maplocations\model\Attachment;

/* @var $route Route */

?>
<div class="cmloc-route cmloc-location-single" data-map-id="<?php echo $mapId; ?>" data-route-id="<?php echo $route->getId();
	?>"<?php echo RouteView::getDisplayParams($displayParams); ?>>
	<?php get_template_part('cmloc', 'route-single-before'); ?>
	<?php get_template_part('cmloc', 'route-single-locations'); ?>
	<?php get_template_part('cmloc', 'route-single-details'); ?>
	<?php get_template_part('cmloc', 'route-single-map'); ?>
	
</div>

<?php

// comments_template('', true);

