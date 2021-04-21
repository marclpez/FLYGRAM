<?php

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Attachment;

/* @var $route Route */

?><div class="cmloc-location-snippet" data-route-id="<?php echo $route->getId(); ?>">
	<?php if (LocationSnippetShortcode::FEATURED_NONE != $atts['featured']): ?>
		<div class="cmloc-location-featured-image"><?php echo RouteView::getFeaturedImage($route, $atts); ?></div>
	<?php endif; ?>
	<div class="cmloc-created-date"><?php echo Date('Y-m-d', strtotime($route->getCreatedDate())); ?></div>
	<h2><a href="<?php echo esc_attr($route->getPermalink()); ?>" class="cmloc-location-link"><?php echo esc_html($route->getTitle()); ?></a></h2>
	<div class="cmloc-address">
		<span><?php echo esc_html($route->getAddress()); ?></span>
	</div>
	<div class="cmloc-postal-code">
			<span><?php echo esc_html($route->getPostalCode()); ?></span>
		</div>
	<?php if ($categories = $route->getCategories()) RouteView::displayTermsInlineNav(Labels::getLocalized('categories'), 'categories', $categories); ?>
	<?php do_action('cmloc_route_snippet_bottom', $route); ?>
	<div class="clear"></div>
</div>