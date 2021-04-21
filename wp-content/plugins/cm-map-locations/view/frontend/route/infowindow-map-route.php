<?php

use com\cminds\maplocations\model\Attachment;

use com\cminds\maplocations\App;

use com\cminds\maplocations\model\Labels;

/* @var $route MapRoute */

?><div class="cmloc-infowindow cmloc-infowindow-maps-routes">
	<?php if ($imageUrl = $route->getFeaturedImageUrl(Attachment::IMAGE_SIZE_THUMB)): ?>
		<a href="<?php echo esc_attr($route->getPermalink()); ?>"><img src="<?php
			echo esc_attr($imageUrl); ?>" class="cmloc-infowindow-image" /></a>
	<?php endif; ?>
	<h2><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo $route->getTitle(); ?></a></h2>
	<div class="cmloc-infowindow-desc">
		<?php echo $route->getContent(); ?>
	</div>
	<div class="cmloc-infowindow-more"><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('More &raquo;'); ?></a></div>
</div>