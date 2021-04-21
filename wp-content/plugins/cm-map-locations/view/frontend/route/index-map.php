<?php

use com\cminds\maplocations\model\Labels;

$printScript = function() use ($routes) {
	?><script type="text/javascript">
	jQuery(function($) {
		if (typeof window.cmlocMaps == 'undefined') window.cmlocMaps = [];
		if (document.getElementById('cmloc-location-index-map-canvas')) {
			window.cmlocMaps.push(new CMLOC_Index_Map('cmloc-location-index-map-canvas', <?php echo json_encode($routes); ?>));
		}
	});
	</script><?php
};


?><div class="cmloc-location-index-map" style="<?php
		if (!empty($atts['mapwidth'])) echo 'width:' . $atts['mapwidth'] .'px;';
		if (!empty($atts['mapheight'])) echo 'height:' . $atts['mapheight'] .'px;';
	?>">

	<div id="cmloc-location-index-map-canvas" style="<?php
		if (!empty($atts['mapwidth'])) echo 'width:' . $atts['mapwidth'] .'px;';
		if (!empty($atts['mapheight'])) echo 'height:' . $atts['mapheight'] .'px;';
	?>"></div>
	
	<?php
	
	if (defined('DOING_AJAX') && DOING_AJAX) {
		$printScript();
	} else {
		add_action('wp_footer', $printScript, PHP_INT_MAX);
	} ?>
	
</div>