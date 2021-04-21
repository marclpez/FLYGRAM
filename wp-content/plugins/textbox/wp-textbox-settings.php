<?php
// don't load directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !class_exists("WP_Textbox_Settings") ) {

	class WP_Textbox_Settings {

		public static $default_settings = Array( 	
		  	'token' => ''
		);
		var $pagehook, $page_id, $settings_field, $options;

		
		function __construct() {	
			$this->page_id = 'wp_textbox';
			// This is the get_options slug used in the database to store our plugin option values.
			$this->settings_field = WP_TEXTBOX_OPTION;
			$this->options = get_option( $this->settings_field );

			add_action('admin_init', array($this,'admin_init'), 20 );
			add_action( 'admin_menu', array($this, 'admin_menu'), 20);
		}
		
		function admin_init() {
			register_setting( $this->settings_field, $this->settings_field, array($this, 'sanitize_theme_options') );
			add_option( $this->settings_field, WP_Textbox_Settings::$default_settings );
			
			
			/* 
				This is needed if we want WordPress to render our settings interface
				It sets up different sections and the fields within each section.
			*/
			add_settings_section('textbox_main', '', array($this, 'main_section_text'), 'textbox_settings_page');

			add_settings_field('token', 'Token', array($this, 'render_token_text'), 'textbox_settings_page', 'textbox_main');
		}

		function admin_menu() {
			if ( ! current_user_can('update_plugins') )
				return;
		
			// Add a new submenu to the standard Settings panel
			$this->pagehook = $page =  add_options_page( __('Textbox', 'wp_textbox'), __('Textbox', 'wp_textbox'), 'administrator', $this->page_id, array($this,'render') );
			
			// Executed on-load. Add all metaboxes.
			add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

			// Include js, css, or header *only* for our settings page
			add_action("admin_print_scripts-$page", array($this, 'js_includes'));
			//add_action("admin_print_styles-$page", array($this, 'css_includes'));
			add_action("admin_head-$page", array($this, 'admin_head') );
		}

		function admin_head() { ?>
			<style>
				.settings_page_wp_textbox label { display:inline-block; width: 150px; }
			</style>
		<?php }

	     
		function js_includes() {
			// Needed to allow metabox layout and close functionality.
			wp_enqueue_script( 'postbox' );
		}

		function css_includes() {
			// Include your css files here with wp_enqueue_style()
		}


		/*
			Sanitize our plugin settings array as needed.
		*/	
		function sanitize_theme_options($options) {
			$options['token'] = stripcslashes($options['token']);
			return $options;
		}


		/*
			Settings access functions.
		*/
		protected function get_field_name( $name ) {

			return sprintf( '%s[%s]', $this->settings_field, $name );

		}

		protected function get_field_id( $id ) {

			return sprintf( '%s[%s]', $this->settings_field, $id );

		}

		protected function get_field_value( $key ) {

			return $this->options[$key];

		}
			

		/*
			Render settings page.
		*/
		
		function render() {
			global $wp_meta_boxes;

			$title = __('Wordpress Textbox', 'wp_textbox');
			?>
			<div class="wrap">   
				<h2><?php echo esc_html( $title ); ?></h2>
			
				<form method="post" action="options.php">

	                <div class="metabox-holder">
	                    <div class="postbox-container" style="width: 99%;">
	                    <?php 
							// Render metaboxes
	                        settings_fields($this->settings_field); 
	                        do_meta_boxes( $this->pagehook, 'main', null );
	                      	if ( isset( $wp_meta_boxes[$this->pagehook]['column2'] ) )
	 							do_meta_boxes( $this->pagehook, 'column2', null );
	                    ?>
	                    </div>
	                </div>

					<p>
						<input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e( 'Salvar', 'wp_textbox' ); ?>" />
					</p>
				</form>
			</div>
	        
	        <!-- Needed to allow metabox layout and close functionality. -->
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function ($) {
					// close postboxes that should be closed
					jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
				});
				//]]>
			</script>
		<?php }
		
		
		function metaboxes() {
			//add_meta_box( 'textbox-version', __( 'Informações', 'wp_textbox' ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );
			add_meta_box( 'textbox-all', __( 'Configurações Textbox', 'wp_textbox' ), array( $this, 'do_settings_box' ), $this->pagehook, 'main' );

		}

		function info_box() { ?>
			<p>
				<strong><?php _e( 'Versão:', 'wp_textbox' ); ?></strong> <?php echo WP_TEXTBOX_VERSION; ?>
			</p>
		<?php }


		function do_settings_box() {
			do_settings_sections('textbox_settings_page'); 
		}
		
		/* 
			WordPress settings rendering functions
		*/
		function main_section_text() {
			echo __( 'Configuração de Autenticação', 'wp_textbox' );
		}
		
		function render_token_text() { 
			?>
	        <input id="token" style="width:50%;"  type="text" name="<?php echo $this->get_field_name( 'token' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'token' ) ); ?>" />	
			<?php 
		}
		

	} // end class
}
?>