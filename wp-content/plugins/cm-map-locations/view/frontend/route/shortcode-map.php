<?php

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Attachment;

/* @var $route Route */

?><div class="cmloc-shortcode-cmloc-location-map cmloc-location-snippet cmloc-location-single"<?php echo RouteView::getDisplayParams($displayParams);
	?> style="<?php
		if (!empty($atts['width'])) echo 'width:' . $atts['width'] .'px;';
	?>">

	<?php if (!empty($atts['showdate'])): ?>
		<div class="cmloc-date"><?php echo Date('Y-m-d', strtotime($route->getCreatedDate())); ?></div>
	<?php endif; ?>
	
	<?php if (!empty($atts['showtitle'])): ?>
		<h2><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo esc_html($route->getTitle()); ?></a></h2>
	<?php endif; ?>
	
	<div class="cmloc-location-map"><?php
		echo RouteView::getFullMap($route, $atts);
	?></div>
	
	<div class="clear"></div>
	
</div>