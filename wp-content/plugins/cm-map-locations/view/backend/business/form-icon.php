<?php 

use com\cminds\maplocations\helper\GoogleMapsIcons;

?><?php if (!empty($term)) $template = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cmloc_category_icon">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template = <<<HTML
	<div class="form-field">
		<label for="cmloc_category_icon">%s</label>
		%s
	</div>
HTML;


$options = '';
foreach ($icons as $icon) {
	$options .= sprintf('<img src="%s">', esc_attr($icon));
}


if (!empty($currentIcon)) {
	$current = '<img src="'. esc_attr($currentIcon) .'" class="cmloc_category_icon_image" />
		<input type="text" name="cmloc_category_icon" value="'. esc_attr($currentIcon) .'"  placeholder="Icon\'s URL address" />';
} else {
	$current = '<img class="cmloc_category_icon_image" /><input type="text" name="cmloc_category_icon" value=""  placeholder="Icon\'s URL address" />';
}


$content = <<<HTML
	<div class="cmloc_category_icon">
		<p><input type="button" value="Choose icon" class="cmloc_category_icon_choose" /></p>
		<div class="cmloc_category_icon_list" style="display:none">%s</div>
		<input type="hidden" name="%s" value="%s" />
		<p>%s</p>
	</div>
HTML;

$content = sprintf($content, $options, $nonceField, $nonce, $current);

printf($template, 'Default marker icon for new businesses', $content);
