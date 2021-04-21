<?php

use com\cminds\maplocations\model\Labels;


?><div class="cmloc-route-embed">
	<p><?php echo Labels::getLocalized('embed_copy_html'); ?></p>
	<textarea readonly><?php echo esc_html($iframe); ?></textarea>
	<button class="cmloc-route-embed-copy-btn"><?php echo Labels::getLocalized('embed_copy_to_clipboard'); ?></button>
</div>