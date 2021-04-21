<?php
/**
 * Plugin Name: WP File Manager
 * Plugin URI: https://najeebmedia.com/
 * Description: WordPress File Manager Plugin allow members and guest users to upload and manage their files on front-end.
 * Version: 17.0
 * Author: N-Media
 * Author URI: najeebmedia.com
 * Text Domain: wpfm
 * License: GPL2
 */

 if( ! defined('ABSPATH' ) ){
	exit;
}

define( 'WPFM_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define( 'WPFM_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'WPFM_SHORT_NAME', 'wpfm' );
define( 'WPFM_USER_UPLOADS', 'user_uploads' );
define( 'WPFM_VERSION', '17.0' );

/* ======= plugin includes =========== */
if( file_exists( dirname(__FILE__).'/inc/arrays.php' )) include_once dirname(__FILE__).'/inc/arrays.php';
if( file_exists( dirname(__FILE__).'/inc/helpers.php' )) include_once dirname(__FILE__).'/inc/helpers.php';
if( file_exists( dirname(__FILE__).'/inc/cpt.php' )) include_once dirname(__FILE__).'/inc/cpt.php';
if( file_exists( dirname(__FILE__).'/inc/hooks.php' )) include_once dirname(__FILE__).'/inc/hooks.php';

if( file_exists( dirname(__FILE__).'/inc/wpfm.inputs.php' )) include_once dirname(__FILE__).'/inc/wpfm.inputs.php';
if( file_exists( dirname(__FILE__).'/inc/shortcode.php' )) include_once dirname(__FILE__).'/inc/shortcode.php';
if( file_exists( dirname(__FILE__).'/inc/callback-functions.php' )) include_once dirname(__FILE__).'/inc/callback-functions.php';
if( file_exists( dirname(__FILE__).'/inc/files.php' )) include_once dirname(__FILE__).'/inc/files.php';
if( file_exists( dirname(__FILE__).'/inc/file-detail.php' )) include_once dirname(__FILE__).'/inc/file-detail.php';
if( file_exists( dirname(__FILE__).'/inc/classes/class.fields.php' )) include_once dirname(__FILE__).'/inc/classes/class.fields.php';
if( file_exists( dirname(__FILE__).'/inc/file.class.php' )) include_once dirname(__FILE__).'/inc/file.class.php';
if( file_exists( dirname(__FILE__).'/inc/file.class.legacy.php' )) include_once dirname(__FILE__).'/inc/file.class.legacy.php';

if( file_exists( dirname(__FILE__).'/inc/classes/class.meta.php' )) include_once dirname(__FILE__).'/inc/classes/class.meta.php';
if( file_exists( dirname(__FILE__).'/inc/classes/class.email.php' )) include_once dirname(__FILE__).'/inc/classes/class.email.php';

if( file_exists( dirname(__FILE__).'/inc/admin.php' )) include_once dirname(__FILE__).'/inc/admin.php';
if( file_exists( dirname(__FILE__).'/inc/migrate.php' )) include_once dirname(__FILE__).'/inc/migrate.php';
if( file_exists( dirname(__FILE__).'/inc/deactivate.class.php' )) include_once dirname(__FILE__).'/inc/deactivate.class.php';



class WPFM {

	/**
	 * this holds all input objects for file meta
	 */
	var $inputs;


	function __construct(){
		
		add_action ( 'init', array (
				$this,
				'init_plugin' 
		) );
		
		// ============ GuturnBug Block=========== 
	    add_action( 'init', array($this, 'register_file_manager') );
	    
	    
	    // add_action( 'trashed_post', 'wpfm_admin_delete_files', 99 ,1 );
	    add_action( 'before_delete_post', 'wpfm_admin_delete_files', 99 );


		// ============ ADMIN =========== 
		add_action ( 'admin_menu', 'wpfm_admin_add_menu_pages' );
		
		add_filter( 'admin_url', array($this, 'wpfm_change_add_new_link'), 10, 2 );
		
		add_action('admin_menu', array($this, 'hide_new_file_menu_cpt'));
		
		// Adding download id in query var
        add_filter( 'query_vars', array($this, 'add_query_var' ));
		
		// admin secripts 
		add_action( 'admin_enqueue_scripts', 'wpfm_admin_load_scripts');
		
		//block action
		add_action( 'enqueue_block_editor_assets', 'wpfm_admin_load_block_js');
	
		
		// on Page save add meta value 'wpfm-found = upload/download'
		add_action( 'save_post', array($this, 'save_page' ), 10, 3 );

		// post realted actions 
		add_action( 'wpfm_after_directory_post_saved', 'wpfm_hooks_after_dir_saved', 10, 2);
		add_action( 'wpfm_after_file_post_save', 'wpfm_hooks_after_file_saved', 10, 3);
		add_action( 'wpfm_file_meta_saving', 'wpfm_hooks_file_meta_save', 10, 1);
		add_action( 'wpfm_after_all_files_post_save', 'wpfm_hooks_send_notification', 10, 2);
		add_action( 'wpfm_after_all_files_post_save', 'wpfm_user_upload_files_counter', 10, 2);
		add_filter( 'wpfm_uploaded_filename', 'wpfm_hook_rename_file', 10, 1);
		
		// File detail from list in columns
        add_filter( 'manage_edit-wpfm-files_columns', 'wpfm_cpt_cloumns' ) ;
		add_action( 'manage_wpfm-files_posts_custom_column', 'wpfm_cpt_columns_data', 10, 2 );
		add_filter( 'manage_edit-wpfm-files_sortable_columns', 'wpfm_cpt_columns_sorted' );
		
		// Log out link
		add_filter( 'wpfm_top_menu', 'wpfm_hooks_logout_link_nav_bar', 99, 1);
		
		//preventing to not generate thumbs for images
		add_filter('intermediate_image_sizes_advanced', array($this, 'prevent_thumbs_generation'));

		
		$this -> inputs = self::get_all_inputs();
		
		
		// ============ Local Hooks ===============
		add_filter( 'wpfm_wp_files_query', 'wpfm_hooks_update_query', 10, 2);

		wpfm_hooks_do_callbacks();
		 
	}
	
