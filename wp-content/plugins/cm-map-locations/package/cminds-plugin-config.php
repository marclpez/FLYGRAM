<?php

use com\cminds\maplocations\App;

$cminds_plugin_config = array(
	'plugin-is-pro'				 => App::isPro(),
	'plugin-has-addons'      => TRUE,
	'plugin-addons'        => array(
		array(
			'title' => 'CM Map Routes Manager',
			'description' => 'Draw map routes and generate a catalog of routes and trails with points of interest using Google maps. ',
			'link' => 'https://www.cminds.com/store/maps-routes-manager-plugin-for-wordpress-by-creativeminds/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=54033&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1'
		),
		array(
			'title' => 'CM Business Directory',
			'description' => 'Build online business directory. Let WordPress users post and manage listings. Includes payment support.',
			'link' => 'https://www.cminds.com/store/purchase-cm-business-directory-plugin-for-wordpress/',
			'link_buy' => 'https://www.cminds.com/checkout/?edd_action=add_to_cart&download_id=33301&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1'
		),
	),
     'plugin-upgrade-text'           => 'Good Reasons to Upgrade to Pro',
    'plugin-upgrade-text-list'      => array(
        array( 'title' => 'Introduction to the locations manager', 'video_time' => '0:00' ),
        array( 'title' => 'Multiple templates for locations index', 'video_time' => 'More' ),
        array( 'title' => 'Support video and images', 'video_time' => 'More' ),
        array( 'title' => 'Choose custom location icon', 'video_time' => 'More' ),
        array( 'title' => 'Import locations frok CSV', 'video_time' => '1:15' ),
        array( 'title' => 'Tags and categories support', 'video_time' => '1:27' ),
        array( 'title' => 'Zip search support', 'video_time' => '1:45' ),
        array( 'title' => 'Customize labels and messages', 'video_time' => '2:00' ),
    ),
    'plugin-upgrade-video-height'   => 240,
    'plugin-upgrade-videos'         => array(
        array( 'title' => 'Map Locations Premium Features', 'video_id' => '146739374' ),
    ),

	'plugin-version'			 => App::VERSION,
	'plugin-abbrev'				 => App::PREFIX,
	'plugin-parent-abbrev'		 => '',
	'plugin-affiliate'               => '',
	'plugin-redirect-after-install'  => admin_url( 'admin.php?page=cmloc-settings' ),
	'plugin-show-guide'                 => TRUE,
	'plugin-guide-text'                 => '    <div style="display:block">
	<ol>
	<li>Go to the plugin <strong>"Setting"</strong></li>
	<li>Get a  <strong>Google Maps Server APP Key</strong> and add it to the plugin settings. </li>
	<li>From the plugin settings click on the <strong>user dashboard</strong> link</li>
	<li>Add your first location. You need to pin the location on the Google map and you can also add a description and images.</li>
	<li><strong>View</strong> the location</li>
	<li>In the <strong>Plugin Settings</strong> you can click on the link of all locations to view them all on one map.</li>
	<li><strong>Troubleshooting:</strong> Make sure that you are using Post name permalink structure in the WP Admin Settings -> Permalinks.</li>
	<li><strong>Troubleshooting:</strong> If post type archive does not show up or displays 404 then install Rewrite Rules Inspector plugin and use the Flush rules button.</li>
	<li><strong>Troubleshooting:</strong> Get the Google Maps Server APP Key. Plugin will not work without it.</li>
	</ol>
	</div>',
	'plugin-guide-video-height'         => 240,
	'plugin-guide-videos'               => array(
		array( 'title' => 'Installation tutorial', 'video_id' => '160220318' ),
	),
	'plugin-show-shortcodes'	 => TRUE,
	'plugin-shortcodes'			 => '<p>You can use the following available shortcodes.</p>',
	'plugin-shortcodes-action'	 => 'cmloc_display_supported_shortcodes',
	'plugin-settings-url' 		 => admin_url( 'admin.php?page=cmloc-settings' ),
	'plugin-file'				 => App::getPluginFile(),
	'plugin-dir-path'			 => plugin_dir_path( App::getPluginFile() ),
	'plugin-dir-url'			 => plugin_dir_url( App::getPluginFile() ),
	'plugin-basename'			 => plugin_basename( App::getPluginFile() ),
	'plugin-icon'				 => '',
	'plugin-name'				 => App::PLUGIN_NAME,
	'plugin-license-name'		 => App::PLUGIN_NAME,
	'plugin-slug'				 => App::PREFIX,
	'plugin-short-slug'			 => App::PREFIX,
	'plugin-parent-short-slug'	 => '',
	'plugin-menu-item'			 => App::PREFIX,
	'plugin-textdomain'			 => '',
	'plugin-userguide-key'		 => '568-cm-map-locations-cmml',
	'plugin-store-url'			 => 'https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/',
	'plugin-support-url'		 => 'https://wordpress.org/support/plugin/cm-map-locations',
	'plugin-review-url'			 => 'http://wordpress.org/support/view/plugin-reviews/cm-map-locations',
	'plugin-changelog-url'		 => 'https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/#changelog',
	'plugin-licensing-aliases'	 => App::getLicenseAdditionalNames(),
	'plugin-compare-table'	 => '
            <div class="pricing-table" id="pricing-table"><h2 style="padding-left:10px;">Upgrade The Location Map Plugin:</h2>
                <ul>
                   <li class="heading" style="background-color:red;">Current Edition</li>
                   <li class="price">FREE<br /></li>
				 	<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Place locations on Google map</li>
					<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Add description and images </li>
					<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Show all locations on a map</li>
                   <hr>
                    Other CreativeMinds Offerings
                    <hr>
                 <a href="https://www.cminds.com/wordpress-plugins-library/seo-keyword-hound-wordpress/" target="blank"><img src="' . plugin_dir_url( __FILE__ ). 'views/Hound2.png"  width="220"></a><br><br><br>
                <a href="https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/" target="blank"><img src="' . plugin_dir_url( __FILE__ ). 'views/banner_yearly-membership_220px.png"  width="220"></a><br>
	</ul>

	<ul>
        <li class="heading">Pro<a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" style="float:right;font-size:11px;color:white;" target="_blank">More</a></li>
        <li class="price">$29.00<br /> <span style="font-size:14px;">(For one Year / Site)<br />Additional pricing options available <a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" target="_blank"> >>> </a></span> <br /></li>
         <li class="action"><a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=65188&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" style="font-size:18px;" target="_blank">Upgrade Now</a></li>
         <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>All Free Version Features <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="All free features are supported in the pro"></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Locations display templates<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Choose between several display templates to support use cases such as store locator, store list, point of interest and more."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Import and Export Locations<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Import and export locations using KML, GPX or CSV format."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Categories<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Place locations in categories and assign a unique icon for each category."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Location Icon<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Override location category icon with a unique icon per each specific location or upload your own icon."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Images and videos<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Images and videos can be added to each location"></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Shortcodes<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Several shortcodes are supported. Shortcodes can be embedded in posts and show a single location, a map with all locations in a category, and more."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Tags<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Tags can be added to locations and allow filtering of locations"></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Search<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Location description, zip code, address and locations name can be searched by keywords."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Search by Radius<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Support for searching a defined radius from a postal code in any country. Can use the web browser geolocation API."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Access control<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Set which role can create or view locations."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Translate text labels<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Modify all text labels in the plugin."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>User Dashboard<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Allow user to see all his posted location in a dashboard. Let him add new locations or control the status of existing ."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Moderation and Notifications<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Let admin moderate user location postings. Send notification to admin when a new location is posted and help for moderation and for user when location is accepted."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Weather information<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Weather information is shown near each location."></span></li>
                    <li class="support" style="background-color:lightgreen; text-align:left; font-size:14px;"><span class="dashicons dashicons-yes"></span> One year of expert support <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="You receive 365 days of WordPress expert support. We will answer questions you have and also support any issue related to the plugin. We will also provide on-site support."></span><br />
                         <span class="dashicons dashicons-yes"></span> Unlimited product updates <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="During the license period, you can update the plugin as many times as needed and receive any version release and security update"></span><br />
                        <span class="dashicons dashicons-yes"></span> Plugin can be used forever <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose not to renew the plugin license, you can still continue to use it as long as you want."></span><br />
                        <span class="dashicons dashicons-yes"></span> Save 40% once renewing license <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose to renew the plugin license you can do this anytime you choose. The renewal cost will be 35% off the product cost."></span></li>
</ul>


	<ul>
        <li class="heading">Mapping Plugins Deluxe <a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" style="float:right;font-size:11px;color:white;" target="_blank">More</a></li>
        <li class="price">$59.00<br /> <span style="font-size:14px;">(For one Year / Site)<br />Additional pricing options available <a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" target="_blank"> >>> </a></span> <br /></li>
         <li class="action"><a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=73574&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" style="font-size:18px;" target="_blank">Upgrade Now</a></li>
         <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>All Free and Pro Version Features <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="All free and pro features are supported in the pro"></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Routes plugin premium<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Includes the routes manager premium version plugin. User can draw routes on Google Maps."></span></li>
                    <li class="support" style="background-color:lightgreen; text-align:left; font-size:14px;"><span class="dashicons dashicons-yes"></span> One year of expert support <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="You receive 365 days of WordPress expert support. We will answer questions you have and also support any issue related to the plugin. We will also provide on-site support."></span><br />
                         <span class="dashicons dashicons-yes"></span> Unlimited product updates <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="During the license period, you can update the plugin as many times as needed and receive any version release and security update"></span><br />
                        <span class="dashicons dashicons-yes"></span> Plugin can be used forever <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose not to renew the plugin license, you can still continue to use it as long as you want."></span><br />
                        <span class="dashicons dashicons-yes"></span> Save 40% once renewing license <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose to renew the plugin license you can do this anytime you choose. The renewal cost will be 35% off the product cost."></span></li>
</ul>

	<ul>
        <li class="heading">Mapping Plugins Ultimate <a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" style="float:right;font-size:11px;color:white;" target="_blank">More</a></li>
        <li class="price">$99.00<br /> <span style="font-size:14px;">(For one Year / Site)<br />Additional pricing options available <a href="https://www.cminds.com/store/map-locations-plugin-for-wordpress-by-creativeminds/" target="_blank"> >>> </a></span> <br /></li>
         <li class="action"><a href="https://www.cminds.com/?edd_action=add_to_cart&download_id=174519&wp_referrer=https://www.cminds.com/checkout/&edd_options[price_id]=1" style="font-size:18px;" target="_blank">Upgrade Now</a></li>
         <li style="text-align:left;"><span class="dashicons dashicons-yes"></span>All Free, Pro and Deluxe Version Features <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="All free, pro and deluxe features are supported in the pro"></span></li>
 		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Routes custom fields addon<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Includes the routes custom fields addon which lets you add additional fields to each route post."></span></li>
		<li style="text-align:left;"><span class="dashicons dashicons-yes"></span>Peepso routes and location addons<span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:green" title="Includes the Peepso routes and location addons, Peepso let you integreate the mapping plugin into a social network you can create on your WP site."></span></li>
                    <li class="support" style="background-color:lightgreen; text-align:left; font-size:14px;"><span class="dashicons dashicons-yes"></span> One year of expert support <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="You receive 365 days of WordPress expert support. We will answer questions you have and also support any issue related to the plugin. We will also provide on-site support."></span><br />
                         <span class="dashicons dashicons-yes"></span> Unlimited product updates <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="During the license period, you can update the plugin as many times as needed and receive any version release and security update"></span><br />
                        <span class="dashicons dashicons-yes"></span> Plugin can be used forever <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose not to renew the plugin license, you can still continue to use it as long as you want."></span><br />
                        <span class="dashicons dashicons-yes"></span> Save 40% once renewing license <span class="dashicons dashicons-admin-comments cminds-package-show-tooltip" style="color:grey" title="Once license expires, If you choose to renew the plugin license you can do this anytime you choose. The renewal cost will be 35% off the product cost."></span></li>
          <li class="heading" style="background-color:orange">BEST VALUE</li>
        	</ul>



	</div>',
);

