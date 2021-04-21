<?php

use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Location;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\helper\RouteView;

/* @var $route Route */

$i = 0;

?><div class="cmloc-route-locations">
	<?php foreach ($route->getLocations() as $location): ?>
		<?php /* @var $location Location */ ?>
		<?php if (Location::TYPE_LOCATION == $location->getLocationType()): ?>
			<?php $i++; ?>
			<div class="cmloc-location-details" data-id="<?php echo $location->getId();
				?>" data-lat="<?php echo $location->getLat(); ?>"  data-long="<?php echo $location->getLong(); ?>">
				<?php do_action('cmloc_single_location_address', $location); ?>
				<?php do_action('cmloc_single_location_before_images', $location); ?>
				<?php if ($images = $location->getImages()):
					RouteView::displayImages($images, 'location', $location->getId());
				endif; ?>
				
				<div class="cmloc-description"><?php echo $location->getContent(); ?></div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>