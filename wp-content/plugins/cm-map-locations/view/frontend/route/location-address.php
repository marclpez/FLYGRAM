<?php

use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Location;
use com\cminds\maplocations\model\Labels;

/**
 * @var Location $location
 */


?>
<?php if ($address = $location->getAddress()): ?>
	<div class="cmloc-address">
		<strong><?php echo Labels::getLocalized('location_address'); ?>:</strong>
		<span><?php echo esc_html($address); ?></span>
	</div>
<?php endif; ?>
<?php if ($postalCode = $location->getPostalCode()): ?>
	<div class="cmloc-postal-code">
		<strong><?php echo Labels::getLocalized('location_postal_code'); ?>:</strong>
		<span><?php echo esc_html($postalCode); ?></span>
	</div>
<?php endif; ?>
<?php if (App::isPro()): ?>
	<?php if ($phone = $location->getPhoneNumber()): ?>
		<div class="cmloc-route-phone">
			<strong><?php echo Labels::getLocalized('location_phone_number'); ?>:</strong>
			<span><a href="tel:<?php echo esc_attr($phone); ?>"><?php echo $phone; ?></a></span>
		</div>
	<?php endif; ?>
	<?php if ($website = $location->getWebsite()): ?>
		<div class="cmloc-route-website">
			<strong><?php echo Labels::getLocalized('location_website'); ?>:</strong>
			<span><a href="<?php echo esc_attr($website); ?>"><?php echo $website; ?></a></span>
		</div>
	<?php endif; ?>
	<?php if ($email = $location->getEmail()): ?>
		<div class="cmloc-route-email">
			<strong><?php echo Labels::getLocalized('location_email'); ?>:</strong>
			<span><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo $email; ?></a></span>
		</div>
	<?php endif; ?>
<?php endif; ?>