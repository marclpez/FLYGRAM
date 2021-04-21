<?php

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\helper\RouteView;

use com\cminds\maplocations\model\Attachment;

?><div class="cmloc-location-details" data-id="<?php echo $route->getId(); ?>">
	<?php if ($images = $route->getImages()):
		RouteView::displayImages($images, 'route', $route->getId());
	endif; ?>
	<div class="cmloc-description"><?php echo nl2br($route->getContent()); ?></div>
</div>