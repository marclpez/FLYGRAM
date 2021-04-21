<?php
/**
 * Store Locator Plus manage locations admin / info tab.
 *
 * @property-read   boolean $cache_expired
 * @property-read   array   $settings_groups    The group_params for the various settings groups.
 */
class SLP_Admin_Info extends SLP_Object_With_Objects {
	private $cache_expired = true;
	private $group_params;
	private $settings_groups;

	protected $objects = array(
		'Admin_Info_Text' => array(
			'auto_instantiate' => true,
			'subdir'           => 'include/module/admin_tabs/',
		),
		'Settings'        => array(
			'auto_instantiate' => true,
			'subdir'           => 'include/module/settings/',
		),
	);

	/**
	 * Things we do at the start.
	 */
	function initialize() {
		parent::initialize();
		$this->group_params = array( 'plugin' => $this->slplus, 'section_slug' => null, 'group_slug' => null );
	}

	/**
	 * Add the How To Use subtab.
	 *
	 * @param   SLP_Settings $settings
	 */
	public function add_how_to_use_subtab( $settings ) {
		$this->group_params['section_slug'] = 'how_to_use';
		$settings->add_section( $this->group_params );

		$this->add_how_to_use_left_side();

		if ( ! defined( 'MYSLP_VERSION' ) ) {
			$this->add_how_to_use_right_side();
		}
	}

	/**
	 * Add How To Use Left
	 */
	private function add_how_to_use_left_side() {
		$this->settings_groups['how_to_use_left'] = array(
			'header'       => __( 'Initial Setup', 'store-locator-le' ),
			'group_slug'   => 'initial_setup',
			'section_slug' => 'how_to_use',
			'div_group'    => 'left_side',
			'plugin'       => $this->slplus,
		);
		$this->Settings->add_group( $this->settings_groups['how_to_use_left'] );

		$this->Settings->add_ItemToGroup( array(
				'group_params' => $this->settings_groups['how_to_use_left'],
				'type'         => 'details',
				'custom'       => $this->create_string_how_to_use_left_content(),
			)
		);
	}

	/**
	 * Add How To Use Right
	 */
	private function add_how_to_use_right_side() {
		$this->settings_groups['how_to_use_right'] = array(
			'header'       => __( 'Latest News', 'store-locator-le' ),
			'group_slug'   => 'latet_news',
			'section_slug' => 'how_to_use',
			'div_group'    => 'right_side',
			'plugin'       => $this->slplus,
		);
		$this->Settings->add_group( $this->settings_groups['how_to_use_right'] );

		$this->Settings->add_ItemToGroup( array(
				'group_params' => $this->settings_groups['how_to_use_right'],
				'type'         => 'details',
				'custom'       => $this->create_string_how_to_use_right_content(),
			)
		);
	}

	/**
	 * Add the Plugin Environment subtab.
	 *
	 * @param   SLP_Settings $settings
	 */
	public function add_plugin_environment_subtab( $settings ) {

		$this->group_params['section_slug'] = 'plugin_environment';
		$settings->add_section( $this->group_params );

		$this->group_params['group_slug'] = 'details';
		$settings->add_ItemToGroup( array(
				'group_params' => $this->group_params,
				'type'         => 'details',
				'custom'       => $this->create_EnvironmentPanel(),
			)
		);

	}

	/**
	 * Set our object options.
	 */
	protected function set_default_object_options() {
		$this->objects['Settings']['options'] = array(
			'name'              => __( 'Info', 'store-locator-le' ),
			'form_action'       => '',
			'save_text'         => '',
			'show_help_sidebar' => false,
		);
	}

