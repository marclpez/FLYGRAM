<?php
add_action('wp_ajax_geoimggpx_ajax_initAdminPostPage_request', 				'geoimggpx_ajax_initAdminPostPage_request', 10);
add_action('wp_ajax_nopriv_geoimggpx_ajax_initAdminPostPage_request', 		'geoimggpx_ajax_initAdminPostPage_request', 10);
//do_action( 'wp_ajax_test_xox', 10);
//do_action( 'wp_ajax_nopriv_test_xox', 10);

function geoimggpx_ajax_initAdminPostPage_request(){
	if (
			!isset( $_POST['nonce'] )
				or
		 	!current_user_can('edit_plugins')
		) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'geoImgGPX_meta_box' ) ) {
		return;
	}

	$postID 					= sanitize_text_field($_POST['postID']);
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

	$geoImgGPX_ExifTool_path 	= $geoImgGPXoptions['geoImgGPX_ExifTool_path'];
	if( isset($geoImgGPXoptions['geoImgGPX_GPSCache'])){
		$GeoImgGPX_GPSCache		= $geoImgGPXoptions['geoImgGPX_GPSCache'];
	}else{
		$GeoImgGPX_GPSCache 	= array();
	}
	$GeoImgGPX_GPSCacheLokal	= array();

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
		echo __("Ihr Server unterstütz keine EXIF-Applikationen zum auslesen von GeoDaten. exif_read_data() ist deaktiviert und ExifTool ist nicht vorhanden oder der Pfad zu diesem stimmt nicht.", "gm");
		return;
	}else{
		echo "<span>".__('ShortCode für GeoImages in Ihrem Beitrag: [geoImg]', 'gm')." </span>";
		echo "<br>";
		echo "<span>".__("Ihr Server unterstütz EXIF-Applikationen zum auslesen von GeoDaten:", "gm")."</span>";
		echo "<br>";
			if( $exif_read_data_func == false){
				echo "<span>".__("- exif_read_data() funktioniert nicht.", "gm")."</span>";
			}else{
				echo "<span>".__("- exif_read_data()  funktioniert und extrahiert GPS Daten aus den Bildern.", "gm")."</span>";
			}
			echo "<br>";
			if( $exiftool_func == false && $exif_read_data_func == false){
				echo "<span>".__("- ExifTool ist nicht vorhanden oder der Pfad zu diesem stimmt nicht. Bitte passen sie den Pfad unter Einstellungen/Images in GPS an. Erfragen Sie den Pfad bei Ihrem Server-Hosting-Anbieter.", "gm")."</span>";
			}else if( $exiftool_func == true ){
				echo "<span>".__("- ExifTool funktioniert und extrahiert GPS Daten aus den Bildern.", "gm")."</span>";
			}
		ob_start();
			?>
			<hr>
			<div class="wp-editor-tools hide-if-no-js">
				<div class="wp-media-buttons">
					<input type="radio" id="gmSelectByPage" name="gmSelectImg" value="gmSelectByPage" <?php if ($gmSelectImgVs == "gmSelectByPage"): ?>checked<?php endif; ?> onchange='gmGetSelectImg("<?php echo $postID ?>")'>
					<label for="gmSelectByPage"><?php _e("Get all images from this page/site", "gm") ?></label>
					<br/>
					<input type="radio" id="gmSelfSelect" 	name="gmSelectImg" value="gmSelfSelect" <?php if ($gmSelectImgVs == "gmSelfSelect"): ?>checked<?php endif; ?> onchange='gmGetSelectImg("<?php echo $postID ?>")'>
					<label for="gmSelfSelect"><?php _e("Self select images from Media", "gm") ?></label>
				</div>
			</div>
			<hr>
			<div class="wp-editor-tools hide-if-no-js">
				<div class="wp-media-buttons" id="wp-content-media-buttons">
					<button class="button insert-media add_media" id="insert-media-button" type="button" data-editor="content">
						<span class="wp-media-buttons-icon"></span><?php _e("Add Images", "gm") ?>
					</button>
				</div>
				<div class="wp-media-buttons">
					<button class="button" onClick='gmBEIgetBackendData("<?php echo $postID ?>");' type="button" data-editor="content">
						<span class="dashicons-before dashicons-update" style="top:5px; position:relative;"></span><?php _e("Reload images", "gm") ?>
					</button>
				</div>
			</div>
			<?php
			if( $gmSelectImgVs == "gmSelfSelect"){
			?>
				<div class="wp-editor-tools hide-if-no-js">
					<div class="wp-media-buttons" id="wp-content-media-buttons">
						<button class="button" id="gmSelectMediaButton" type="button" data-editor="content" onclick="gm_open_media_window()">
							<span class="dashicons-before dashicons-search"></span><?php _e("Select/deselect Images", "gm") ?>
						</button>
						<i style="vertical-align:middle"><?php _e("(Hold Ctrl and select images)", "gm") ?></i>
					</div>
				</div>
			<?php
			}
		echo ob_get_clean();
		echo "<hr>";
	}

	//---------------------------------------------------------------------------------------------
	if ($geoImgGPX_attachments) {
		foreach ($geoImgGPX_attachments as $geoImgGPX_post) {
			setup_postdata($geoImgGPX_post);
			$hasExifData 		 = false;
			$geoImgGPX_exif_data = array();

			$geoImgGPX_image 		= wp_get_attachment_image_src( $geoImgGPX_post->ID, 'large', false );  	// image_default_size  thumbnail•medium•large•full
			$geoImgGPX_image_marker = wp_get_attachment_image_src( $geoImgGPX_post->ID, array(50, 50), false );

			if( isset($GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi']) and isset($GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi']) ){
				$geoImgGPX_exif_data['GPS']['latdezi'] 	= $GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi'];
				$geoImgGPX_exif_data['GPS']['lngdezi'] 	= $GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi'];
				$geoImgGPX_exif_data['GPS']['alt'] 		= $GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['alt'];
				$hasExifData = true;
			}

			if($exif_read_data_func == true and $hasExifData == false){
				$geoImgGPX_exif_data 		= @exif_read_data($geoImgGPX_image[0],'ANY_TAG', true);			//http://stackoverflow.com/questions/38772471/php-exif-read-data-no-longer-extracts-gps-location
				if( GPSLatitudeDezi($geoImgGPX_exif_data) != 0 and GPSLongitudeDezi($geoImgGPX_exif_data) != 0 ){
					$geoImgGPX_exif_data['GPS']['latdezi'] 			= GPSLatitudeDezi($geoImgGPX_exif_data);
					$geoImgGPX_exif_data['GPS']['lngdezi'] 			= GPSLongitudeDezi($geoImgGPX_exif_data);
					$geoImgGPX_exif_data['GPS']['alt']				= "-";
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
					$geoImgGPX_exif_data['GPS']['alt'] 			= GPSAltitudeExifTool($GPSDataExifTool);
					$hasExifData = true;
				}else{
					$hasExifData = false;
				}
			}

			$GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['latdezi'] 			= isset($geoImgGPX_exif_data['GPS']['latdezi']) ? $geoImgGPX_exif_data['GPS']['latdezi'] 	: "";
			$GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['lngdezi'] 			= isset($geoImgGPX_exif_data['GPS']['lngdezi']) ? $geoImgGPX_exif_data['GPS']['lngdezi'] 	: "";
			$GeoImgGPX_GPSCache[$geoImgGPX_post->ID]['alt']						= isset($geoImgGPX_exif_data['GPS']['alt']) 		? $geoImgGPX_exif_data['GPS']['alt'] 			: "";
			$GeoImgGPX_GPSCacheLokal[$geoImgGPX_post->ID]['latdezi'] 	= isset($geoImgGPX_exif_data['GPS']['latdezi'])	? $geoImgGPX_exif_data['GPS']['latdezi']	: "";
			$GeoImgGPX_GPSCacheLokal[$geoImgGPX_post->ID]['lngdezi'] 	= isset($geoImgGPX_exif_data['GPS']['lngdezi'])	? $geoImgGPX_exif_data['GPS']['lngdezi']	: "";
			$GeoImgGPX_GPSCacheLokal[$geoImgGPX_post->ID]['imgUrl'] 	= $geoImgGPX_image[0];



			echo "<div class='gmBEImgDiv' id='gmBEImgDiv_".$geoImgGPX_post->ID."'>";
				echo "<table><tr><td>";
					echo "<img id='gmBEImg_".$geoImgGPX_post->ID."' 	class='gmBEImg' 	src='".$geoImgGPX_image_marker[0]."'>";
					echo "<img id='gmBEImgBig_".$geoImgGPX_post->ID."' title='".__('Click for close','gm')."'	class='gmBEImgBig' 	src='".$geoImgGPX_image[0]."'>";
				echo "</td><td>";
					echo "<span class='gmBEImgDiv_title' style='hyphens: auto;'>".$geoImgGPX_post->post_title."</span>";
					echo "<br>";
					if(
						isset($geoImgGPX_exif_data['GPS'])
						&&
						isset($geoImgGPX_exif_data['GPS']['latdezi'])
						&&
						isset($geoImgGPX_exif_data['GPS']['lngdezi'])
						&&
						$geoImgGPX_exif_data['GPS']['latdezi'] != 0
						&&
						$geoImgGPX_exif_data['GPS']['lngdezi'] != 0
						&&
						$geoImgGPX_exif_data['GPS']['latdezi'] != ""
						&&
						$geoImgGPX_exif_data['GPS']['lngdezi'] != ""
					){
						echo "<span class='gmBEedit' id='gmBEedit_".$geoImgGPX_post->ID."' title='".__('GeoDaten vorhanden','gm')."' >".__('GPS-Daten edit: ','gm')."<img class='gmBEImgGPS ok' src='".GEOIMAGEGPXURL."/images/button/ok.png' alt='".__('GeoDaten vorhanden','gm')."' title='".__('GeoDaten vorhanden','gm')."' ></span>";
					}else{
						echo "<span class='gmBEedit' id='gmBEedit_".$geoImgGPX_post->ID."' title='".__('Keine GeoDaten gefunden. Klicken sie auf diesen Button und geben Sie die Geodaten manuel ein.','gm')."' >".__('GPS-Daten edit: ','gm')."<img class='gmBEImgGPS ask' src='".GEOIMAGEGPXURL."/images/button/ask.png' alt='".__('Keine GeoDaten gefunden','gm')."' title='".__('Keine GeoDaten gefunden. Klicken sie auf diesen Button und geben Sie die Geodaten manuel ein.','gm')."' ></span>";
					}
					if($gmSelectImgVs == "gmSelectByPage"){
						echo "<br>";
						echo "<span class='gmBEdel' id='gmBEdel_".$geoImgGPX_post->ID."' title='".__('Bild löschen','gm')."' >".__('Bild löschen: ','gm')."<img class='gmBEImgDel' src='".GEOIMAGEGPXURL."/images/button/x.png' alt='".__('Bild löschen','gm')."' title='".__('Bild löschen','gm')."' ></span>";
					}
				echo "</td></tr></table>";
			echo "</div>";
			$i++;
		}
	}
	echo '<div style="clear:both;"></div>';

	if($i == 0){
		echo "<p>".__("Keine Bilddaten gefunden", "gm")."</p>";
		if( $gmSelectImgVs == "gmSelfSelect"){
		?>
			<div class="wp-editor-tools hide-if-no-js">
				<div class="wp-media-buttons" id="wp-content-media-buttons">
					<button class="button" id="gmSelectMediaButton" type="button" data-editor="content" onclick="gm_open_media_window()">
						<span class="dashicons-before dashicons-search"></span><?php _e("Select/deselect Images", "gm") ?>
					</button>
					<i style="vertical-align:middle"><?php _e("(Hold Ctrl and select images)", "gm") ?></i>
				</div>
			</div>
		<?php
		}
	}
	?>
	<script type="text/javascript">
		var GeoImgGPX_GPSCacheLokal = <?php echo json_encode($GeoImgGPX_GPSCacheLokal) ?>;

		gmBEImgShow = function (e) {
			var Id = this.id.split("_");
				Id = Id[1];
			jQuery("#gmBEImgBig_" + Id ).css( 'display', 'inline');
		};

		jQuery(".gmBEImg").click(gmBEImgShow);

		gmBEImgBigClose = function (e) {
			jQuery( this ).css( 'display', 'none');
		};

		jQuery(".gmBEImgBig").click(gmBEImgBigClose);

		delGmBEImgDiv = function (e) {
			var Id = this.id.split("_");
				Id = Id[1];
			jQuery( "#gmBEImgDiv_" + Id ).css( 'display', 'none');
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
/*							alert(response);			*/
						},
							error: function (jqXHR,textStatus,errorThrown) {
								var msg = jqXHR + " :: " + textStatus + " :: " + errorThrown;
									alert( msg );
							}
			});
		};

		jQuery(".gmBEdel").click(delGmBEImgDiv);


		var latdeziLast = 48.1893942;
		var lngdeziLast = 11.5188319;

		delGmBEIedit = function (e) {
			var Id = this.id.split("_");
				Id = Id[1];

			var latdezi =	jQuery.isNumeric(GeoImgGPX_GPSCacheLokal[Id]['latdezi']) ? GeoImgGPX_GPSCacheLokal[Id]['latdezi'] : latdeziLast;
			var lngdezi =	jQuery.isNumeric(GeoImgGPX_GPSCacheLokal[Id]['lngdezi']) ? GeoImgGPX_GPSCacheLokal[Id]['lngdezi'] : lngdeziLast;
			var imgUrl	= 	GeoImgGPX_GPSCacheLokal[Id]['imgUrl'];

			var cont  = "";
				cont += "<div class='gmCloseHead'>&nbsp;<b><?php _e('GPS Position des Bildes auf der Karte ändern', 'gm')?></b></div>";
				cont += '<img class="gmGPSEditImg" src="' + imgUrl + '">';
				cont += "<span class='gmClose' title='<?php _e('close', 'gm')?>' ><b>X</b></span>";
				cont += '<br>';
				cont += '<?php _e('Verschieben Sie den Marker zu der richtigen Position', 'gm')?>';
				cont += '<br>';
				cont += '<div id="gmEditMap"></div>';


			jQuery( "#geoImgGPXdialog" ).css( 'display', 'block');
			jQuery( "#geoImgGPXdialog" ).html( cont );
			jQuery( ".gmClose" ).click( gmCloseSpan );
			initMap(Id, latdezi, lngdezi);
		};

		function gmBEIeditSave(Id, latdezi, lngdezi){
			if( !jQuery.isNumeric( latdezi )){
				alert('<?php _e('Geben Sie einen Numerischen Wert an. Erlaubte Werte: . 0-9 Bsp.: 48.345', 'gm')?>');
				return;
			}
			if( !jQuery.isNumeric( lngdezi )){
				alert('<?php _e('Geben Sie einen Numerischen Wert an. Erlaubte Werte: . 0-9 Bsp.: 48.345', 'gm')?>');
				return;
			}
			Id		= parseInt(Id);
			latdezi = parseFloat(latdezi);
			lngdezi = parseFloat(lngdezi);
			GeoImgGPX_GPSCacheLokal[Id]['latdezi'] = latdezi;
			GeoImgGPX_GPSCacheLokal[Id]['lngdezi'] = lngdezi;

			latdeziLast	= latdezi;
			lngdeziLast = lngdezi;

			jQuery.ajax({
					method: 		'POST',
					url: 			"<?php echo admin_url( "admin-ajax.php")?>",
					async: 			true,
					data: {
							action		: "geoimggpx_ajax_editAdminPostPage_request",
							nonce		: document.getElementById('geoImgGPX_meta_box_nonce').value,
							ID			: Id,
							latdezi		: latdezi,
							lngdezi		: lngdezi
						},
						success : function( response ) {
						jQuery( "#gmBEedit_" + Id + " > img").attr('src', '<?php echo GEOIMAGEGPXURL ?>/images/button/ok.png');
					},
						error: function (jqXHR,textStatus,errorThrown) {
							var msg = jqXHR + " :: " + textStatus + " :: " + errorThrown;
								alert( msg );
						}
			});
		}

		jQuery(".gmBEedit").click(delGmBEIedit);

		gmCloseSpan = function (e) {
			jQuery( "#geoImgGPXdialog" ).css( 'display', 'none');
		};

		function initMap(id, latdezi, lngdezi) {
			var myLatLng = {lat: latdezi, lng: lngdezi };

			var map = new google.maps.Map(document.getElementById('gmEditMap'), {
			    zoom: 12,
			    center: myLatLng,
				mapTypeId: google.maps.MapTypeId.TERRAIN
			});

			var marker = new google.maps.Marker({
			    position: myLatLng,
			    map: map,
				draggable: true,
			    title: '<?php _e('Verschiebe mich zu der richtigen Position', 'gm')?>'
			});
			marker.addListener('dragend', function(e){
				gmBEIeditSave(id, e.latLng.lat(), e.latLng.lng());
			});
		}

		function gmBEIgetBackendData(postID){
			<?php
			ob_start();
				?>
					<div style="height:510px; overflow-x:auto; ">
						<div id="GeoImgGPXAdminResponse">
							<img id="geoImgGPXSpinner" src="<?php echo GEOIMAGEGPXURL."/images/Spinner/spinner.gif" ?>">
							<span id="GeoImgGPXAdminResponseTxt"><?php _e('Daten werden geladen', 'gm')?></span>
						</div>
					</div>
				<?php
			$spinnerContent = ob_get_clean();
			$spinnerContent = trim(preg_replace('/\s+/', ' ', $spinnerContent));
			$spinnerContent = trim($spinnerContent);
			?>
			jQuery( "#GeoImgGPXAdminResponse" ).html("<?php echo addslashes($spinnerContent) ?>");

			jQuery( "#GeoImgGPXAdminResponse" ).load(
				"<?php echo admin_url( "admin-ajax.php")?>",
				{
					action: "geoimggpx_ajax_initAdminPostPage_request",
					nonce: document.getElementById('geoImgGPX_meta_box_nonce').value,
					postID:postID
				},
				function(response, status, xhr ) {
				 if ( status == "error" ) {
					var msg = "Sorry but there was an error: ";
					jQuery( "#GeoImgGPXerror" ).html( msg + xhr.status + " " + xhr.statusText );
				}
			});
		}

