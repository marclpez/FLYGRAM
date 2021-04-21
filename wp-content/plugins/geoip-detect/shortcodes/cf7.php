<?php



add_action( 'wpcf7_init', 'geoip_detect2_add_wpcf7_shortcodes' );
function geoip_detect2_add_wpcf7_shortcodes() {
	if (function_exists('wpcf7_add_form_tag')) {
		// >=CF 4.6
		wpcf7_add_form_tag(array('geoip_detect2_countries', 'geoip_detect2_countries*'), 'geoip_detect2_shortcode_country_select_wpcf7', true);
		wpcf7_add_form_tag(array('geoip_detect2_text_input', 'geoip_detect2_text_input*'), 'geoip_detect2_shortcode_text_input_wpcf7', true);
	} else if (function_exists('wpcf7_add_shortcode')) {
		// < CF 4.6
		wpcf7_add_shortcode(array('geoip_detect2_countries', 'geoip_detect2_countries*'), 'geoip_detect2_shortcode_country_select_wpcf7', true);
		wpcf7_add_shortcode(array('geoip_detect2_text_input', 'geoip_detect2_text_input*'), 'geoip_detect2_shortcode_text_input_wpcf7', true);
	}
}


/**
 * Generating a country select field that has the geoip value as default
 * 
 * Examples:
 *
 * `[geoip_detect2_countries mycountry id:id class:class lang:fr]`
 * A list of all country names in French (with CSS id "#id" and class ".class"), the visitor's country is preselected.
 *
 * `[geoip_detect2_countries mycountry include_blank]`
 * Country names are in the current site language. User can also choose '---' for no country at all.
 *
 * `[geoip_detect2_countries mycountry flag tel]`
 * Country names have a UTF-8 flag in front of the country name, and the (+1) internation phone code after it
 * 
 * `[geoip_detect2_countries mycountry "US"]`
 * "United States" is preselected, there is no visitor IP detection going on here
 *
 * `[geoip_detect2_countries mycountry default:US]`
 * Visitor's country is preselected, but in case the country is unknown, use "United States"
 *
 */
function geoip_detect2_shortcode_country_select_wpcf7($tag) {
	$tag = new WPCF7_FormTag( $tag );

	$default = (string) reset( $tag->values );
	$default = $tag->get_default_option($default, array('multiple' => false));
	$default = wpcf7_get_hangover( $tag->name, $default ); // Get from $_POST if available

	$class = wpcf7_form_controls_class( $tag->type );
	$validation_error = wpcf7_get_validation_error( $tag->name );
	if ($validation_error)
		$class .= ' wpcf7-not-valid';

	$attr = array(
		'name' => $tag->name,
		'include_blank' => $tag->has_option( 'include_blank' ),
		'required' => substr($tag->type, -1) == '*',
		'invalid' => $validation_error ? 'true' : 'false',
		'id' => $tag->get_id_option(),
		'class' => $tag->get_class_option( $class ),
		'lang' => $tag->get_option('lang', '', true),
		'selected' => $default,
		'default' => $tag->get_option('default', '', true),
		'flag' => $tag->has_option('flag'),
		'tel' => $tag->has_option('tel'),
	);
	
	$html = geoip_detect2_shortcode_country_select($attr);

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s">%2$s %3$s</span>',
		sanitize_html_class( $tag->name ), $html, $validation_error );

	return $html;
}


/**
 * Generating a text field that has a geoip value as default
 * 
 * Property can be: continent, country, city, postal.code or any other property understood by `geoip_detect2_get_info_from_ip`
 * 
 * Examples:
 *
 * `[geoip_detect2_text_input city property:city lang:fr id:id class:class]`
 * A text input that has the detetected city as default (with CSS id "#id" and class ".class")
 *
 * `[geoip_detect2_text_input city property:city lang:fr id:id class:class default:Paris]`
 * As above, but in case the city is unknown, use "Paris"
 * 
 * `[geoip_detect2_text_input postal property:postal.code type:hidden]`
 * An invisible text input containing the postal code. 
 *
 */