	/**
	 * Create Environment Panel
	 */
	private function create_EnvironmentPanel() {

		// Add ON Packs
		//
		$addonStr = '';
		if ( isset( $this->slplus->AddOns ) ) {
			if ( ! $this->cache_expired ) {
				$new_versions = get_option( 'slplus_addon_versions' );
			} else {
				$new_versions = array();
			}

			foreach ( $this->slplus->AddOns->instances as $addon => $instantiated_addon ) {
				if ( strpos( $addon, 'slp.' ) === 0 ) {
					continue;
				}

				if ( $this->cache_expired ) {
					if ( isset( $instantiated_addon ) ) {
						$new_versions[ $instantiated_addon->name ] = $instantiated_addon->admin->get_newer_version();
					}
				}

				$newest_version = isset ( $new_versions[ $instantiated_addon->name ] ) ? $new_versions[ $instantiated_addon->name ] : '';

				$version =
					! is_null( $instantiated_addon ) && method_exists( $instantiated_addon, 'get_meta' ) ?
						$instantiated_addon->get_meta( 'Version' ) :
						'active';

				// If update is available, report it.
				//
				if ( $instantiated_addon != null ) {
					if ( ! empty( $newest_version ) && version_compare( $version, $newest_version, '<' ) ) {
						$url       = $instantiated_addon->get_meta( 'PluginURI' );
						$link_text = '<strong>' . sprintf( __( 'Version %s in production ', 'store-locator-le' ), $newest_version ) . '</strong>';
						$version .= ' ' . sprintf( '<a href="%s">%s</a> ', $url, $link_text );

					}

					if ( ! empty( $version ) ) {
						/**
						 * FILTER: slp_version_report_<addon_slug>
						 *
						 * Allow add ons to refine the version info, for example reporting active modules.
						 *
						 * @param string $version
						 *
						 * @return string           modified version info
						 */
						$version = apply_filters( 'slp_version_report_' . $instantiated_addon->short_slug, $version );
						$addonStr .= $this->create_EnvDiv( $instantiated_addon->name, $version );
					}
				}
			}

			// Cache was expired.
			//
			if ( $this->cache_expired ) {
				update_option( 'slplus_addon_versions', $new_versions );
			}

		}

		/** @var wpdb $wpdb */
		global $wpdb;
		$my_metadata = get_plugin_data( SLPLUS_FILE );

		$html =
			'<div class="left_side">' .
			'<div class="content_pad">' .
			$this->create_EnvDiv( $my_metadata['Name'], $my_metadata['Version'] ) .
			$addonStr .
			'<br/>' .
			$this->create_EnvDiv( __( 'Site URL', 'store-locator-le' ), get_option( 'siteurl' ) ) .
			$this->create_EnvDiv( __( 'This Info Cached', 'store-locator-le' ), $this->slplus->options_nojs['broadcast_timestamp'] ) .
			$this->create_EnvDiv( __( 'Network Active', 'store-locator-le' ), is_plugin_active_for_network( $this->slplus->slug ) ? __( 'Yes', 'store-locator-le' ) : __( 'No', 'store-locator-le' ) ) .
			'<br/>' .
			$this->create_EnvDiv( __( 'WordPress Version', 'store-locator-le' ), $GLOBALS['wp_version'] ) .
			$this->create_EnvDiv( __( 'PHP Version', 'store-locator-le' ), phpversion() ) .
			$this->create_EnvDiv( __( 'MySQL Version', 'store-locator-le' ), $wpdb->db_version() ) .
			'<br/>' .

			$this->create_EnvDiv(
				__( 'PHP Limit', 'store-locator-le' ),
				ini_get( 'memory_limit' )
			) .

			$this->create_EnvDiv(
				__( 'WordPress General Limit', 'store-locator-le' ),
				WP_MEMORY_LIMIT
			) .

			$this->create_EnvDiv(
				__( 'WordPress Admin Limit', 'store-locator-le' ),
				WP_MAX_MEMORY_LIMIT
			) .

			$this->create_EnvDiv( __( 'PHP Peak RAM', 'store-locator-le' ),
				sprintf( '%0.2d MB', memory_get_peak_usage( true ) / 1024 / 1024 ) ) .

			$this->create_EnvDiv(
				__( 'PHP Post Max Size', 'store-locator-le' ),
				ini_get( 'post_max_size' )
			) .
			'</div>' .
			'</div>';

		return $html;
	}

	/**
	 * Create a plugin environment div.
	 *
	 * @param string $label
	 * @param string $content
	 *
	 * @return string
	 */
	private function create_EnvDiv( $label, $content ) {
		return "<p class='envinfo'><span class='label'>{$label}:</span>{$content}</p>";
	}

