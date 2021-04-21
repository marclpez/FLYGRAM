<?php

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\helper\FormHtml;

?>
<div class="cmbd-single-filter">
	<label class="cmloc-zip-filter-code"><span><?php echo Labels::getLocalized('filter_zip_code'); ?></span><input type="text" name="zipcode" value="<?php echo esc_attr($zipcodeValue); ?>" /></label>
</div>
<div class="cmbd-single-filter">
	<label class="cmloc-zip-filter-radius"><span><?php echo Labels::getLocalized('filter_zip_radius'); ?></span><?php echo FormHtml::selectBox('zipradius', $radiusOptions, $radiusValue); ?></label>
</div>