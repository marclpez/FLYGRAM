<?php

use com\cminds\maplocations\App;
use com\cminds\maplocations\controller;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\model\Attachment;
use com\cminds\maplocations\model\Route;
use com\cminds\maplocations\helper\FormHtml;


/* @var $route Route */

?>
<div class="cmloc-location-editor">

    <?php do_action('cmloc_route_editor_top', $route); ?>

	<form action="<?php echo esc_attr($formUrl); ?>" method="post" enctype="multipart/form-data" data-route-id="<?php echo $route->getId(); ?>">

		<div class="cmloc-field">
			<label><?php echo Labels::getLocalized('location_name'); ?>:</label>
			<input type="text" name="name" value="<?php echo esc_attr($route->getTitle()); ?>" required />
		</div>
		
		<div class="cmloc-field cmloc-field-description">
			<label><?php echo Labels::getLocalized('location_description'); ?>:</label>
			<?php if (Settings::getOption(Settings::OPTION_EDITOR_RICH_TEXT_ENABLE)): ?>
				<?php wp_editor($route->getContent(), 'cmloc_location_description', array('textarea_name' => 'description')); ?>
			<?php else :?>
				<textarea name="description"><?php echo esc_html($route->getContent()); ?></textarea>
			<?php endif; ?>
		</div>
		
		<div class="cmloc-field">
			<label><?php echo Labels::getLocalized('location_status'); ?>:</label>
			<?php echo FormHtml::selectBox('status', apply_filters('cmloc_editor_allowed_statuses', array(
				'publish' => Labels::getLocalized('location_status_publish'),
				'draft' => Labels::getLocalized('location_status_draft'),
			),  $route), $route->getStatus()); ?>
		</div>
		
		<?php do_action('cmloc_route_editor_middle', $route); ?>
		
		<div class="cmloc-field cmloc-images">
			<label><?php echo Labels::getLocalized('location_images'); ?>:</label>
			<ul class="cmloc-images-list"><?php
			
			$template = '<li data-id="%s"%s><a href="%s" target="_blank" title="%s"><img src="%s" alt="Image" /></a>'
				. '<span class="cmloc-image-delete" title="%s">&times;</span></li>';
			printf($template, 0, ' style="display:none"', 'about:blank', esc_attr(Labels::getLocalized('dashboard_image_open')),
				'about:blank', esc_attr(Labels::getLocalized('dashboard_image_remove')));
			
			$imagesIds = array();
			foreach ($route->getImages() as $image):
				$imagesIds[] = $image->getId();
				printf($template,
					$image->getId(),
					'',
					esc_attr($image->isImage() ? $image->getImageUrl(Attachment::IMAGE_SIZE_FULL) : $image->getUrl()),
					esc_attr(Labels::getLocalized('dashboard_image_open')),
					esc_attr($image->getImageUrl(Attachment::IMAGE_SIZE_THUMB)),
					esc_attr(Labels::getLocalized('dashboard_image_remove'))
				);
			endforeach; ?></ul>
			<div class="cmloc-field-desc"<?php if (empty($imagesIds)) echo ' style="display:none;"'; ?>><?php echo Labels::getLocalized('dashboard_images_description'); ?></div>
			<div class="cmloc-images-add">
				<input type="hidden" name="images" value="<?php echo esc_attr(implode(',', $imagesIds)); ?>" />
				<span><?php echo Labels::getLocalized('dashboard_upload_image'); ?></span>
				<input type="file" class="cmloc-images-upload" accept="image/*" multiple>
				<div class="cmloc-btn cmloc-add-video-btn"><?php echo Labels::getLocalized('dashboard_video_add_btn'); ?></div>
			</div>
		</div>
		
		<div id="cmloc-editor-map">
			
			<ul class="cmloc-inline-nav cmloc-toolbar">
				<li><input type="text" class="cmloc-find-location" placeholder="<?php echo esc_attr(Labels::getLocalized('dashboard_map_search')); ?>" /></li>
			</ul>
			
			<div id="cmloc-editor-map-canvas"></div>
			
						
		</div>
		
		<div id="cmloc-editor-locations">
			<ul class="cmloc-locations-list">
				<li data-id="0" style="display:none">
					<input class="location-id" type="hidden" name="locations[id][]" value="0" />
					<input class="location-name" type="hidden" name="locations[name][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_name')); ?>" />
					
					<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_latitude'); ?>:</label><input class="location-lat" type="text" name="locations[lat][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_latitude')); ?>" /></div>
					<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_longitude'); ?>:</label><input class="location-long" type="text" name="locations[long][]" value="" placeholder="<?php echo esc_attr(Labels::getLocalized('location_longitude')); ?>" /></div>
					<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_address'); ?>:</label><input class="location-address" type="text" name="locations[address][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_address')); ?>" /></div>
					<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_postal_code'); ?>:</label><input class="location-postal-code" type="text" name="locations[postal-code][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_postal_code')); ?>" /></div>
					<?php if (App::isPro()): ?>
						<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_phone_number'); ?>:</label><input class="location-phone-number" type="text" name="locations[phone-number][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_phone_number')); ?>" /></div>
						<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_website'); ?>:</label><input class="location-website" type="text" name="locations[website][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_website')); ?>" /></div>
						<div class="cmloc-field"><label><?php echo Labels::getLocalized('location_email'); ?>:</label><input class="location-email" type="text" name="locations[email][]" value="" title="<?php echo esc_attr(Labels::getLocalized('location_email')); ?>" /></div>
					<?php endif; ?>
					<input class="location-type" type="hidden" name="locations[type][]" value="location" />
					<div style="display:none"><textarea class="location-description" name="locations[description][]" placeholder="<?php echo esc_attr(Labels::getLocalized('location_description')); ?>"></textarea></div>
					<?php /*
					<div class="cmloc-images">
						<input type="hidden" name="locations[images][]" value="" />
						<ul class="cmloc-images-list">
							<li data-id="0" style="display:none"><a href="about:blank" target="_blank" title="<?php
								echo esc_attr(Labels::getLocalized('dashboard_image_open')); ?>"><img src="about:blank" alt="Image" /></a>
							<span class="cmloc-image-delete" title="<?php
								echo esc_attr(Labels::getLocalized('dashboard_image_remove')); ?>">&times;</span></li>
						</ul>
						<a href="#" class="button cmloc-images-add-btn"><?php
								echo Labels::getLocalized('dashboard_image_add'); ?></a>
					</div> */ ?>
				</li>
			</ul>
			<script type="text/javascript">
			jQuery(function($) {
				new CMLOC_Editor(<?php echo json_encode($route->getJSLocations()); ?>);
				$('.cmloc-images').each(CMLOC_Editor_Images_init);
			});
			</script>
		</div>

		<?php do_action('cmloc_editor_before_form_summary', $route); ?>
		
		<div class="form-summary">
			<input type="hidden" name="<?php echo esc_attr(controller\DashboardController::EDITOR_NONCE); ?>" value="<?php echo esc_attr($nonce); ?>" class="cmloc-nonce" />
			<input type="submit" name="btn_save" value="<?php echo esc_attr(Labels::getLocalized('dashboard_save_btn')); ?>" class="button button-primary" />
		</div>

	</form>

    <?php if( $route->getId() && current_user_can('manage_options') ) {
        if( Settings::getOption(Settings::OPTION_SHOW_SHORTCODES_ROUTE_EDIT_FRONTEND) ) { ?>
            <div class="cmloc-field">
                <label><?php echo Labels::getLocalized('shortcodes'); ?>:</label>
                <?php echo controller\RouteController::loadBackendView('metabox-shortcodes', array('id' => $route->getId())); ?>
            </div>
            <?php
        }
    } ?>

</div>