	function wpfm_change_add_new_link($url, $path){
	
		if( $path === 'post-new.php?post_type=wpfm-files' ) {
	        // $url = 'https://nmdevteam.com/wp1/wp-admin/edit.php?post_type=wpfm-files&page=wpfm-settings'; // or any other url
	        $url = menu_page_url('wpfm-addnew', false);
	    }
    	return $url;
	}
	
	function hide_new_file_menu_cpt(){
		global $submenu;
	    unset($submenu['edit.php?post_type=wpfm-files'][10]);
	}
	
	function register_file_manager() {
       if (function_exists('register_block_type')) {
           $settings = array();
           $settings['render_callback'] =  'wpfm_shortcode_render';
           $settings['attributes'] = array('id' => array('type' => 'string'));
           register_block_type( 'block/file-manager', $settings );
           
            // wpfm_check_expire_date_files();
       }
    }
	
	function get_all_inputs() {

		$wpfm_inputs = WPFM_Inputs();

		$meta_inputs = array (
				
			'text' 		=> $wpfm_inputs->get_input ( 'text' ),
			'email' 	=> $wpfm_inputs->get_input ( 'email' ),
			'date' 		=> $wpfm_inputs->get_input ( 'date' ),
			'textarea' 	=> $wpfm_inputs->get_input ( 'textarea' ),
			'select' 	=> $wpfm_inputs->get_input ( 'select' ),
			'radio' 	=> $wpfm_inputs->get_input ( 'radio' ),
			'checkbox' 	=> $wpfm_inputs->get_input ( 'checkbox' ),
		);

		return apply_filters('wpfm_meta_inputs', $meta_inputs);
	}
	
	function add_query_var( $qvars ) {
        
        $qvars[] = 'download_id';
        $qvars[] = 'group_id';
        $qvars[] = 'wpfm_uploading';
        return $qvars;
    }
    
    function save_page($page_id, $post, $update){
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        $post_type = get_post_type($page_id);

	    // If this isn't a 'page' post, don't update it.
	    if ( "page" != $post_type ) return;
        // if find the shortcode then updata meta value
        if( has_shortcode( $post->post_content, 'wpfm-downloads') ) {


			update_post_meta($page_id, 'wpfm_found', 'download' );
		}

		if ( has_shortcode( $post->post_content, 'nm-wp-file-uploader') ) {

			update_post_meta($page_id, 'wpfm_found', 'upload' );
		}
		
    }
    
    
    function prevent_thumbs_generation($sizes){
	 	
	 	$is_upload_page = get_query_var('wpfm_uploading');
	 	if($is_upload_page){
	 		
	 		foreach(get_intermediate_image_sizes() as $size) {
	 			
	 			if( isset($sizes[$size]) ) {
	 				
		 			unset( $sizes[$size] );	
	 			}
	 		}
	 	}
	 	
        return $sizes;
	 }
	
	function init_plugin() {
		
		// Shortcode
		add_shortcode ( 'nm-wp-file-uploader', 'wpfm_shortcode_render' );
		// remove_post_type_support( 'wpfm-files', 'post-formats' );
		
		load_plugin_textdomain('wpfm', false, basename( dirname( __FILE__ ) ) . '/languages');
		
		// Register CPT
		wpfm_cpt_register_post_type();
		// add_action( 'add_meta_boxes', 'admin_file_upload_metaboxes');

		
		// sniff file download
		wpfm_file_download();
		
		wpfm_digital_file_download();
	}
}

add_action('plugins_loaded', 'wpfm');
// lets start plugin
function wpfm() {

	return new WPFM();
}

/*
 * activation/install the plugin data
*/
register_activation_hook( __FILE__, 'wpfm_activate_plugin' );
register_deactivation_hook( __FILE__, 'wpfm_deactivate_plugin' );
function wpfm_activate_plugin() {
	
	wpfm_migrate();
}

function wpfm_deactivate_plugin() {
	
	// Sleep
}