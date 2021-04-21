<?php 

 if( ! defined('ABSPATH' ) ){
	exit;
}

function wpfm_cpt_register_post_type() {
	
	$custom_post_types_meta = wpfm_array_cpt_meta();
	
	foreach( $custom_post_types_meta as $type => $cpt ) {
		
		register_post_type ( $type, $cpt );
		
	}
	
}

function admin_file_upload_metaboxes(){
	add_meta_box( 
            'wpfm_admin_file_upload',
            __( 'File Manage' , 'wpfm'),
            'admin_file_upload',
            'wpfm-files',
            'normal',
            'high'
        );
}

/* == Load questions template ==*/
function admin_file_upload(){
	
	if ( !is_user_logged_in() && !$allow_public) {

		$public_message = wpfm_get_option('_public_message');
		if($public_message != ''){
			ob_start ();
		
			printf(__('%s', 'wpfm'), $public_message);
			$output_string = ob_get_contents ();
			ob_end_clean ();
			return $output_string;
		}else{
			echo '<script type="text/javascript">
			window.location = "'.wp_login_url( get_permalink() ).'"
			</script>';
			return ;
		}
	}
	

	// Loading all required scripts
	wpfm_load_scripts();


	$file_id = isset($_REQUEST['file_id']) ? $_REQUEST['file_id'] : '';
	$template_vars = array('group_ids' => '', 'file_id' => $file_id);
	
	ob_start ();
	
		wpfm_load_templates('wpfm-main.php', $template_vars);
		$output_string = ob_get_contents ();
		
	ob_end_clean ();

	echo $output_string;
}



function wpfm_user_file_model( $file ) { ?>
	<?php if ( $file->node_type == 'dir' ){ ?>
		<a class='view-icon button button-primary' href="#dir_modal_<?php echo $file->id; ?>" data-target="dir_modal_<?php echo $file->id; ?>" id="dir<?php echo $file->id; ?>">
			<span class="detail-icon dashicons dashicons-search"></span>
		</a>
		<?php wpfm_dir_model($file); ?>
	<?php
	 }else { ?>
		
		<a class='view-icon btn button button-primary' href="#file_detail_box_<?php echo $file->id; ?>" data-target="file_detail_box_<?php echo $file->id; ?>" id="modal<?php echo $file->id; ?>">
			<span class="detail-icon dashicons dashicons-visibility"></span>
		</a>
	<?php echo $file->file_detail_html; ?>
	<?php  } ?>
	
<?php  
}

function wpfm_dir_model($file){ ?>

	
	<div id="dir_modal_<?php echo $file->id; ?>">
	    <div class="close-modal-btn close-dir_modal_<?php echo $file->id; ?>">
	    	<img class="close-btn" src="<?php echo WPFM_URL ?>/images/closebt.svg">
	    </div>
	        <div class="wpfm-modal-content">
	        	<h2 class="modal-title"><?php _e($file->title, "wpfm"); ?></h2>
	            <hr>
				<?php 
					if (!empty($file->children)) { 
						
						get_dir_detail($file->children); 
					} else {
						echo 'No Nested found';
					}
				?>
			</div>
	    </div>
	</div>
<?php } 

function get_dir_detail($files) { ?>

	<div class="row">
	<?php 
	foreach ($files as $file) { ?>
			<div class="col-md-2">
				<?php if ($file->node_type == 'file'){ ?>
					<a class='view-icon modal<?php echo $file->id; ?> close-dir_modal_<?php echo $file->file_parent; ?>' href="#file_detail_box_<?php echo $file->id; ?>" data-target="file_detail_box_<?php echo $file->id; ?>" data-modal_id="modal<?php echo $file->id; ?>">
					
					<?php 
						echo $file->thumb_image;
						echo '<h4 class="title">'.$file->title.'</h4>';

					 ?>
					</a>
				<?php }else { ?>
					<a class='view-icon dir<?php echo $file->id; ?> close-dir_modal_<?php echo $file->file_parent; ?>' href="#dir_modal_<?php echo $file->id; ?>" data-target="dir_modal_<?php echo $file->id; ?>" data-modal_id="dir<?php echo $file->id; ?>">
			
					<?php 
						echo $file->thumb_image;
						echo '<h4 class="title">'.$file->title.'</h4>';

					 ?>
					</a>
				<?php } ?>
			</div>
	<?php } ?>
		</div>
		<?php 
}

function wpfm_cpt_cloumns(){
	
	//@Fayaz: not properly loclized
	$columns = array(
		'cb' 		=> '<input type="checkbox" />',
		'thumb' 	=> __( 'Thumbnail' ),
		'title' 	=> __( 'Tilte' ),
		'author' 	=> __( 'Author' ),
		'downloads' => __( 'Downloads' ),
		'file_type' => __( 'Type' ),
		'location'	=> __( 'Location' ),
		'file_size' => __( 'size' ),
		'detail'	=> __('Detail'),
		'upload_date'		=> __('Date'),
	);
	
	if(!empty($file->file_meta)){
		$columns['meta'] = __("Meta");
	}
	
	if( wpfm_is_pro_installed() ){
		$columns['taxonomy-file_groups'] = __( 'File Groups' );
	}

	if( wpfm_digital_download_addon_installed() ){
		$columns['price'] = __( 'Price' );
	}

	return apply_filters( 'wpfm_cpt_cloumns', $columns);
}

function wpfm_cpt_columns_data( $column, $post_id ) {
	
	$file = new WPFM_File($post_id);
	switch( $column ) {

		case 'thumb' :

			echo $file->thumb_image;
			
			break;

		case 'author' :
				echo get_post_meta( $post_id, 'wpfm_file_author', true );
			break;

		case 'downloads':
				echo $file->total_downloads;
			break;

		case 'file_type':
			$file_type = wp_check_filetype($file->name);
			
			if ( !$file_type['ext'] ) {
				echo "Directory";	
			}else {
				echo $file_type['ext'];
			};
			break;

		case 'location':
				//@Fayaz: use some icons for local and amazon
				echo $file->location;
			break;
		

		case 'file_size':
				echo $file->size;
			break;

		case 'detail':
			
			wpfm_user_file_model($file);
			echo $file->download_button;
			break;
			
		case 'upload_date':
			echo get_the_date( '', $post_id );
			break;
		case 'price':

				echo $file->price_html;
			break;
	}
}

function wpfm_cpt_columns_sorted($columns) {
	// var_dump($columns);

	$columns['downloads'] 	= 'downloads';
	$columns['file_size'] 	= 'file_size';
	$columns['author'] 		= 'author';
	$columns['upload_date'] = 'upload_date';

	return $columns;
}