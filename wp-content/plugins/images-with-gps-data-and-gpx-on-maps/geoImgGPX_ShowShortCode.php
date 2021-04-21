<?php
// Shortcode: [geoImg]
function geoImg_function(){
//	phpinfo();
//	$url="http://192.168.188.25/WordPress/wp-content/uploads/2016/12/20161110_134141-1024x576.jpg";
//	qg_var_dump( read_exif_data($url,'ANY_TAG', true) );
	ob_start();
	wp_nonce_field( 'geoImgGPX_meta_box', 'geoImgGPX_meta_box_nonce' );

	$postID 					= get_the_ID();
	$geoImgGPXoptions = get_option('geoimggpx_options');
	$gmSelectedImg		= array();

	if( isset($geoImgGPXoptions['geoImgGPX_selectImg'][$postID]) ){
		$gmSelectImgVs = $geoImgGPXoptions['geoImgGPX_selectImg'][$postID];
	}else{
		$gmSelectImgVs = "gmSelectByPage";
	}

	if( $gmSelectImgVs == "gmSelectByPage"){
		$geoImgGPXargs = array(
				'post_type' 				=> 'attachment',
				'numberposts' 			=> -1, // posts_per_page doesn't work for get_posts!
				'orderby' 					=> 'ID',
				'order' 						=> 'ASC',
				'caller_get_posts' 	=> 1,
				'post_parent' 			=> $postID,
				'post_mime_type' 		=> 'image',
				'post_status' 			=> null
		);
		$geoImgGPX_attachments 		= get_posts($geoImgGPXargs);
	}else{
		$gmSelectedImg = get_post_meta( $postID, 'gmSelectedImage', true );
		if(
			isset($gmSelectedImg)
			&&
			is_array($gmSelectedImg)
			&&
			count($gmSelectedImg) > 0
		){
			$geoImgGPXargs = array(
					'post_type' 				=> 'attachment',
					'numberposts' 			=> -1, // posts_per_page doesn't work for get_posts!
					'orderby' 					=> 'ID',
					'order' 						=> 'ASC',
					'include'          	=> $gmSelectedImg,
					'post_mime_type' 		=> 'image',
					'post_status' 			=> null
			);
			$geoImgGPX_attachments 		= get_posts($geoImgGPXargs);
		}else{
			$geoImgGPX_attachments = array();
		}
	}

	$geoImgGPXoptions 			= get_option('geoimggpx_options');
	if( isset($geoImgGPXoptions['geoImgGPX_ExifTool_path']) ){
		$geoImgGPX_ExifTool_path 	= $geoImgGPXoptions['geoImgGPX_ExifTool_path'];
	}else{
		$geoImgGPXoptions['geoImgGPX_ExifTool_path'] = "";
		$geoImgGPX_ExifTool_path 	= "";
	}

	if( !isset($geoImgGPXoptions['googleAPIKey']) ){
		$geoImgGPXoptions['googleAPIKey'] = "";
	}

	if( isset($geoImgGPXoptions['geoImgGPX_GPSCache'])){
		$GeoImgGPX_GPSCache		= $geoImgGPXoptions['geoImgGPX_GPSCache'];
	}else{
		$GeoImgGPX_GPSCache 	= array();
	}

//---------------------------------------------------------------------------------------------
	$i 		= 0;

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
//	qg_var_dump("exif_read_data_func = " . $exif_read_data_func);
//	qg_var_dump(GPSLatitudeDezi($geoImgGPX_exif_data));
//	qg_var_dump($exif_read_data_time);

	$GPSDataExifTool = "";
	if($geoImgGPX_ExifTool_path !== NULL && strlen($geoImgGPX_ExifTool_path) > 0){
		$time_startG = microtime();
		@eval('$GPSDataExifTool='.`$geoImgGPX_ExifTool_path -php -q $testImgPath`);
		$time_endG = microtime();
		$exiftool_time = $time_endG - $time_startG;
		if(is_array($GPSDataExifTool)){
			if( GPSLatitudeDeziExifTool($GPSDataExifTool) != 0 AND GPSLongitudeDeziExifTool($GPSDataExifTool) != 0){
				$exiftool_func			= true;
			}
		}
	//	qg_var_dump("exiftool_func = " . $exiftool_func);
	//	qg_var_dump(GPSLatitudeDeziExifTool($GPSDataExifTool));
	//	qg_var_dump($exiftool_time);
	}

	if( $exif_read_data_func == false and  $exiftool_func == false){
		echo _e("Ihr Server unterstütz keine EXIF-Applikationen zum auslesen von GeoDaten. exif_read_data() ist deaktiviert und ExifTool ist nicht vorhanden oder der Pfad zu diesem stimmt nicht.", "gm");
		return;
	}
	$GeoImgGPX_sameGPS 	= array();
	$globalJsVarTxt 	= "";
	$ii = 0;
	if ($geoImgGPX_attachments) {
		foreach ($geoImgGPX_attachments as $geoImgGPX_post) {
			$globalJsVarTxt .= "var infowindow_".$ii." = []; ";
			$globalJsVarTxt .= "var marker_".$ii." = []; ";
			$ii++;
		}
	}

	?>
	<!--
	Plugin Name => Images with GPS-Data and GPX on Maps
	Plugin URI => https://wordpress.org/plugins/images-with-gps-data-and-gpx-on-maps/
	Description => Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags.
	Author => Severin Roth
	Author URI => https://profiles.wordpress.org/severinroth
	-->
	<div id	= "geoImgGPXMapDiv" class	= "geoImgGPXWidget-canvas" style	= "border:1px solid gray; height:800px; width:100%;">Image Map</div>

	<script type="text/javascript">
	 /*
	 Plugin Name => Images with GPS-Data and GPX on Maps
	 Plugin URI => https://wordpress.org/plugins/images-with-gps-data-and-gpx-on-maps/
	 Description => Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags.
	 Author => Severin Roth
	 Author URI => https://profiles.wordpress.org/severinroth
	 */
		var geoImgGPX_GoogleMapsApiKey = '<?php echo $geoImgGPXoptions['googleAPIKey'] ?>';
		jQuery(document).ready(function(){
			imgMapInitialize();
		});

		var imagePlaceholder 	= "<?php echo GEOIMAGEGPXURL ?>/images/placeholder_1x1.png";
		var GeoImgGPX_AnzImg	= 0;
		var GeoImgGPX_delMarker = [];
		var GeoImgGPX_map		= [];
		var GeoImgGPX_imgObj= {};
		<?php echo $globalJsVarTxt ?>

		function imgMapInitialize(){
			if ( window.console && window.console.log ) {
				window.console.log( "imgMapInitialize(): start" );
			}
			var marker 				= "";
			var i 					= 0;
			var j 					= 0;
			var markerText 			= "";
			var GeoImgGPXBounds 	= new google.maps.LatLngBounds();

			GeoImgGPX_map = new google.maps.Map(document.getElementById('geoImgGPXMapDiv'), {
				zoom: 5,
				center:new google.maps.LatLng(48.1893942, 11.5188319),
//				mapTypeId:google.maps.MapTypeId.ROADMAP,
				mapTypeId:google.maps.MapTypeId.TERRAIN,
				mapTypeControl:true
			});

			<?php
				//---------------------------------------------------------------------------------------------
				if ($geoImgGPX_attachments) {
					foreach ($geoImgGPX_attachments as $geoImgGPX_post) {
						setup_postdata($geoImgGPX_post);
						$hasExifData = false;

						$geoImgGPX_image 		= wp_get_attachment_image_src( $geoImgGPX_post->ID, 'large', false );  	// image_default_size  thumbnail•medium•large•full
						$geoImgGPX_image_marker = wp_get_attachment_image_src( $geoImgGPX_post->ID, array(50, 50), false );

						if( isset($GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi']) and isset($GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi']) ){
							$geoImgGPX_exif_data['GPS']['latdezi'] = $GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi'];
							$geoImgGPX_exif_data['GPS']['lngdezi'] = $GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi'];
							$hasExifData = true;
						}

						if($exif_read_data_func == true and $hasExifData == false){
							$geoImgGPX_exif_data 		= exif_read_data($geoImgGPX_image[0],'ANY_TAG', true);			//http://stackoverflow.com/questions/38772471/php-exif-read-data-no-longer-extracts-gps-location
							if( GPSLatitudeDezi($geoImgGPX_exif_data) != 0 and GPSLongitudeDezi($geoImgGPX_exif_data) != 0 ){
								$geoImgGPX_exif_data['GPS']['latdezi'] 			= GPSLatitudeDezi($geoImgGPX_exif_data);
								$geoImgGPX_exif_data['GPS']['lngdezi'] 			= GPSLongitudeDezi($geoImgGPX_exif_data);
								$hasExifData = true;
							}else{
								$hasExifData = false;
							}
						}

						if($exiftool_func == true and $hasExifData == false){
							$GPSDataExifTool = "";
							$geoImgGPX_image_path 	= get_attached_file( $geoImgGPX_post->ID  );
							eval('$GPSDataExifTool='.`$geoImgGPX_ExifTool_path -php -q $geoImgGPX_image_path` );
							if( is_array($GPSDataExifTool)) {
								$geoImgGPX_exif_data['GPS']['latdezi'] 	= GPSLatitudeDeziExifTool($GPSDataExifTool);
								$geoImgGPX_exif_data['GPS']['lngdezi'] 	= GPSLongitudeDeziExifTool($GPSDataExifTool);
								$geoImgGPX_exif_data['GPS']['alt'] 		= GPSAltitudeExifTool($GPSDataExifTool);
								$hasExifData = true;
							}else{
								$hasExifData = false;
							}
						}

						if( $_SERVER["SERVER_ADDR"] == "192.168.188.25" and $hasExifData == false){
							$geoImgGPX_exif_data['GPS']['latdezi'] = floatval("48.".rand());
							$geoImgGPX_exif_data['GPS']['lngdezi'] = floatval("11.".rand());
							$hasExifData = true;
						}

						if($hasExifData == false){
							continue;
						}


						if( $geoImgGPX_exif_data['GPS']['latdezi'] != 0 and $geoImgGPX_exif_data['GPS']['lngdezi'] != 0){
						?>
						var tmpGeoImgGPX_Content_<?php echo $i ?>  = '<span class="gmGPSWindowTitle"><?php echo urlencode($geoImgGPX_post->post_title) 	?></span>';
							tmpGeoImgGPX_Content_<?php echo $i ?> += '<span class="gmGPSCloseImg" href="\\#" onclick="return infowindow_<?php echo $i ?>.close();"><img src="<?php echo GEOIMAGEGPXURL ?>/images/button/close.gif"></span>';
							tmpGeoImgGPX_Content_<?php echo $i ?> += '<p><?php echo urlencode($geoImgGPX_post->post_content) 	?></p>';
							tmpGeoImgGPX_Content_<?php echo $i ?> += '<img src="<?php echo $geoImgGPX_image[0] ?>" 	>';


						<?php
						if( is_admin() or get_the_author_meta('ID') == get_current_user_id() ){
						?>
							tmpGeoImgGPX_Content_<?php echo $i ?> += "<?php _e('Bild löschen: ','gm')?><span id='gmBEImgDelSpan_<?php echo $geoImgGPX_post->ID ?>' onclick='javascript:delGmBEImgDivW(this.id)' ><img class='gmBEImgDelW'  src='<?php echo GEOIMAGEGPXURL ?>/images/button/x.png' alt='<?php _e('Bild löschen','gm') ?>' title='<?php _e('Bild löschen','gm') ?>'></span>";
						<?php
						}
						?>

						GeoImgGPX_delMarker[<?php echo $geoImgGPX_post->ID ?>]		= 0;

						<?php
						$GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi'] = $geoImgGPX_exif_data['GPS']['latdezi'];
						$GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi'] = $geoImgGPX_exif_data['GPS']['lngdezi'];

						$GeoImgGPX_koor = "'".round($geoImgGPX_exif_data['GPS']['latdezi'], 5)."_".round($geoImgGPX_exif_data['GPS']['lngdezi'], 5)."'";
						if( isset($GeoImgGPX_sameGPS[$GeoImgGPX_koor]) ){
							$geoImgGPX_exif_data['GPS']['latdezi'] = $geoImgGPX_exif_data['GPS']['latdezi'] + ($GeoImgGPX_sameGPS[$GeoImgGPX_koor] * 0.00003);
							$geoImgGPX_exif_data['GPS']['lngdezi'] = $geoImgGPX_exif_data['GPS']['lngdezi'] + ($GeoImgGPX_sameGPS[$GeoImgGPX_koor] * 0.00010);
							$GeoImgGPX_sameGPS[$GeoImgGPX_koor]++;
						}else{
							$GeoImgGPX_sameGPS[$GeoImgGPX_koor] = 1;
						}
						?>

							var tmpGeoImgGPXPos 	= new google.maps.LatLng( <?php echo $geoImgGPX_exif_data['GPS']['latdezi'] ?> , <?php echo $geoImgGPX_exif_data['GPS']['lngdezi'] ?> );

							infowindow_<?php echo $i ?> = new google.maps.InfoWindow({
																							content: decodeURIComponent(tmpGeoImgGPX_Content_<?php echo $i ?>)
																						});

							marker_<?php echo $i ?> = new google.maps.Marker({
																					position: tmpGeoImgGPXPos,
																					map: GeoImgGPX_map,
																					icon: imagePlaceholder,
																					label: '<?php echo $i + 1 ?>',
																					title: '<?php echo $geoImgGPX_image_marker[0] ?>_x|x_<?php echo $geoImgGPX_post->ID ?>'
																				});


							marker_<?php echo $i ?>.addListener("click", function() {
																			for (j = 0; j < i; j++) {
																				eval( "infowindow_" + j + ".close(GeoImgGPX_map, marker_" + j + ")" );
																			}
																			infowindow_<?php echo $i ?>.open(GeoImgGPX_map, marker_<?php echo $i ?>);
																			setTimeout(GeoImgGPX_changeMarker, 750);
																		});

							GeoImgGPXBounds.extend(tmpGeoImgGPXPos);
							i++;
						<?php
						$i++;
					}
				}
		//---------------------------------------------------------------------------------------------
				?>
				GeoImgGPX_AnzImg = i;
				if( i == 0){
					alert("<?php _e('Es wurden keine Bilder mit GeoDaten gefunden.', 'gm'); ?>");
					return;
				}else{
					GeoImgGPX_map.fitBounds(GeoImgGPXBounds);

					var zoom = GeoImgGPX_map.getZoom() > 16 ? 16 : GeoImgGPX_map.getZoom();
					GeoImgGPX_map.setZoom(zoom);

					GeoImgGPX_map.addListener('zoom_changed', function(){
//						console.log('zoom_changed');
						setTimeout(GeoImgGPX_changeMarker, 500);
					});
					GeoImgGPX_map.addListener('dragend', function(){
						setTimeout(GeoImgGPX_changeMarker, 500);
					});

					setTimeout(GeoImgGPX_changeMarker, 500);
				}
			<?php
		}
		?>
		if ( window.console && window.console.log ) {
			window.console.log( "imgMapInitialize(): finish" );
		}
	}
	function GeoImgGPX_changeMarker(){
		var aI = 0;

		// https://developers.google.com/maps/documentation/javascript/examples/maptype-image-overlay?hl=de

		GeoImgGPX_imgObj = jQuery('#geoImgGPXMapDiv [title]');
		if(GeoImgGPX_imgObj.length == 0){
			setTimeout(GeoImgGPX_changeMarker, 50);
			console.log("GeoImgGPX_changeMarker ==> timeOut");
			return;
		}
//		console.log("-------------------------");
//		console.log(allObj);

		jQuery(".GeoImgGPXmarkerContainer").remove();

		GeoImgGPX_imgObj.each(function(i, obj) {  /* Falls Google das Div auch ncit mehr benützt, einfach nach allen Titteln suchen */
//		jQuery('[title]').each(function(i, obj) {
//			console.log(obj);
			if(obj.title.length > 0 && obj.title.indexOf("_x|x_") !== -1){
//			console.log(obj.title);
				var title 	= obj.title.split("_x|x_");
				var ID		= title[1];
				var imgUrl 	= title[0];
				aI++;
				if( GeoImgGPX_delMarker[ID] == 0 ){
					var cont  = "<div class='GeoImgGPXmarkerContainer' id='GeoImgGPXmarkerContainer_" + ID + "' style='left:-63px; top:-176px; position:relative;'  >";
						 	cont += 	"<div class='GeoImgGPXmarkerImg'>";
						 	cont += 		"<div class='GeoImgGPXmarkerThumb' style=' background-image: url(\"" + imgUrl + "\") '></div>";
						 	cont += 		"<div class='GeoImgGPXmarkerDot'></div>";
						 	cont += 	"</div>";
						 	cont += "</div>";

					jQuery(obj)
						.append(cont)
						.css({ "opacity":"1", "overflow":"visible"})
						.show();
				}
			}
		});

		if(GeoImgGPX_AnzImg != aI){
			setTimeout(GeoImgGPX_changeMarker, 150);
		}

/*
		jQuery("img").attr("src", imagePlaceholder).remove();
		jQuery(".gm-style img").attr("src", imagePlaceholder).each(function(i, obj) {

		});
*/
	}

	delGmBEImgDivW = function (id) {
		var Id 	= id.split("_");
			Id 	= Id[1];
	  		jQuery( "#" + id ).html('<?php _e('Bild gelöscht', 'gm') ?>');
	  		jQuery( "#GeoImgGPXmarkerContainer_" + Id ).css( 'display', 'none');
	  		for (j = 0; j < GeoImgGPX_AnzImg; j++) {
				eval( "infowindow_" + j + ".close(GeoImgGPX_map, marker_" + j + ")" );
			}

		jQuery.ajax({
			  	method: 		'POST',
			  	url: 			"<?php echo admin_url( "admin-ajax.php")?>",
			  	async: 			true,
			  	data: {
				  		action		: "geoimggpx_ajax_delAdminPostPage_request",
				  		nonce		: document.getElementById('geoImgGPX_meta_box_nonce').value,
				  		ID			: Id
				  	},
				  	success : function( response ) {
				  		GeoImgGPX_delMarker[Id] = 1;
					},
				    error: function (jqXHR,textStatus,errorThrown) {
		 		    	var msg = jqXHR + " :: " + textStatus + " :: " + errorThrown;
				        alert( msg );
				    }
		});
	};

	</script>
	<?php
	$geoImgGPXoptions['geoImgGPX_GPSCache'] = $GeoImgGPX_GPSCache;
	update_option('geoimggpx_options', $geoImgGPXoptions);
	return ob_get_clean();
}
add_shortcode('geoImg', 'geoImg_function' );
?>