	/**
	 * Show the box to enter the Google Maps API Key.
	 */
	private function create_string_enter_api_key() {
		$this->Settings->add_ItemToGroup( array(
			'group_params' => $this->settings_groups['how_to_use_left'],
			'option'       => 'google_server_key',
			'onChange'     => 'SLP_ADMIN.options.change_option(this);',
		) );
	}

	/**
	 * Get a google key text.
	 */
	private function create_string_get_a_google_key() {
		if ( defined( 'MYSLP_VERSION' ) && ! is_main_site() )  return '';
		if ( ! empty( $this->slplus->SmartOptions->google_server_key->value ) ) return '';

		$this->create_string_enter_api_key();

		return
			'<h4>' . __( 'Get A Google API Key', 'store-locator-le' ) . '</h4>' .

			$this->slplus->Text->get_web_link( 'get_started_google_key' ) .

			'<p>' .

			sprintf(
				__( 'Before you get started you will need to get a <a href="%s">Google Maps Standard API Key</a>. ', 'store-locator-le' ),
				'https://developers.google.com/maps/documentation/javascript/get-api-key'
			) .

			__( 'Enter the Google Browser Key field above. ', 'store-locator-le' ) .

			sprintf(
				__( 'You can change it via the Google Browser Key field under the <a href="%s">General / Server tab</a> in the Google Developers Console panel. ', 'store-locator-le' ),
				admin_url() . 'admin.php?page=slp_general'
			) .

			'</p>';
	}

