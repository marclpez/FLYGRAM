<?php

use com\cminds\maplocations\App;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\model\Route;

/* @var $route Route */

?>

<ul class="cmloc-inline-nav cmloc-toolbar">
	<li><a href="<?php echo esc_attr(RouteView::getRefererUrl()); ?>" title="<?php echo esc_attr(Labels::getLocalized('location_backlink'));
		?>"><span class="dashicons dashicons-controls-back"></span></a></li>
	<?php do_action('cmloc_route_single_toolbar_middle', $route); ?>
	<li class="cmloc-width-auto"></li>
	<li class="cmloc-map-fullscreen-btn-item"><a class="cmloc-map-fullscreen-btn" href="#" title="<?php
		echo esc_attr(Labels::getLocalized('show_fullscreen_title')); ?>"><span class="dashicons dashicons-editor-expand"></span></a></li>
</ul>
<?php do_action('cmloc_route_toolbar_after', $route); ?>


<?php echo $mapCanvas; ?>