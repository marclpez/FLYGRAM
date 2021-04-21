<?php

use com\cminds\maplocations\App;

use com\cminds\maplocations\model\Labels;

/* @var $route Route */

?><div class="cmloc-infowindow">
	<?php echo $snippet; ?>
	<?php if (strlen($description) > 0): ?>
		<div class="cmloc-infowindow-desc"><?php echo $description; ?></div>
	<?php endif; ?>
	<?php if (App::isPro()): ?>
		<?php if ($phone = $route->getLocation()->getPhoneNumber()): ?>
			<div class="cmloc-infowindow-phone"><?php echo Labels::getLocalized('location_phone_number'); ?>: <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo $phone; ?></a></div>
		<?php endif; ?>
		<?php if ($website = $route->getLocation()->getWebsite()): ?>
			<div class="cmloc-infowindow-website"><?php echo Labels::getLocalized('location_website'); ?>: <a href="<?php echo esc_attr($website); ?>"><?php echo $website; ?></a></div>
		<?php endif; ?>
		<?php if ($email = $route->getLocation()->getEmail()): ?>
			<div class="cmloc-infowindow-email"><?php echo Labels::getLocalized('location_email'); ?>: <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo $email; ?></a></div>
		<?php endif; ?>
	<?php endif; ?>
	<div class="cmloc-infowindow-more"><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('More &raquo;'); ?></a></div>
</div>