function geoip_detect2_shortcode_text_input_wpcf7($tag) {
	$tag = new WPCF7_FormTag( $tag );

	$default = (string) reset( $tag->values );
	$default = $tag->get_default_option($default, array('multiple' => false));
	$default = wpcf7_get_hangover( $tag->name, $default ); // Get from $_POST if available

	$class = wpcf7_form_controls_class( $tag->type );
	$validation_error = wpcf7_get_validation_error( $tag->name );
	if ($validation_error)
		$class .= ' wpcf7-not-valid';

	$attr = array(
		'name' => $tag->name,
		'required' => substr($tag->type, -1) == '*',
		'invalid' => $validation_error ? 'true' : 'false',
		'id' => $tag->get_id_option(),
		'class' => $tag->get_class_option( $class ),
		'type' => $tag->get_option('type', '', true),
		'lang' => $tag->get_option('lang', '', true),
		'property' => $tag->get_option('property', '', true),
		'default' => $tag->get_option('default', '', true),
	);
	$html = geoip_detect2_shortcode_text_input($attr);

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s">%2$s %3$s</span>',
		sanitize_html_class( $tag->name ), $html, $validation_error );

	return $html;
}

function geoip_detect2_shortcode_user_info_wpcf7($output, $name, $isHtml) {
	$lines = array();

	switch($name) {
		case 'geoip_detect2_get_client_ip':
			$lines[] = geoip_detect2_get_client_ip();
			break;
		case 'geoip_detect2_get_current_source_description':
			$lines[] = geoip_detect2_get_current_source_description();
			break;
		case 'geoip_detect2_property_country':
			$info = geoip_detect2_get_info_from_current_ip();
			$lines[] = $info->country->name;
			break;
		case 'geoip_detect2_property_most_specific_subdivision':
		case 'geoip_detect2_property_state':
		case 'geoip_detect2_property_region':
			$name = 'geoip_detect2_property_most_specific_subdivision';
			$info = geoip_detect2_get_info_from_current_ip();
			$lines[] = $info->mostSpecificSubdivision->name;
			break;
		case 'geoip_detect2_property_city':
			$info = geoip_detect2_get_info_from_current_ip();
			$lines[] = $info->city->name;
			break;

		case 'geoip_detect2_user_info':
			$lines[] = sprintf(__('IP of the user: %s', 'geoip-detect'), geoip_detect2_get_client_ip());

			$info = geoip_detect2_get_info_from_current_ip();
			if ($info->country->name)
				$lines[] = sprintf(__('Country: %s', 'geoip-detect'), $info->country->name);
			if ($info->mostSpecificSubdivision->name)
				$lines[] = sprintf(__('State or region: %s', 'geoip-detect'), $info->mostSpecificSubdivision->name);
			if ($info->city->name)
				$lines[] = sprintf(__('City: %s', 'geoip-detect'), $info->city->name);

			$lines[] = '';
			$lines[] = sprintf(__('Data from: %s', 'geoip-detect'), geoip_detect2_get_current_source_description());
			break;
			
		default:
			return $output;
	}

	/**
	 * Filter: geoip2_detect_wpcf7_special_mail_tags
	 * This filter is called if a GeoIP-detection-tag was used.
	 *
	 * @param array $lines - Output lines
	 * @param string $name - Name of the WPCF 7 Tag that was used
	 * @param bool $isHtml - Whether HTML or Plain Text output should be used
	 * @return array Output lines
	 */
	$lines = apply_filters('geoip2_detect_wpcf7_special_mail_tags', $lines, $name, $isHtml);

    $lineBreak = $isHtml ? "<br>" : "\n";
    return implode($lineBreak, $lines);
}
add_filter( 'wpcf7_special_mail_tags', 'geoip_detect2_shortcode_user_info_wpcf7', 18, 3 );
