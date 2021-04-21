<?php

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Settings;

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

use com\cminds\maplocations\model\Labels;

?><div class="cmloc-locations-archive-list-wrapper">
	<div class="cmloc-locations-archive-summary"><?php printf(Labels::getLocalized('locations_index_summary'), count($routes), $totalRoutesNumber); ?></div>
	<div class="cmloc-locations-archive-list">
		<?php foreach ($routes as $route):
			echo RouteController::loadFrontendView('index-list-item', compact('route'));
		endforeach; ?>
		<?php if (empty($routes)): ?>
			<p><?php echo Labels::getLocalized('index_no_locations'); ?></p>
		<?php endif; ?>
	</div>
	<?php get_template_part('cmloc', 'route-index-bottom'); ?>
	<?php do_action('cmloc_route_index_pagination', $query); ?>
</div>