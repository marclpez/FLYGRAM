<?php

use com\cminds\maplocations\model\Route;

/**
 * @var Route $route
 */

?><div class="cmloc-route cmloc-route-single cmloc-shortcode-route-map" data-map-id="<?php echo $mapId;
	?>" data-route-id="<?php echo $id; ?>">
	
	<?php

	echo '<h2><a href="'. esc_attr($route->getPermalink()) .'" target="_blank">'. esc_attr($route->getTitle()) . '</a></h2>';
	echo $mapCanvas;
	
	?>
	
	<div class="cmloc-single-location-address">
		<?php do_action('cmloc_single_location_address', $route->getLocation()); ?>
	</div>

</div>