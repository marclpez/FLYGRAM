<?php

use com\cminds\maplocations\helper\GoogleMapsIcons;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\helper\FormHtml;

?>
<div class="cmloc-field cmloc-field-icon">
	<label><?php echo Labels::getLocalized('dashboard_icon'); ?>:</label>
	<input type="hidden" name="icon" value="<?php echo esc_attr($route->getIconUrl()); ?>" />
	<img src="<?php echo esc_attr($route->getIconUrl()); ?>" class="cmloc-current-icon" /><br />
	<a href="#" class="cmloc-choose-icon-btn"><?php echo Labels::getLocalized('dashboard_choose_icon_btn'); ?></a>
	<div class="cmloc-upload-wrapper">
		<input type="file" name="cmloc-upload-icon" class="cmloc-upload-icon-input" data-action="cmloc_upload_icon" data-nonce="<?php
			echo esc_attr(wp_create_nonce(RouteController::UPLOAD_ICON_NONCE)); ?>" data-url="<?php
			echo esc_attr(admin_url('admin-ajax.php')); ?>" />
		<a href="#" class="cmloc-upload-icon-btn"><?php echo Labels::getLocalized('dashboard_upload_icon_btn'); ?></a>
	</div>
	<label class="cmloc-icon-size"><?php echo Labels::getLocalized('icon_size'); ?>: <?php echo FormHtml::selectBox('icon_size', $iconSizes, $route->getIconSize()); ?></label>
	<div class="cmloc-icons-list"><?php foreach (GoogleMapsIcons::getAll() as $icon):
		printf('<img src="%s" />', esc_attr($icon));
	endforeach; ?></div>
</div>