/*
		function gmBEIgetBackendOpenMediaLibrary(postID){
			alert(postID);
		}
*/
		function gmGetSelectImg(Id){
			jQuery.ajax({
					method: 	'POST',
					url: 			"<?php echo admin_url( "admin-ajax.php")?>",
					async: 		true,
					data: {
							action	   : "geoimggpx_ajax_saveSelectImg_request",
							nonce		   : document.getElementById('geoImgGPX_meta_box_nonce').value,
							ID			   : Id,
							gmSelectImg: document.querySelector('input[name="gmSelectImg"]:checked').value
						},
						success : function( response ) {
							gmBEIgetBackendData(Id);
						},
						error: function (jqXHR,textStatus,errorThrown) {
							var msg = jqXHR + " :: " + textStatus + " :: " + errorThrown;
								alert( msg );
						}
			});
		}

		function gm_open_media_window() {
			var gmMediaIdsArr = <?php echo html_entity_decode(json_encode($gmSelectedImg)); ?>;
			var image_frame;
			image_frame = wp.media(
				 {
					 title: "Select Images",
					 multiple : true,
					 frame: "select",
					 library : {
						 type : "image"
					 }
				 }
			 );
			 image_frame.on("open", function() {
				 if ( !isset(gmMediaIdsArr) ) {
					 gmMediaIdsArr = [];
				 }
				 var selection = image_frame.state().get("selection");
				 jQuery.each(gmMediaIdsArr, function(key, id) {
					 var attachment = wp.media.attachment(id);
					 attachment.fetch();
					 selection.add( attachment ? [ attachment ] : [] );
				 });
			 });
			 image_frame.open();

				/* Alle Events zu wp.media.view.MediaFrame https://atimmer.github.io/wordpress-jsdoc/wp.media.view.MediaFrame.Select.html */
			 image_frame.on("close escape",function() {								/* multi events on one function, escape ist schliessen des Frame mit esc oder per X schliessen */
				 var selection = image_frame.state().get("selection");
				 gmMediaIdsArr = [];
				 selection.each(function(attachment) {
					 gmMediaIdsArr.push(attachment["id"]);
				 });
				 console.log(gmMediaIdsArr);
				 jQuery.ajax({
					method: 		'POST',
					url: 			"<?php echo admin_url( "admin-ajax.php")?>",
					async: 			true,
					data: {
						action		: "geoimggpx_ajax_updateSelectedImg_request",
						nonce			: document.getElementById('geoImgGPX_meta_box_nonce').value,
						ID				: <?php echo $postID ?>,
						selected	: JSON.stringify(gmMediaIdsArr)
					},
					success : function( response ) {
						gmBEIgetBackendData( <?php echo $postID ?>);
					},
					 error: function (jqXHR,textStatus,errorThrown) {
						 var msg = jqXHR + " :: " + textStatus + " :: " + errorThrown;
							 alert( msg );
					 }
				 });

			 });
		}
	</script>
	<div id="geoImgGPXdialog"></div>
	<?php
	$geoImgGPXoptions['geoImgGPX_GPSCache'] = $GeoImgGPX_GPSCache;
	update_option('geoimggpx_options', $geoImgGPXoptions);
	wp_die();
}
// ----------------------------------------------------------------------------------------------------------------------------
add_action('wp_ajax_geoimggpx_ajax_delAdminPostPage_request', 				'geoimggpx_ajax_delAdminPostPage_request', 10);
add_action('wp_ajax_nopriv_geoimggpx_ajax_delAdminPostPage_request', 		'geoimggpx_ajax_delAdminPostPage_request', 10);

