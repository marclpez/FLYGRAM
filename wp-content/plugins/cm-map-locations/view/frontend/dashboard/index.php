<?php


use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\controller\DashboardController;

?>

<?php if (Route::canCreate()): ?>
	<p class="cmloc-location-add"><a href="<?php echo RouteController::getDashboardUrl('add'); ?>"><?php echo Labels::getLocalized('menu_add_location'); ?></a></p>
<?php endif; ?>

<?php if (count($routes) > 0): ?>
	<table>
		<thead>
			<tr>
				<th><?php echo Labels::getLocalized('location_name'); ?></th>
				<th style="width:7em"><?php echo Labels::getLocalized('location_status'); ?></th>
				<th style="width:15em"><?php echo Labels::getLocalized('dashboard_locations_actions'); ?></th>
			</tr>
		</thead>
		<tbody><?php foreach ($routes as $route): ?>
			<tr>
				<td><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo esc_html($route->getTitle()); ?></a></td>
				<td><?php echo ('publish' == $route->getStatus() ? Labels::getLocalized('location_status_publish') : Labels::getLocalized('location_status_draft')); ?></td>
				<td>
					<ul class="cmloc-inline-nav">
						<li><a href="<?php echo esc_attr($route->getPermalink()); ?>"><?php echo Labels::getLocalized('dashboard_view'); ?></a></li>
						<li><a href="<?php echo esc_attr($route->getUserEditUrl()); ?>"><?php echo Labels::getLocalized('dashboard_edit'); ?></a></li>
						<li><a href="<?php echo esc_attr($route->getUserDeleteUrl()); ?>" class="cmloc-delete-confirm"><?php echo Labels::getLocalized('dashboard_delete'); ?></a></li>
					</ul>
				</td>
			</tr>
		<?php endforeach; ?></tbody>
		</table>
<?php else: ?>
	<p><?php echo Labels::getLocalized('dashboard_no_locations'); ?></p>
<?php endif; ?>