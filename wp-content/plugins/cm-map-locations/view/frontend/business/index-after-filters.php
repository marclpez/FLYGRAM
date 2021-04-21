<?php

use com\cminds\maplocations\model\Labels;

?>

<div class="cmloc-bd-index-map-wrapper">
	<a href="#" class="cmloc-show-map-btn cmloc-btn"><?php echo Labels::getLocalized('bd_show_map'); ?></a>
	<a href="#" class="cmloc-hide-map-btn cmloc-btn" style="display:none"><?php echo Labels::getLocalized('bd_hide_map'); ?></a>
	<?php echo $shortcode; ?>
</div>