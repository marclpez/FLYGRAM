<?php
/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
function geoImgGPX_meta_box_callback( $post ) {


	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'geoImgGPX_meta_box', 'geoImgGPX_meta_box_nonce' );

	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$options 	= get_option('geoimggpx_options');
	$options['googleAPIKey'];
	global $wpdb;

	$postId = get_the_ID();
	$userId = get_current_user_id();


	?>
	<div style="height:510px; overflow-x:auto; ">
		<div id="GeoImgGPXAdminResponse">
			<img id="geoImgGPXSpinner" src="<?php echo GEOIMAGEGPXURL."/images/Spinner/spinner.gif" ?>">
			<span id="GeoImgGPXAdminResponseTxt"><?php _e('Daten werden geladen', 'gm')?></span>
		</div>
	</div>
	<script type="text/javascript">
		jQuery( "#GeoImgGPXAdminResponse" ).load( "<?php echo admin_url( "admin-ajax.php")?>", { action: "geoimggpx_ajax_initAdminPostPage_request", nonce: document.getElementById('geoImgGPX_meta_box_nonce').value, postID:"<?php echo get_the_ID()?>" }, function(response, status, xhr ) {
			 if ( status == "error" ) {
				var msg = "Sorry but there was an error: ";
				jQuery( "#GeoImgGPXerror" ).html( msg + xhr.status + " " + xhr.statusText );
			}
		});
	</script>
	
	
<?php 
}
?>