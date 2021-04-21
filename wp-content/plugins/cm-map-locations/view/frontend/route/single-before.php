<?php

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\controller\FrontendController;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\helper\RouteView;

?><div class="cmloc-location-map-before">

	<?php do_action('cmloc_route_map_before_top', $route); ?>

	<ul class="cmloc-inline-nav">
		<?php $created = $route->formatCreatedDate(); ?>
		<li class="cmloc-created-date"><strong><?php echo Labels::getLocalized('location_created'); ?>:</strong> <span><?php echo $created; ?></span></li>
		<?php if ($updated = $route->formatModifiedDate() AND $updated != $created): ?>
			<li class="cmloc-created-date"><strong><?php echo Labels::getLocalized('location_updated'); ?>:</strong> <span><?php echo $updated; ?></span></li>
		<?php endif; ?>
	</ul>
	
	<?php if ($categories = $route->getCategories()) RouteView::displayTermsInlineNav(Labels::getLocalized('categories'), 'categories', $categories); ?>
	<?php if ($tags = $route->getTags()) RouteView::displayTermsInlineNav(Labels::getLocalized('tags'), 'tags', $tags); ?>
	

</div>