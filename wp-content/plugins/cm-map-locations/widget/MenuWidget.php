<?php

namespace com\cminds\maplocations\widget;

use com\cminds\maplocations\controller\Controller;
use com\cminds\maplocations\controller\FrontendController;

use com\cminds\maplocations\App;
use com\cminds\maplocations\model\SettingsAbstract;

class MenuWidget extends Widget {

	const WIDGET_NAME = 'CM Map Locations Menu';
	const WIDGET_DESCRIPTION = 'Displays CM Map Locations menu.';
	
	
	function getWidgetContent($args, $instance) {
		$route = FrontendController::getRoute();
		return Controller::loadView('frontend/widget/menu', compact('instance', 'route'));
	}
	
	
	function canDisplay($args, $instance) {
		return FrontendController::isThePage();
	}
	

}