function geoimggpx_ajax_delAdminPostPage_request(){
	if (
			!isset( $_POST['nonce'] )
				or
		 	!current_user_can('edit_plugins')
		) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'geoImgGPX_meta_box' ) ) {
		return;
	}
	$attachID = sanitize_text_field($_POST['ID']);

	if ( false === wp_delete_attachment( $attachID ) ){
		_e('Fehler: Bild nicht gelöscht', 'gm');
	}else{
		_e('Bild gelöscht', 'gm');
	}

	wp_die();
}
// ----------------------------------------------------------------------------------------------------------------------------
add_action('wp_ajax_geoimggpx_ajax_editAdminPostPage_request', 				'geoimggpx_ajax_editAdminPostPage_request', 10);
add_action('wp_ajax_nopriv_geoimggpx_ajax_editAdminPostPage_request', 		'geoimggpx_ajax_editAdminPostPage_request', 10);

function geoimggpx_ajax_editAdminPostPage_request(){
	if (
			!isset( $_POST['nonce'] )
				or
		 	!current_user_can('edit_plugins')
		) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'geoImgGPX_meta_box' ) ) {
		return;
	}
	$geoImgGPXoptions 			= get_option('geoimggpx_options');
	if( isset($geoImgGPXoptions['geoImgGPX_GPSCache'])){
		$GeoImgGPX_GPSCache		= $geoImgGPXoptions['geoImgGPX_GPSCache'];
	}else{
		$GeoImgGPX_GPSCache 	= array();
	}

	$attachID	= sanitize_text_field($_POST['ID']);

	$GeoImgGPX_GPSCache[$attachID]['latdezi'] 	= floatval(sanitize_text_field($_POST['latdezi']));
	$GeoImgGPX_GPSCache[$attachID]['lngdezi'] 	= floatval(sanitize_text_field($_POST['lngdezi']));

	$geoImgGPXoptions['geoImgGPX_GPSCache'] = $GeoImgGPX_GPSCache;
	update_option('geoimggpx_options', $geoImgGPXoptions);

	wp_die();
}
// ----------------------------------------------------------------------------------------------------------------------------
add_action('wp_ajax_geoimggpx_ajax_saveSelectImg_request', 				'geoimggpx_ajax_saveSelectImg_request', 20);
add_action('wp_ajax_nopriv_geoimggpx_ajax_saveSelectImg_request', 'geoimggpx_ajax_saveSelectImg_request', 20);

