<?php

// if (empty($atts['mapheight'])) $atts['mapheight'] = 600;

?>

<div class="cmloc-location-map-canvas-outer" style="display:<?php echo (!isset($atts['map']) OR $atts['map'] == 1) ? 'block' : 'none'; ?>">
	<div id="<?php echo $mapId; ?>" class="cmloc-location-map-canvas" style="<?php
		if (!empty($atts['mapwidth'])) echo 'width:' . $atts['mapwidth'] .'px;';
		if (!empty($atts['mapheight'])) echo 'height:' . $atts['mapheight'] .'px;';
	?>"></div>
</div>

<?php // add_action('wp_footer', function() use ($mapId, $route) { ?>
	<script type="text/javascript">
		jQuery(function($) {
			var mapId = <?php echo json_encode($mapId); ?>;
			var locations = <?php echo json_encode($route->getJSLocations()); ?>;
			var pathColor = null;
			document.getElementById(mapId).cmloc_route = new CMLOC_Route(mapId, locations, pathColor);
		});
	</script>
<?php // }); ?>