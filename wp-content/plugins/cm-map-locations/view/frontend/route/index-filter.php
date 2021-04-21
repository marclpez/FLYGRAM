<?php

use com\cminds\maplocations\model\Labels;


?><div class="cmloc-location-index-filter cmloc-filter">

	<form action="<?php echo esc_attr($atts['searchformurl']); ?>" class="cmloc-location-filter-form">
	
		<?php do_action('cmloc_map_filter_before', $atts); ?>
	
		<label class="cmloc-field-search">
			<input type="text" name="s" value="<?php echo esc_attr($atts['searchstring']);
				?>" placeholder="<?php echo Labels::getLocalized('search_placeholder'); ?>" class="cmloc-input-search" />
			<button type="submit" class="cmloc-submit-btn" title="<?php echo esc_attr(Labels::getLocalized('search_btn')); ?>"><span class="dashicons dashicons-search"></span></button>
		</label>
		
		<?php do_action('cmloc_map_filter_after', $atts); ?>
	
	</form>
	
</div>