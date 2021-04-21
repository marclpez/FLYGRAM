<?php
function geoImgGPXAdminPage(){

	$options 	= get_option('geoimggpx_options');

	echo "<h1>" . __( 'geoImgGPX administrieren', 'gm' ). "</h1>";

	//https://codex.wordpress.org/Function_Reference/wp_nonce_field
	if (
			isset( $_POST['gmNonceField'] )
		    and
			wp_verify_nonce( $_POST['gmNonceField'], 'gmNonceSaveAdmin' )
				and
			isset($_POST['form_submit'])
			 and
			current_user_can('edit_plugins')
	) {
		if (isset($_POST['googleAPIKey'])){
			$options['googleAPIKey']   					= sanitize_text_field($_POST['googleAPIKey'])  					? trim(sanitize_text_field($_POST['googleAPIKey']))   					: '';
		}
		if (isset($_POST['geoImgGPX_ExifTool_path'])){
			$options['geoImgGPX_ExifTool_path'] = sanitize_text_field($_POST['geoImgGPX_ExifTool_path']) ? trim(sanitize_text_field($_POST['geoImgGPX_ExifTool_path']))  : '';
		}
		?><div class="updated fade"><p><?php _e('Settings Saved', 'gm') ?></p></div><?php

		update_option('geoimggpx_options', $options);
	}
	//---------------------------------------------------------------------------------------------
	// Test ob der Server EXIF Daten auslesen kann
	$geoImgGPXoptions 			= get_option('geoimggpx_options');
	$geoImgGPX_ExifTool_path 	= $geoImgGPXoptions['geoImgGPX_ExifTool_path'];
	//---------------------------------------------------------------------------------------------
	//	Kann der Server GPS Daten auslesen?
	$exif_read_data_func 	= false;
	$exiftool_func			= false;
	$testImgUrl 	= GEOIMAGEGPXURL."/images/GeoImage/content_example_ibiza.jpg";
	$testImgPath 	= GEOIMAGEGPXPATH."/images/GeoImage/content_example_ibiza.jpg";

	$time_startG = microtime(true);
	$geoImgGPX_exif_data = exif_read_data( $testImgUrl, 'ANY_TAG', true );
	$time_endG = microtime(true);
	$exif_read_data_time = $time_endG - $time_startG;
	if( GPSLatitudeDezi($geoImgGPX_exif_data) != 0 AND GPSLongitudeDezi($geoImgGPX_exif_data) != 0){
		$exif_read_data_func = true;
	}

	$GPSDataExifTool = "";
	if($geoImgGPX_ExifTool_path !== NULL && strlen($geoImgGPX_ExifTool_path) > 0){
		$time_startG = microtime();
		//https://stackoverflow.com/questions/10085284/how-to-handle-parse-error-for-eval-function-in-php
		try {
		  @eval('$GPSDataExifTool='.`$geoImgGPX_ExifTool_path -php -q $testImgPath`);
		} catch (ParseError $e) {
			$exifToolCaughtException = $e->getMessage();
			$oldExifPath	= $options['geoImgGPX_ExifTool_path'];
		  $options['geoImgGPX_ExifTool_path'] = "";
			update_option('geoimggpx_options', $options);
		}
		$time_endG = microtime();
		$exiftool_time = floatval($time_endG) - floatval($time_startG);

		if(is_array($GPSDataExifTool)){
			if( GPSLatitudeDeziExifTool($GPSDataExifTool) != 0 AND GPSLongitudeDeziExifTool($GPSDataExifTool) != 0){
				$exiftool_func			= true;
			}
		}
	}

	if( $exif_read_data_func == false and  $exiftool_func == false){
		echo __("Ihr Server unterstütz keine EXIF-Applikationen zum auslesen von GeoDaten. exif_read_data() ist deaktiviert und ExifTool ist nicht vorhanden oder der Pfad zu diesem stimmt nicht.", "gm");
	}else{
		echo "<span>".__("Ihr Server unterstütz EXIF-Applikationen zum auslesen von GeoDaten:", "gm")."</span>";
		echo "<br>";
		if( $exif_read_data_func == false){
			echo "<spanclass='gm_warning'>".__("- exif_read_data() funktioniert nicht.", "gm")."</span>";
		}else{
			echo "<span>".__("- exif_read_data()  funktioniert und extrahiert GPS Daten aus den Bildern.", "gm")."</span>";
		}
		echo "<br>";
		if(isset($exifToolCaughtException)){
			echo "<span class='gm_warning'>".__("- ExifTool liefert folgenden Fehler: ", "gm").$exifToolCaughtException."</span>";
			echo "<br>";
		}
		if($geoImgGPX_ExifTool_path !== NULL && strlen($geoImgGPX_ExifTool_path) > 0){
			if( $exiftool_func == false){
				echo "<span class='gm_warning'>".__("- ExifTool ist nicht vorhanden oder der Pfad zu diesem stimmt nicht. Bitte passen sie den Pfad unter 'Wordpress Backend ==> Einstellungen ==> Image in GPS & GPX in Map' an. Erfragen Sie den Pfad bei Ihrem Server-Hosting-Anbieter.", "gm")."</span>";
			}else{
				echo "<span>".__("- ExifTool funktioniert und extrahiert GPS Daten aus den Bildern.", "gm")."</span>";
			}
		}
		echo "<hr>";
	}

	?>
	<div class="wrap">
	    <div id="icon-settings" class="icon32"></div>
		<form id="form_data" name="form" method="post">
			<table>
				<tr>
					<td>
						<h3><?php _e( "Allgemein", 'gm' ) ?></h3>
					</td>
					<td>
					</td>
				</tr>
 				<tr>
					<td>
						<?php _e( "Google API Key:", 'gm' ) ?>
					</td>
					<td>
						<input type="text" name="googleAPIKey" size="40" value="<?php echo $options['googleAPIKey']; ?>">
					</td>
				</tr>
				 <tr>
					<td>
						<?php _e( "ExifTool Path:", 'gm' ) ?>
					</td>
					<td>
						<input type="text" name="geoImgGPX_ExifTool_path" size="40" value="<?php echo (isset($oldExifPath) ? $oldExifPath : $options['geoImgGPX_ExifTool_path']); ?>">
					</td>
				</tr>
				<tr>
					<td>
						<?php _e( "ExifTool Path Samples:", 'gm' ) ?>
					</td>
					<td>
						<p>Sample Path:</p>
						<p>Hostpoint: /usr/local/bin/exiftool</p>
					</td>
				</tr>
				<tr>
					<td>
						<p class="submit">
							<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Settings', 'gm') ?>">
			            </p>
						<input type="hidden" name="form_submit" value="true" />
						<?php wp_nonce_field( 'gmNonceSaveAdmin', 'gmNonceField' ); ?>
					</td>
					<td>
					</td>
				</tr>
			</table>
    	</form>
    </div>
	<?php
}
?>
