<?php
/**
 * Plugin Name: WP Textbox Plugin
 * Plugin URI: http://textbox.vc/
 * Description: A nova plataforma brasileira para gerenciamento de redatores freelancers.
 * Version: 1.0
 * Author: Diogo Bruni
 * Author URI: http://diogobruni.com.br
 */


// don't load directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'WP_TEXTBOX_VERSION', '1.0' );
define( 'WP_TEXTBOX_OPTION', 'wp_textbox_options' );
define( 'WP_TEXTBOX_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_TEXTBOX_URL', plugin_dir_url( __FILE__ ) );


if ( !class_exists("WP_Textbox") ) {

	class WP_Textbox {
		var $settings,
			$options_page;
		
		function __construct() {	

			if ( is_admin() ) {
				// Load example settings page
				if ( !class_exists("WP_Textbox_Settings") ) {
					require( WP_TEXTBOX_DIR . 'wp-textbox-settings.php' );
				}
				$this->settings = new WP_Textbox_Settings();	
			}
			
			add_action('init', array($this,'init') );
			add_action('admin_init', array($this,'admin_init') );
			add_action('admin_menu', array($this,'admin_menu') );
			
			register_activation_hook( __FILE__, array($this,'activate') );
			register_deactivation_hook( __FILE__, array($this,'deactivate') );
		}

		/*
			Propagates pfunction to all blogs within our multisite setup.
			If not multisite, then we just run pfunction for our single blog.
		*/
		function network_propagate($pfunction, $networkwide) {
			global $wpdb;

			if ( function_exists('is_multisite') && is_multisite() ) {
				// check if it is a network activation - if so, run the activation function 
				// for each blog id
				if ( $networkwide ) {
					$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						call_user_func($pfunction, $networkwide);
					}
					switch_to_blog($old_blog);
					return;
				}	
			} 
			call_user_func($pfunction, $networkwide);
		}

		function activate($networkwide) {
			$this->network_propagate(array($this, '_activate'), $networkwide);
		}

		function deactivate($networkwide) {
			$this->network_propagate(array($this, '_deactivate'), $networkwide);
		}

		/*
			Enter our plugin activation code here.
		*/
		function _activate() {}

		/*
			Enter our plugin deactivation code here.
		*/
		function _deactivate() {}
		

		/*
			Load language translation files (if any) for our plugin.
		*/
		function init() {
			load_plugin_textdomain( 'wp_textbox', WP_TEXTBOX_DIR . 'lang', basename( dirname( __FILE__ ) ) . '/lang' );
		}

		function admin_init() {
			/**
			 * Status Code List
			 * 1 - Sucesso
			 * 2 - Token vazio
			 * 3 - Token não configurado
			 * 4 - Token inválido
			 * 5 - Campo(s) obrigatório(s) não preenchido(s)
			 * 6 - Usuário não existe
			 * 7 - Categoria não existe
			 */

			function isTokenValid( $rToken, $arReturn = array() ) {
				$rToken = isset($rToken) && trim($rToken) != '' ? trim($rToken) : false;
				if ( !$rToken ) {
					$arReturn['statusCode'] = 2;
					$arReturn['message'] = __( 'Token em branco!', 'wp_textbox' );
				}

				if ( $arReturn['statusCode'] == 1 ) {
					$pluginOptions = get_option( WP_TEXTBOX_OPTION );
					$pluginToken = isset($pluginOptions['token']) && trim($pluginOptions['token']) != '' ? trim($pluginOptions['token']) : false;

					if ( $pluginToken ) {
						if ( $rToken != $pluginToken ) {
							$arReturn['statusCode'] = 4;
							$arReturn['message'] = __( 'Token inválido!', 'wp_textbox' );
						}
					} else {
						$arReturn['statusCode'] = 3;
						$arReturn['message'] = __( 'Token não configurado!', 'wp_textbox' );
					}
				}

				return $arReturn;
			}

			add_action( 'wp_ajax_nopriv_textbox_get_user_list', 'textbox_get_user_list' );
			function textbox_get_user_list() {
				$arReturn = Array(
					'statusCode' => 1,
					'message' => ''
				);

				$rToken = isset($_POST['token']) && trim($_POST['token']) != '' ? trim($_POST['token']) : false;
				$arReturn = isTokenValid( $rToken, $arReturn );

				if ( $arReturn['statusCode'] == 1 ) {
					$wpUserList = get_users();
					$arReturn['result'] = $wpUserList;
				}

				exit( json_encode($arReturn) );
			}

			add_action( 'wp_ajax_nopriv_textbox_get_category_list', 'textbox_get_category_list' );
			function textbox_get_category_list() {
				$arReturn = Array(
					'statusCode' => 1,
					'message' => ''
				);

				$rToken = isset($_POST['token']) && trim($_POST['token']) != '' ? trim($_POST['token']) : false;
				$arReturn = isTokenValid( $rToken, $arReturn );

				if ( $arReturn['statusCode'] == 1 ) {
					$args = Array(
						'hide_empty' => 0
					);
					$wpPostCategories = get_categories( $args );
					$arReturn['result'] = $wpPostCategories;
				}

				exit( json_encode($arReturn) );
			}

			add_action( 'wp_ajax_nopriv_textbox_add_post', 'textbox_add_post' );
			function textbox_add_post() {
				$arReturn = Array(
					'statusCode' => 1,
					'message' => ''
				);

				$rToken = isset($_POST['token']) && trim($_POST['token']) != '' ? trim($_POST['token']) : false;
				$arReturn = isTokenValid( $rToken, $arReturn );

				$arPostArgs = Array();

				$arEmptyRequiredFields = Array();
				$requiredFields = Array(
					'post_title',
					'post_content',
					'post_author'
				);

				$extraFields = Array(
					'post_name',
					'post_excerpt',
					'post_status',
					'post_date',
					'comment_status',
					'post_category',
					'tags_input'
				);

				foreach( $requiredFields as $requiredField ) {
					if ( !isset( $_POST[ $requiredField ] ) || trim( $_POST[ $requiredField ] ) == '' ) {
						$arReturn['statusCode'] = 5;
						$arEmptyRequiredFields[] = $requiredField;
					} else {
						$arPostArgs[ $requiredField ] = stripslashes( $_POST[ $requiredField ] );
					}
				}

				if ( $arReturn['statusCode'] == 5 ) {
					$auxEmptyFields = implode(',', $arEmptyRequiredFields);
					$arReturn['message'] = __( 'Campos não preenchidos: ', 'wp_textbox' ) . "({$auxEmptyFields})";
				}

				if ( $arReturn['statusCode'] == 1 ) {
					$author = get_user_by( 'id', $_POST['post_author'] );

					if ( !$author ) {
						$arReturn['statusCode'] = 6;
						$arReturn['message'] = __( 'O usuário selecionado não existe!', 'wp_textbox' );
					}
				}

				if ( $arReturn['statusCode'] == 1 ) {
					$arCategoriesNotExists = Array();
					$arCategories = explode( ',', $_POST['post_category'] );
					foreach( $arCategories as $categoryId ) {
						$category = get_category( $categoryId );

						if ( !$category ) {
							$arReturn['statusCode'] = 7;
							$arCategoriesNotExists[] = $categoryId;
						}
					}

					if ( $arReturn['statusCode'] == 7 ) {
						$auxCategoriesNotExists = implode( ',', $arCategoriesNotExists );
						$arReturn['message'] = __( 'Categorias inexistentes: ', 'wp_textbox' ) . "({$auxCategoriesNotExists})";
					}
				}

				if ( $arReturn['statusCode'] == 1 ) {
					foreach( $extraFields as $extraField ) {
						if ( isset( $_POST[ $extraField ] ) && trim( $_POST[ $extraField ] ) != '' ) {
							$arPostArgs[ $extraField ] = stripslashes( $_POST[ $extraField ] );
						}
					}

					if ( isset( $arPostArgs['post_category'] ) ) {
						$arPostArgs['post_category'] = explode( ',', $arPostArgs['post_category'] );
					}

					if ( isset( $arPostArgs['tags_input'] ) ) {
						$arPostArgs['tags_input'] = explode( ',', $arPostArgs['tags_input'] );
					}

					$postId = wp_insert_post( $arPostArgs );

					if ( $postId ) {
						$arReturn['id_post'] = $postId;

						$postContent = $arPostArgs['post_content'];
						$images = false;
						preg_match_all( "/<img.+?src=[\"'](.+?)[\"'].*?>/", $postContent, $images );

						$replacedImage = 0;

						if ( count( $images ) > 1 ) {
							$arInsertedImages = Array();
							foreach( $images[1] as $imageSrc ) {
								$newImageSrc = '';
								if ( $imageSrc && !in_array( $imageSrc, $arInsertedImages ) ) {
									$newImageSrc = media_sideload_image( 'http://textbox.vc/' . $imageSrc, $postId, null, 'src' );
									if ( $newImageSrc ) {
										$arInsertedImages[] = $imageSrc;
										$replacedImage++;
										$postContent = str_replace( $imageSrc, $newImageSrc, $postContent );
									}
								}
							}
						}

						if ( $replacedImage ) {
							$arUpdatePost = Array(
								'ID' => $postId,
								'post_content' => $postContent
							);
							wp_update_post( $arUpdatePost );
						}

					} else {
						$arReturn['statusCode'] = 8;
						$arReturn['message'] = __( 'Ocorreu um erro ao inserir o post no WordPress.', 'wp_textbox' );
						$arReturn['error'] = $postId;
					}
				}

				exit( json_encode($arReturn) );
			}
		}

		function admin_menu() {
		}

	} // end class
}


// Initialize our plugin object.
global $wp_textbox;
if ( class_exists("WP_Textbox") && !$wp_textbox ) {
    $wp_textbox = new WP_Textbox();	
}
?>