	/**
	 * The how to add location text.
	 */
	private function create_string_how_to_add_location() {
		return
			'<h4>' . __( 'Add A Location', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			sprintf(
				__( 'Add a location or two via the <a href="%s">Add Location form</a>.', 'store-locator-le' ),
				admin_url() . 'admin.php?page=slp_manage_locations'
			) .

			sprintf( __( 'You will find this link in the left sidebar under the %s entry. ', 'store-locator-le' ) , SLPLUS_NAME ) .

			$this->slplus->Text->get_web_link( 'import_suggestion' ) .
			'</p>';
	}

	/**
	 * Using MySLP
	 */
	private function create_string_using_myslp() {
		if ( defined( 'MYSLP_VERSION' ) ) return;

		return
			'<h4>' . __( 'MySLP Service versus WordPress Plugins', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			sprintf( __( 'Store Locator Plus® WordPress plugins are NOT the same thing as our <a href="%s">MySLP</a> SaaS service. ' , 'store-locator-le' ), $this->slplus->url_main_slp_site ) .

			__( 'With the WordPress plugin you must manage your own software updates and Google API keys. ' , 'store-locator-le' ).

			__( 'Google now requires an API key for all products using their map software. ' , 'store-locator-le' ) .

			__( 'They will bill you directly for every map view and location geocoding request. ' , 'store-locator-le' ) .

			'</p><p>' .

			sprintf( __( 'Your login to the <a href="%s">WordPress Store Locator Plus plugin store</a> is NOT the same thing as a MySLP subscription. ' , 'store-locator-le' ) , $this->slplus->slp_store_url ).

			__( 'None of our WordPress plugins come with "pre-purchased" map views or geocoding requests. ' , 'store-locator-le' ).

			__( 'Even our Premier Subscription members still need to pay Google for drawing maps or adding coordinates to your locations. ' , 'store-locator-le' ).

			'</p><p>' .

			sprintf( __( '<a href="%s">MySLP</a> is service where you manage your locations and settings on our server. ', 'store-locator-le' ), $this->slplus->url_main_slp_site ) .

			__( 'With MySLP, we deal with Google and their API keys and crazy new billing system. ' , 'store-locator-le' ) .

			__( 'If you signed up for a MySLP account you should not be using this, or any other, Store Locator Plus® WordPress plugins. ' , 'store-locator-le' ) .

			'</p><p>' .

			__( 'If you have a MySLP account, delete the Store Locator Plus® plugins, login to your MySLP account and copy the embed code from Generate Embed. ' , 'store-locator-le' ).

			__( 'Come back to this site, go to a page or post, switch to text editing mode and paste the embed code into your content. ' , 'store-locator-le' ).

			'</p><p>' .

			__( 'Your map will appear.  No software to install, plugins to maintain, Google keys to manage or direct billing from Google. ' , 'store-locator-le' ) .

			__( 'Sound better than what you are doing now with this WordPress plugin? ' , 'store-locator-le' ) .

			sprintf( __( 'Signup for a <a href="%s">MySLP</a> account today and stop fooling with this Google key nonsense and get back to doing things that matter. ' , 'store-locator-le' ) , $this->slplus->url_main_slp_site ) .

			'</p>';
	}

	/**
	 * The how to create a locations page info.
	 *
	 * @return string
	 */
	private function create_string_how_to_create_page() {
		return
			'<h4>' . __( 'Create A Page', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			__( 'Go to the sidebar and select "Add New" under the pages section.  You will be creating a standard WordPress page. ', 'store-locator-le' ) .

			sprintf(
				__( 'On that page add the [SLPLUS] <a href="%s" target="slplus">shortcode</a>.  When a visitor goes to that page it will show a default search form and a Google Map.', 'store-locator-le' ),
				$this->slplus->support_url . '/blog/slplus-shortcode/'
			) .

			__( 'When someone searches for a zip code that is close enough to a location you entered it will show those locations on the map. ', 'store-locator-le' ) .

			'</p>';
	}

	/**
	 * Create a link list with header.
	 *
	 * @param $header
	 * @param $links
	 *
	 * @return string
	 */
	private function create_link_list_with_header( $header, $links ) {

		$classname = sanitize_title_with_dashes( $header );

		$link_list = '';
		foreach ( $links as $slug => $entry ) {
			$link_list .= sprintf( '<li><a href="%s" target="store_locator_plus" title="%s">%s</a></li>', $entry['url'], $entry['text'], $entry['text'] );
		}

		return
			"<div class='slp_info link_list {$classname}'>" .
			"<h4>{$header}</h4>" .
			"<ul>{$link_list}</ul>" .
			'</div>';
	}

	/**
	 * The text for the news feed from SLP.
	 */
	private function create_string_news_feed() {
		$rss = fetch_feed( $this->slplus->url_main_slp_site. '/feed/' );

		$item_quantity = 0;

		if ( ! is_wp_error( $rss ) ) {
			$item_quantity = $rss->get_item_quantity( 5 );
			$rss_items     = $rss->get_items( 0, $item_quantity );
		}

		if ( $item_quantity == 0 ) {
			$news = '';

		} else {
			$news = '';
			foreach ( $rss_items as $item ) {
				$title = esc_html( $item->get_title() );
				$news .=
					sprintf( '<li class="news_feed_item"><a href="%s" title="%s" target="slp">%s</a><span class="news_datetime">%s</span></li>',
						esc_url( $item->get_permalink() ),
						$title,
						$title,
						$item->get_date( 'j F Y | g:i a' )
					);
			}
			$news = '<ul class="news_feed_list">' . $news . '</ul>';
		}

		return $news;
	}

	/**
	 * Create the social media icon array.
	 */
	private function create_string_social_outlets() {
		$html =
			$this->slplus->Text->get_web_link( 'icon_for_documentation' ) .
			$this->slplus->Text->get_web_link( 'icon_for_twitter' ) .
			$this->slplus->Text->get_web_link( 'icon_for_youtube' ) .
			$this->slplus->Text->get_web_link( 'icon_for_rss' )
			;

		return
			'<h4>' . __( 'Connect On Social Media', 'store-locator-le' ) . '</h4>' .
			$html;
	}

	/**
	 * How to tweak settings text.
	 *
	 * @return string
	 */
	private function create_string_how_to_tweak_settings() {
		return
			'<h4>' . __( 'Tweak The Settings', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			sprintf(
				__( 'You can modify basic settings such as the options shown on the radius pull down list on the <a href="%s">Experience</a> page. ', 'store-locator-le' ),
				admin_url() . 'admin.php?page=slp_experience'
			) .

			$this->slplus->Text->get_web_link( 'more_options_suggestion' ) .
			'</p>';
	}

	/**
	 * Create the YouTube iFrame and div.
	 *
	 * @return string
	 */
	private function create_string_how_to_video() {
		$video_url = set_url_scheme( 'https://www.youtube.com/embed/b51J1ay7fyk' );

		return
			'<div style="text-align:center; margin: 0px auto 3em;">' .
			"<iframe width='560' height='315' src='{$video_url}' frameborder='0' allowfullscreen></iframe>" .
			'</div>';
	}

	/**
	 * Help us translate.
	 *
	 * @return string
	 */
	private function create_string_more_help() {
		$links = array(
			array(
				'text' => __( 'Getting Started', 'store-locator-le' ),
				'url'  => $this->slplus->support_url . '/blog/getting-started/',
			),
			array(
				'text' => __( 'Adding Locations', 'store-locator-le' ),
				'url'  => $this->slplus->support_url . '/blog/adding-locations/',
			),
			array(
				'text' => sprintf( __( 'Translating %s', 'store-locator-le' ) , SLPLUS_NAME) ,
				'url'  => $this->slplus->support_url . '/blog/translating-store-locator-plus/',
			),
		);

		return $this->create_link_list_with_header( __( 'Documentation To Get You Started', 'store-locator-le' ), $links );
	}

	/**
	 * Render the How To Use text.
	 *
	 * @return string
	 */
	private function create_string_how_to_use_left_content() {
		$html = '';

		if ( ! defined( 'MYSLP_VERSION' ) ) {
			$html .= '<strong>' .
			         __( 'Tired of dealing with Google API keys? ', 'store-locator-le' ) . '<br/>' .
					 __( 'Are you a MySLP User? ', 'store-locator-le' )  . '<br/>' .
			         '</strong>' .
			         __( 'Look at our note over to the right -->', 'store-locator-le' )
			         ;
		}

		$html .= $this->create_string_more_help();

		$html .= $this->create_string_get_a_google_key();

		$html .= $this->create_string_how_to_add_location();

		if ( ! defined( 'MYSLP_VERSION' ) ) {
			$html .= $this->create_string_how_to_create_page();
		}

		$html .= $this->create_string_how_to_tweak_settings();

		$html .= $this->create_string_how_to_video();

		$html .= apply_filters( 'slp_how_to_use', '' );

		return '<div class="content_pad how_to_use">' . $html . '</div>';
	}

	/**
	 * Render How To use right side content.
	 */
	private function create_string_how_to_use_right_content() {
		return '<div class="content_pad">' .
		       $this->create_string_news_feed() .
		       $this->create_string_using_myslp() .
		       $this->create_string_social_outlets() .
		       '</div>';

	}

	/**
	 *  Setup and render the info tab.
	 */
	function display() {
		$this->cache_expired = $this->is_cache_expired();

		$this->Settings->add_section(
			array(
				'name'        => 'Navigation',
				'div_id'      => 'navbar_wrapper',
				'description' => SLP_Admin_UI::get_instance()->create_Navbar(),
				'innerdiv'    => false,
				'is_topmenu'  => true,
				'auto'        => false,
			)
		);

		add_action( 'slp_build_info_tab', array( $this, 'add_how_to_use_subtab' ), 10 );
		if ( ! defined( 'MYSLP_VERSION' ) || is_main_site() ) {
			add_action( 'slp_build_info_tab', array( $this, 'add_plugin_environment_subtab' ), 20 );
		}

		do_action( 'slp_build_info_tab', $this->Settings );

		$this->Settings->render_settings_page();

		if ( $this->cache_expired ) {
			$this->update_cache_timestamp();
		}

	}

	/**
	 * Return true if the cache is expired.
	 *
	 * 1 H = 3600s
	 *
	 * @return bool
	 */
	private function is_cache_expired() {
		return ( time() - $this->slplus->options_nojs['broadcast_timestamp'] > ( 24 * 3600 ) );
	}

	/**
	 * Update cache timestamp
	 */
	private function update_cache_timestamp() {
		$this->slplus->options_nojs['broadcast_timestamp'] = time();
		$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
	}
}
