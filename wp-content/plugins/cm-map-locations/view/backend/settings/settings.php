<?php

use com\cminds\maplocations\controller\DashboardController;

use com\cminds\maplocations\controller\FrontendController;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\controller\SettingsController;
use com\cminds\maplocations\view\SettingsView;
use com\cminds\maplocations\App;
use com\cminds\maplocations\model\Settings;


if (!empty($_GET['status']) AND !empty($_GET['msg'])) {
	printf('<div id="message" class="%s"><p>%s</p></div>', ($_GET['status'] == 'ok' ? 'updated' : 'error'), esc_html($_GET['msg']));
}

$settingsView = new SettingsView();

?>

<div class="cmloc-help-shortcodes">
	<p><strong>Index page:</strong> <a href="<?php echo esc_attr(FrontendController::getUrl()); ?>" target="_blank"><?php echo FrontendController::getUrl(); ?></a></p>
	<p><strong>User dashboard:</strong> <a href="<?php echo esc_attr(RouteController::getDashboardUrl()); ?>" target="_blank"><?php echo RouteController::getDashboardUrl(); ?></a></p>
</div>

<form method="post" id="settings">

<ul class="cmloc-settings-tabs"><?php

$tabs = apply_filters('cmloc_settings_pages', Settings::$categories);
foreach ($tabs as $tabId => $tabLabel) {
	printf('<li><a href="#tab-%s">%s</a></li>', $tabId, $tabLabel);
}

?></ul>

<div class="inner"><?php

echo $settingsView->render();

?></div>

<p class="form-finalize">
	<a href="<?php echo esc_attr($clearCacheUrl); ?>" class="right button">Clear cache</a>
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(SettingsController::getMenuSlug()); ?>" />
	<input type="submit" value="Save" class="button button-primary" />
</p>

</form>
