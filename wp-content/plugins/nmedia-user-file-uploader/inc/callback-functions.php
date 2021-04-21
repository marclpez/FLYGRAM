<?php

 if( ! defined('ABSPATH' ) ){
	exit;
}


/**
 * 
 * move file/directory into other directory
 * 
 * @since 11.6
 **/
	
function nm_uploadfile_move_file() {

	if (isset($_REQUEST)) {

	    $result  = array(
	        'ID' => intval( $_REQUEST['file_id'] ), 
	        'post_parent' => intval( $_REQUEST['parent_id'] )
	    );

	    $post_id = wp_update_post( $result, true );
	
	}		
	
	if($result){
		_e('File is move successfully', 'nm-filemanager');
	} else {
		_e('Error while moveing file, please try again.', 'nm-filemanager');
	}
		
	die(0);
		
}

/*
 * Edit file title and description
 */
function wpfm_edit_file_title_desc(){
	
	if (isset($_REQUEST)) {
		$file = array(
			'ID'           => esc_attr($_REQUEST['file_id']),
			'post_title'   => esc_attr($_REQUEST['file_title']),
			'post_content' => esc_attr($_REQUEST['file_content']),
		);
		$post_id = wp_update_post( $file, true );
		// var_dump($post_id);
		// Update the post into the database
		if( $post_id != 0 ) {

			update_post_meta( $post_id,'wpfm_title', $_REQUEST['file_title']);
			_e('File updated', 'wpfm');
		}
		else
			_e('Error while updating file, try again', 'wpfm');
	}
	
	die(0);
}


// sending file in email
function wpfm_send_file_in_email() {
	
	$file_id = isset($_REQUEST['file_id']) ? $_REQUEST['file_id'] : '';
	
	
	$file = new WPFM_File($file_id);
	
	if( empty($_POST['subject']) || empty($_POST['emailaddress']) ) {
		
		$response = array('message'=>__('Email or Subject not given.','wpfm'),'status'=>'error');
		wp_send_json( $response );
	}
	
	$message		= '';
	$message		.= $_POST['message'];
	$message		.= '<br><hr>';
	
	
    $file_hash = $file->add_file_hash();
    
    $download_url	= add_query_arg('file_hash',$file_hash, $file->download_url);
    
	$message		.= sprintf(__('<a href="%s">Download %s</a>','wpfm'), esc_url($download_url), $file->title );
	
	$context = 'send-file';
	$email = new WPFM_Email($file_id, $context);
	$email->to		= $_POST['emailaddress'];
	$email->subject = $_POST['subject'];
	$email->message	= $message;
	
	// send
	$email->send();
	
	$response = array('message'=>__('File is shared successfully','wpfm'),'status'=>'success');
	
	wp_send_json( $response );
}