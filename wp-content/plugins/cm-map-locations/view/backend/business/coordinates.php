<?php
use \com\cminds\maplocations\model\Business;
?>
<p>
	<label for="<?php echo Business::META_BD_MANUAL_MARKER ?>" class="cmbd_metabox_label"><?php _e( 'Place the marker manually', CMBD_SLUG_NAME ); ?>
	<span class="cmbd_field_help" title="If not checked then latitude and longitude will be generated automatically from address"> </span></label>
	<input type="hidden" name="<?php echo Business::META_BD_MANUAL_MARKER ?>" value="0" />

	<input type="checkbox" name="<?php echo Business::META_BD_MANUAL_MARKER ?>"
           id="<?php echo Business::META_BD_MANUAL_MARKER ?>"
           class="cm_checkbox" value="1" <?php checked( $manual_marker, '1' ); ?> />
</p>
<div class="clear"></div>

<p>
    <label class="cmbd_metabox_label" for="<?php echo Business::META_LAT ?>"><?php _e( 'Latitude', CMBD_SLUG_NAME ); ?></label>
    <input type="text" name="<?php echo Business::META_LAT ?>" id="<?php echo Business::META_LAT ?>"
           value="<?php esc_attr_e( $lat ) ?>" class="large-text cm_input">
</p>
<div class="clear"></div>

<p>
    <label class="cmbd_metabox_label" for="<?php echo Business::META_LONG ?>"><?php _e( 'Longitude', CMBD_SLUG_NAME ); ?></label>
    <input type="text" name="<?php echo Business::META_LONG ?>" id="<?php echo Business::META_LONG ?>"
           value="<?php esc_attr_e( $lng ) ?>" class="large-text cm_input">
</p>
<div class="clear"></div>