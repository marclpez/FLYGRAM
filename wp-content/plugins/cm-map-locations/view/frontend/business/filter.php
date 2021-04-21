<?php 

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\helper\RouteView;

?><div class="cmloc-business-filter cmloc-filter" data-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>" data-atts="<?php echo esc_attr(json_encode($atts)); ?>">
	<form action="">
		<?php if ($atts['searchbar']): ?>
			<label class="cmloc-field-search">
					<input type="text" name="s" value="<?php echo esc_attr($atts['s']);
						?>" placeholder="<?php echo Labels::getLocalized('search_placeholder'); ?>" class="cmloc-input-search" />
			</label>
			<button type="submit" title="<?php echo esc_attr(Labels::getLocalized('search_btn')); ?>"><span class="dashicons dashicons-search"></span></button>
		<?php endif; ?>
		<?php if ($atts['categoryfilter']): ?>
			<label class="cmloc-categories-filter">
				<select class="cmloc-business-filter-category">
					<option value="0">-- <?php echo Labels::getLocalized('filter_all_categories_opt'); ?> --</option>
					<?php echo RouteView::businessCategoriesFilter($currentCategoryId, $categories); ?>
				</select>
			</label>
		<?php endif; ?>
	</form>
</div>