function geoimggpx_ajax_saveSelectImg_request(){
	if (
			!isset( $_POST['nonce'] )
				or
		 	!current_user_can('edit_plugins')
		) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'geoImgGPX_meta_box' ) ) {
		return;
	}
	$geoImgGPXoptions 			= get_option('geoimggpx_options');
	if( isset($geoImgGPXoptions['geoImgGPX_selectImg'])){
		$GeoImgGPX_selectImg		= $geoImgGPXoptions['geoImgGPX_selectImg'];
	}else{
		$GeoImgGPX_selectImg 	= array();
	}

	$attachID	= sanitize_text_field($_POST['ID']);

	$GeoImgGPX_selectImg[$attachID] = sanitize_text_field($_POST['gmSelectImg']);


	$geoImgGPXoptions['geoImgGPX_selectImg'] = $GeoImgGPX_selectImg;
	update_option('geoimggpx_options', $geoImgGPXoptions);

	wp_die();
}
// ----------------------------------------------------------------------------------------------------------------------------
add_action('wp_ajax_geoimggpx_ajax_updateSelectedImg_request', 				'geoimggpx_ajax_updateSelectedImg_request', 30);
add_action('wp_ajax_nopriv_geoimggpx_ajax_updateSelectedImg_request', 'geoimggpx_ajax_updateSelectedImg_request', 30);

function geoimggpx_ajax_updateSelectedImg_request(){
	if (
			!isset( $_POST['nonce'] )
				or
		 	!current_user_can('edit_plugins')
		) {
		return;
	}
	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'geoImgGPX_meta_box' ) ) {
		return;
	}

	$postID		= sanitize_text_field($_POST['ID']);
	$selected = sanitize_text_field($_POST['selected']);
	$selected = json_decode($selected);

	update_post_meta( $postID, 'gmSelectedImage', $selected );

	wp_die();
}
?>
