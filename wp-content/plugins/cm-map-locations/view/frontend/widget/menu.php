<?php

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\controller\DashboardController;

use com\cminds\maplocations\controller\FrontendController;

?>
<div class="cmloc-widget-menu">
	<ul>
		<li><a href="<?php echo esc_attr(FrontendController::getUrl()); ?>"><?php echo Labels::getLocalized('menu_all_locations'); ?></a></li>
		<?php if (Route::canCreate()): ?>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('index')); ?>"><?php echo Labels::getLocalized('menu_my_locations'); ?></a></li>
			<li><a href="<?php echo esc_attr(RouteController::getDashboardUrl('add')); ?>"><?php echo Labels::getLocalized('menu_add_location'); ?></a></li>
			<?php if (!empty($route) AND $route->canEdit()): ?>
				<?php if (FrontendController::isDashboard() AND 'publish' == $route->getStatus()): ?>
					<li><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('menu_view_location'); ?></a></li>
				<?php else: ?>
					<li><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('menu_edit_location'); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
</div>