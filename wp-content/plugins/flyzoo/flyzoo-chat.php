<?php
    /*
    Plugin Name: Chat by Flyzoo - Group Chat & Live Support
    Plugin URI: https://www.flyzoo.co/
    Description: The amazing chat to rock your community. Perfect for BuddyPress and Ultimate Member.
    Version: 2.3.3
    Author: Flyzoo
    Author URI: https://www.flyzoo.co/
    License: GPL2
    Copyright 2018 Andrea De Santis (email : info@flyzoo.co)
    This program is free trial software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    */
    register_uninstall_hook(__FILE__, 'flyzoo_chat_uninstall');

    function flyzoo_check_regex($path, $patterns)
    {
        $to_replace = array(
        '/(\r\n?|\n)/',
        '/\\\\\*/',
      );
        $replacements = array('|','.*');
        $patterns_fixed = preg_quote($patterns, '/');
        $regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_fixed) . ')$/';
        return (bool) preg_match($regexps[$patterns], $path);
    }

    function flyzoo_show_page()
    {
        $mode = trim(get_option("FlyzooPageFilterMode"));
        $matched = false;
        $pagelist = trim(get_option("FlyzooPageFilterList"));
        if ($mode == "0" || $mode =="") {
            return true;
        }
        if ($pagelist != '') {
            if (function_exists('mb_strtolower')) {
                $pagelist = mb_strtolower($pagelist);
                $currentpath = mb_strtolower($_SERVER['REQUEST_URI']);
            } else {
                $pagelist = strtolower($pagelist);
                $currentpath = strtolower($_SERVER['REQUEST_URI']);
            }
            if (flyzoo_check_regex($currentpath, "/wp-admin/*")) {
                return true;
            }
            $matched = flyzoo_check_regex($currentpath, $pagelist);
            $matched = ($mode == '2')?(!$matched):$matched;
        } elseif ($mode == '2') {
            $matched = true;
        }
        return $matched;
    }

    function flyzoo_chat_uninstall()
    {
        if (get_option('FlyzooApplicationID')) {
            delete_option('FlyzooApplicationID');
        }
        if (get_option('FlyzooPoweredBy')) {
            delete_option('FlyzooPoweredBy');
        }
        if (get_option('FlyzooApiEnabled')) {
            delete_option('FlyzooApiEnabled');
        }
        if (get_option('FlyzooHideInDashboard')) {
            delete_option('FlyzooHideInDashboard');
        }
        if (get_option('FlyzooPageFilterList')) {
            delete_option('FlyzooPageFilterList');
        }

        if (get_option('FlyzooPageFilterMode')) {
            delete_option('FlyzooPageFilterMode');
        }
        if (get_option('FlyzooEnableBuddyPress')) {
            delete_option('FlyzooEnableBuddyPress');
        }

        if (get_option('FlyzooSiteAdded')) {
            delete_option('FlyzooSiteAdded');
        }

        if (get_option('FlyzooHideOnMobile')) {
            delete_option('FlyzooHideOnMobile');
        }
        if (get_option('FlyzooAPISecretKey')) {
            delete_option('FlyzooAPISecretKey');
        }
        if (get_option('FlyzooLanguage')) {
            delete_option('FlyzooLanguage');
        }

        if (get_option('FlyzooCustomLoader')) {
            delete_option('FlyzooCustomLoader');
        }
    }

    function flyzoo_get_wp_userid()
    {
        $user = wp_get_current_user();
        if ($user->ID > 0) {
            return $user->ID;
        } else {
            return '';
        }
    }

    function flyzoo_get_wp_loginname()
    {
        $user = wp_get_current_user();
        if ($user->ID > 0) {
            return $user->user_login;
        } else {
            return '';
        }
    }

    function flyzoo_get_wp_email()
    {
        $user = wp_get_current_user();
        if ($user->ID > 0) {
            return $user->user_email;
        } else {
            return '';
        }
    }

    function flyzoo_get_wp_username()
    {
        $user = wp_get_current_user();

        if ($user->ID <= 0) {
            return '';
        }
        if (defined('BP_VERSION')) {
            $visiblename = bp_get_loggedin_user_fullname();
        } elseif (isset($xoouserultra)) {
            $visiblename = $xoouserultra->userpanel->get_display_name($user->ID);
        } else {
            $visiblename = $user->display_name;
        }

        return $visiblename;
    }

    function flyzoo_get_ultimatemember_avatar_url()
    {
        $user = wp_get_current_user();
        um_fetch_user($user->ID);

        if (um_profile('profile_photo')) {
            $avatar_uri = um_get_avatar_uri(um_profile('profile_photo'), 128);
        } else {
            $avatar_uri = um_get_default_avatar_uri();
        }

        $avatar_uri = apply_filters('um_user_avatar_url_filter', $avatar_uri, um_user('ID'));

        if ($avatar_uri) {
            return $avatar_uri;
        } else {
            return '';
        }
    }

    function flyzoo_get_avatar_url($get_avatar)
    {
        $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
        preg_match($pattern, $get_avatar, $link);
        $link = htmlspecialchars_decode($link[1]);
        return $link;
    }

    function flyzoo_get_userpro_avatar_url($id_or_email)
    {
        global $userpro;
        $size = 100;
        require_once(userpro_path.'lib/BFI_Thumb.php');
        if (isset($id_or_email->user_id)) {
            $id_or_email = $id_or_email->user_id;
        } elseif (is_email($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            $id_or_email = $user->ID;
        }

        if ($id_or_email && userpro_profile_data('profilepicture', $id_or_email)) {
            $url = $userpro->file_uri(userpro_profile_data('profilepicture', $id_or_email), $id_or_email);
            $params = array('width'=>$size,'height'=>$size,'quality'=>100);
            $return = bfi_thumb(get_site_url().(strpos($url, "http") !== false ? urlencode($url) : $url), $params);
        } else {
            if ($id_or_email && userpro_profile_data('gender', $id_or_email)) {
                $gender = strtolower(userpro_profile_data('gender', $id_or_email));
            } else {
                $gender = 'male'; // default gender
            }

            $return = userpro_url . 'img/default_avatar_'.$gender.'.jpg';
        }

        if (userpro_profile_data('profilepicture', $id_or_email) != '') {
            return $return;
        } else {
            if (userpro_get_option('use_default_avatars') == 1) {
                return $avatar;
            } else {
                return $return;
            }
        }
    }

    function flyzoo_get_uultra_avatar_url($id)
    {
        global  $xoouserultra;

        require_once(ABSPATH . 'wp-includes/link-template.php');

        $site_url = '';

        $pic_size = "";

        $upload_dir = wp_upload_dir();
        $upload_folder =   $upload_dir['baseurl'].'/'.$xoouserultra->get_option('media_uploading_folder');

        $path = $site_url.$upload_folder."/".$id."/";
        $author_pic = get_the_author_meta('user_pic', $id);

        $avatar_pic = $path.$author_pic;

        return $avatar_pic;
    }

    function flyzoo_get_uultra_friends($user_id)
    {
        global $wpdb, $xoouserultra;

        $sql = ' SELECT friend.*, u.ID FROM ' . $wpdb->prefix . 'usersultra_friends friend  ' ;
        $sql .= " RIGHT JOIN ".$wpdb->prefix ."users u ON ( u.ID = friend.friend_receiver_id)";
        $sql .= " WHERE u.ID = friend.friend_receiver_id  AND  friend.friend_status = 1 AND friend.friend_receiver_id = '".$user_id."'  ORDER BY friend.friend_id DESC ";

        $rows = $wpdb->get_results($sql);

        $fids = array();

        foreach ($rows as $msg) {
            $fids[] =  $msg->friend_sender_user_id;
        }

        return $fids;
    }

    function flyzoo_get_wp_avatar()
    {
        $user = wp_get_current_user();
        global $xoouserultra;

        if (!$user->ID > 0) {
            return '';
        }

        if (defined('BP_VERSION')) {
            if (bp_core_fetch_avatar(array( 'item_id' => $user->ID , 'no_grav' => true,'html'=> false )) != bp_core_avatar_default('local')) {
                return bp_get_loggedin_user_avatar('type=full&html=false');
            } else {
                return flyzoo_get_avatar_url(get_avatar($user->ID));
            }
        } elseif (isset($xoouserultra)) {
            return flyzoo_get_uultra_avatar_url($user->ID);
        } elseif (function_exists("um_get_user_avatar_url")) {
            return flyzoo_get_ultimatemember_avatar_url();
        } elseif (function_exists("userpro_profile_data")) {
            return flyzoo_get_userpro_avatar_url($user);
        } elseif (function_exists("user_avatar_fetch_avatar")) {
            return user_avatar_fetch_avatar(array('html' => false, 'item_id' => $user->ID));
        } elseif (function_exists("get_wp_user_avatar_src")) {
            return get_wp_user_avatar_src($user->ID);
        } elseif (function_exists("get_simple_local_avatar")) {
            $a = get_simple_local_avatar($user->ID);
            $a = explode('src="', $a);
            if (isset($a[1])) {
                $a = explode('"', $a[1]);
            } else {
                $a = explode("src='", $a[0]);
                if (isset($a[1])) {
                    $a = explode("'", $a[1]);
                } else {
                    $a[0] = 'http://www.gravatar.com/avatar/' . (($current_user->ID)?(md5(strtolower($user->user_email))):('00000000000000000000000000000000')) . '?d=mm&size=24';
                }
                return  $a[0] ;
            }
        } else {
            try {
                return flyzoo_get_avatar_url(get_avatar($user->ID));
            } catch (Exception $e) {
                return "";
            }
        }


        return '';
    }

    function flyzoo_logout()
    {
        setcookie("flyzoo-force-logout", "true", time() + 3600, "/");
    }


    function flyzoo_get_user_profile_url()
    {
        global $ultimatemember, $xoouserultra;

        $uid = flyzoo_get_wp_userid();

        if ($uid <=0) {
            return '';
        }

        if (function_exists("bp_core_get_userlink")) {
            return bp_core_get_userlink($uid, false, true);
        } elseif (function_exists("userpro_profile_data")) {
            global $userpro;
            return $userpro->permalink(get_current_user_id());
        } elseif (function_exists("um_user_profile_url")) {
            return um_user_profile_url();
        } elseif (isset($xoouserultra)) {
            return $xoouserultra->userpanel->get_user_profile_permalink($uid);
        }

        return "";
    }

    function flyzoo_get_access_roles()
    {
        global $current_user;
        $user_roles = $current_user->roles;
        $user_role = implode (", ",  $current_user->roles);
        if (function_exists("userpro_profile_data")) {
            $user_role=userpro_profile_data('role', get_current_user_id());
        }
        return $user_role;
    }

    function flyzoo_embed_chatroom($atts)
    {
        $embed = "<div id='flyzoo-embedded-chatroom' data-id='" . $atts['id'] . "' style='width:" . $atts['width'] . "; height:" . $atts['height'] . ";'></div>";
        return $embed;
    }

    function flyzoo_get_friends()
    {
        $user = wp_get_current_user();
        if (!$user->ID > 0) {
            return '';
        }

        global $xoouserultra;

        if (function_exists('friends_get_friend_user_ids')) {
            return friends_get_friend_user_ids($user->ID);
        } elseif (isset($xoouserultra)) {
            return flyzoo_get_uultra_friends($user->ID);
        } else {
            return "";
        }
    }

    add_shortcode('flyzoo-embed-chatroom', 'flyzoo_embed_chatroom');

    class FlyzooWidget
    {
        public $flyzooroot;

        public function __construct()
        {
            $this->flyzooroot = "https://www.flyzoo.co/";
            add_action('wp_logout', 'flyzoo_logout');
            add_action('wp_footer', array(
                &$this,
                "embedFlyzoo"
            ));

            if (is_admin()) {
                add_action("admin_menu", array(
                    &$this,
                    "adminMenu"
                ));

                add_action('admin_init', array(
                    &$this,
                    "setOptions"
                ));

                if (get_option('FlyzooHideInDashboard') != true) {
                    $Path = $_SERVER['REQUEST_URI'];

                    if (strpos($Path, 'wp-admin/plugin-install.php?tab=plugin-information') === false) {
                        add_action('admin_footer', array(
                            $this,
                            'embedFlyzoo'
                        ));
                    }
                }
            }
        }

        public function setOptions()
        {
            register_setting('flyzoo-options', 'FlyzooApplicationID');
            register_setting('flyzoo-options', 'FlyzooPoweredBy');
            register_setting('flyzoo-options', 'FlyzooApiEnabled');
            register_setting('flyzoo-options', 'FlyzooHideInDashboard');
            register_setting('flyzoo-options', 'FlyzooPageFilterList');
            register_setting('flyzoo-options', 'FlyzooPageFilterMode');
            register_setting('flyzoo-options', 'FlyzooEnableBuddyPress');
            register_setting('flyzoo-options', 'FlyzooSiteAdded');
            register_setting('flyzoo-options', 'FlyzooHideOnMobile');
            register_setting('flyzoo-options', 'FlyzooAPISecretKey');
            register_setting('flyzoo-options', 'FlyzooLanguage');
            register_setting('flyzoo-options', 'FlyzooCustomLoader');
        }

        public function adminMenu()
        {
            add_menu_page('Flyzoo Live Chat', 'Flyzoo Live Chat', 'manage_options', 'flyzoo-chat', array(
                $this,
                'createAdminPage'
            ), content_url() . '/plugins/flyzoo/images/flyzoo-icon.png');
        }

        public function getSignupUrl()
        {
            return $this->flyzooroot . 'signup?utm_source=wordpress&utm_medium=admin&s=pro&t=12&fzsiteurl=' . urlencode(site_url()) . '&p=wordpress&e=' . urlencode(get_option('admin_email')) . '&sip=' . $_SERVER['REMOTE_ADDR'] . '&un=' . urlencode(wp_get_current_user()->display_name);
        }



        public function createAdminPage()
        {
            $code = get_option('FlyzooApplicationID'); ?>
<style>
        #flyzoo-options ul { margin-left: 10px; }
        #flyzoo-options ul li { margin-left: 15px; list-style-type: disc;}
        #flyzoo-options h1 {margin-top: 5px; margin-bottom:10px; color: #00557f}
        .fz-span { margin-left: 23px;}


    .flyzoo-signup-button {
      float: left;
      vertical-align: top;
      width: auto;
      height: 30px;
      line-height: 30px;
      padding: 10px;
      font-size: 22px;
      color: white;
      text-align: center;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
      background: #c0392b;
      border-radius: 5px;
      border-bottom: 2px solid #b53224;
      cursor: pointer;
      -webkit-box-shadow: inset 0 -2px #b53224;
      box-shadow: inset 0 -2px #b53224;
      text-decoration: none;
      margin-top: 10px;
      margin-bottom: 10px;
      clear: both;
    }

    a.flyzoo-signup-button:hover {
      cursor: pointer;
      color: #f8f8f8;
    }


</style>
<div id="flyzoo-options" style="width:880px;margin-top:10px;">
    <div style="float: left; width: 300px;">
        <?php
            echo '<a target="_blank" href="https://www.flyzoo.co?utm_source=wp-plugin">';
            echo '<img style="border-radius:5px;border:0px;" src="' . plugins_url('images/logo.jpg', __FILE__) . '" > ';
            echo '</a>'; ?>
        <?php
            if ($code != '') {
                echo '<a target="_blank" href="https://dashboard.flyzoo.co?utm_source=wp-plugin">';
                echo '<img style="border:0px;margin-top:5px;border-radius:5px;" src="' . plugins_url('images/dashboard.jpg', __FILE__) . '" > ';
                echo '</a>';
            }
            echo '<a target="_blank" href="https://www.flyzoo.co/support?utm_source=wp-plugin-help">';
            echo '<img style="border:0px;margin-top:5px; margin-bottom:5px;border-radius:5px;" src="' . plugins_url('images/support.jpg', __FILE__) . '" > ';
            echo '</a>'; ?>
    </div>
    <div style="float: left; margin-left: 10px; width: 500px; background-color:#f8f8f8; padding: 10px; border-radius: 5px;">
        <h1>Flyzoo Chat</h1>

        <?php
            if ($code == '') {
                echo "<a class='flyzoo-signup-button' target='_blank' href='" . $this->getSignupUrl() . "'>Click here to create your account!</a>'";
            } ?>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                    function updateFilterPanel() {
                if (jQuery("#FlyzooPageFilterMode").val() != "0") {
                    jQuery("#FlyzooPageFilterDiv").show()
                } else {
                    jQuery("#FlyzooPageFilterDiv").hide();
                }
            };
                jQuery("#FlyzooPageFilterMode").change(function () {
                    updateFilterPanel();
                });
                  updateFilterPanel();
            })
        </script>
        <div style="clear: both"></div>

        <form method="post" action="options.php">
            <?php
                settings_fields('flyzoo-options'); ?>
            <h4>Enter your Flyzoo Application ID</h4>
            <p>
                <input type="text" style="width: 400px" name="FlyzooApplicationID" id="FlyzooApplicationID" value="<?php echo(get_option("FlyzooApplicationID")); ?>" maxlength="999" />

            </p>
            <h4>Enter your API Secret Key</h4>
            <p>
                <input type="text" style="width: 400px" name="FlyzooAPISecretKey" id="FlyzooAPISecretKey" value="<?php echo(get_option("FlyzooAPISecretKey")); ?>" maxlength="50" />

            </p>
            <p>
                <input type="checkbox" id="FlyzooPoweredBy" name="FlyzooPoweredBy" <?php echo(get_option("FlyzooPoweredBy") == true ? 'checked="checked"' : ''); ?>>
                <strong>Required</strong> I acknowledge there is a 'powered by Flyzoo' link <br />on the widget (WOW plan only).<br />
            </p>
            <input type="submit" value="<?php
       echo(_e("Save Changes")); ?>" /><br /> <br />
            <hr /><br />
            <h1>Options</h1>
            <br />
            <!-- Single sign on -->
            <input type="checkbox" id="FlyzooApiEnabled" name="FlyzooApiEnabled" <?php  echo(get_option("FlyzooApiEnabled") == true ? 'checked="checked"' : ''); ?>>
                <strong>Enable Single Sign On</strong> - check this to allow users log into the chat with their existing WordPress Account.<br />
                <br />
                <!-- Single sign on -->
                <input type="checkbox" id="FlyzooEnableBuddyPress" name="FlyzooEnableBuddyPress" <?php  echo(get_option("FlyzooEnableBuddyPress") == true ? 'checked="checked"' : ''); ?>>
                <strong>Sync User Data</strong> - check this to integrate Flyzoo with BuddyPress/Ultimate Member/Users Ultra and others (sync friends, profile url, avatar) <br />
                <br />
                <!-- Hide in Admin -->
                <input type="checkbox" id="FlyzooHideInDashboard" name="FlyzooHideInDashboard" <?php echo(get_option("FlyzooHideInDashboard") == true ? 'checked="checked"' : ''); ?>>
                <strong>Hide in WordPress Admin</strong> - check this to hide the widget on the WP Admin. <br />
                <br />
                <!-- Hide on Mobile -->
                <input type="checkbox" id="FlyzooHideOnMobile" name="FlyzooHideOnMobile" <?php echo(get_option("FlyzooHideOnMobile") == true ? 'checked="checked"' : ''); ?>>
                <strong>Hide on Mobile Devices</strong> - check this to hide the widget on mobile devices<br />
                <br />
                <strong>PAGE FILTERS</strong><br /><br>
           Show Flyzoo
                <select id="FlyzooPageFilterMode" name="FlyzooPageFilterMode">
                    <option <?php echo(get_option("FlyzooPageFilterMode") == "0" || get_option("FlyzooPageFilterMode") == "" ? 'selected="selected"' : ''); ?> value="0">on all pages</option>
                    <option <?php echo(get_option("FlyzooPageFilterMode") == "1" ? 'selected="selected"' : ''); ?> value="1">only on the listed pages</option>
                    <option <?php echo(get_option("FlyzooPageFilterMode") == "2" ? 'selected="selected"' : ''); ?> value="2">on all pages except those listed</option>
                </select>
                <div id="FlyzooPageFilterDiv" style="display:none">
                    <ul>
                        <li>Enter only one path per line</li>
                        <li>Always start the path with a forward slash (/)</li>
                        <li>Use '*' for the wildcard (Ex. /2014/posts/* to select all the posts)</li>
                    </ul>


                    <textarea style="width: 450px; height: 90px" name="FlyzooPageFilterList" id="FlyzooPageFilterList"><?php echo(get_option("FlyzooPageFilterList")); ?></textarea>

                </div><br /><br>
                  <strong>WIDGET LANGUAGE</strong><br /><br>
             <select id="FlyzooLanguage" name="FlyzooLanguage">
                  <option <?php echo(get_option("FlyzooLanguage") == "auto" || get_option("FlyzooLanguage") == "" ? 'selected="selected"' : ''); ?> value="auto">auto (use browser language)</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "en" ? 'selected="selected"' : ''); ?> value="en">English</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "es" ? 'selected="selected"' : ''); ?> value="es">Spanish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "fr" ? 'selected="selected"' : ''); ?> value="fr">French</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "it" ? 'selected="selected"' : ''); ?> value="it">Italian</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "bg" ? 'selected="selected"' : ''); ?> value="bg">Bulgarian</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "cs" ? 'selected="selected"' : ''); ?> value="cs">Czech</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "da" ? 'selected="selected"' : ''); ?> value="da">Danish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "de" ? 'selected="selected"' : ''); ?> value="de">German (Standard)</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "fa" ? 'selected="selected"' : ''); ?> value="fa">Farsi</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "fi" ? 'selected="selected"' : ''); ?> value="fi">Finnish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "he" ? 'selected="selected"' : ''); ?> value="he">Hebrew</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "ja" ? 'selected="selected"' : ''); ?> value="ja">Japanese</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "nl" ? 'selected="selected"' : ''); ?> value="nl">Dutch</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "pl" ? 'selected="selected"' : ''); ?> value="pl">Polish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "pt-BR" ? 'selected="selected"' : ''); ?> value="pt-BR">Portuguese (Brazil)</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "pt-PT" ? 'selected="selected"' : ''); ?> value="pt-PT">Portuguese (Portugal)</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "ru" ? 'selected="selected"' : ''); ?> value="ru">Russian</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "sk" ? 'selected="selected"' : ''); ?> value="sk">Slovak</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "sr" ? 'selected="selected"' : ''); ?> value="sr">Serbian</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "sv" ? 'selected="selected"' : ''); ?> value="sv">Swedish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "tr" ? 'selected="selected"' : ''); ?> value="tr">Turkish</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "vi" ? 'selected="selected"' : ''); ?> value="vi">Vietnamese</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "zh" ? 'selected="selected"' : ''); ?> value="zh">Chinese</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "el" ? 'selected="selected"' : ''); ?> value="el">Greek</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "ro" ? 'selected="selected"' : ''); ?> value="ro">Romanian</option>
                  <option <?php echo(get_option("FlyzooLanguage") == "kr" ? 'selected="selected"' : ''); ?> value="kr">Korean</option>


                </select>
                <br /><br />
                 <h1>Advanced</h1>
                   <strong>CUSTOM LOADER</strong><br />
                  Just skip this :) This will be provided by the Support Team in case you need a particular fix for your site.
             <p>
                <input type="text" style="width: 400px" name="FlyzooCustomLoader" id="FlyzooCustomLoader" value="<?php echo(get_option("FlyzooCustomLoader")); ?>" maxlength="999" />

            </p>

                <input type="submit" value="<?php
       echo(_e("Save Changes")); ?>" /><br /> <br />
        </form>
    </div>
</div>
<?php
        }
        public function embedFlyzoo()
        {
            $e    = '';
            $code = get_option('FlyzooApplicationID');
            $secret = get_option('FlyzooAPISecretKey');
            $customLoader = trim(get_option('FlyzooCustomLoader'));
            $loaderScript = $customLoader  == "" ? "flyzoo.start.js" : $customLoader ;
            $friends = flyzoo_get_friends();
            $profile_url = flyzoo_get_user_profile_url();
            if (wp_is_mobile() &&  get_option('FlyzooHideOnMobile')) {
                return;
            }
            if ($code == '') {
                return;
            }
            //if (get_option('FlyzooPoweredBy')!=true) return;
            if (!flyzoo_show_page()) {
                return;
            }

            if (get_option('FlyzooApiEnabled') == false && get_option('FlyzooLanguage') != "auto") {
                $api = '<script type="text/javascript">' . 'var FlyzooApi = FlyzooApi || { };' .
                      'FlyzooApi.Language = ' . json_encode(get_option('FlyzooLanguage')) . ';'.
                      '</script>';
                echo($api);
            }

            $e .= '<!-- Flyzoo Script V3 -->' .
             '<script type="text/javascript">' .
             '(function () { ' . 'window._FlyzooApplicationId="' . $code . '";' .
             'var fz = document.createElement("script"); fz.type = "text/javascript"; fz.async = true;' .
             'fz.src = "//widget.flyzoo.co/scripts/'.  $loaderScript.'";' .
              'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(fz, s)})()' .
               '</script>';
            echo($e);

            if (get_option('FlyzooApiEnabled') != true) {
                return;
            }

            $fzid = flyzoo_get_wp_userid();
            $fzun = flyzoo_get_wp_username();
            $fzem = flyzoo_get_wp_email();
            $fzsig = "";
            $payload = trim(strtolower($fzid)).trim(strtolower($fzem));
            if ($secret != "") {
                $fzsig = hash_hmac("sha256", $payload, $secret);
            }
            $api = '<script type="text/javascript">' . 'var FlyzooApi = FlyzooApi || { };' .
            'FlyzooApi.UserId = ' . json_encode($fzid) . ';' .
            'FlyzooApi.UserName = ' . json_encode($fzun) . ';'.
            'FlyzooApi.Email = ' . json_encode($fzem) . ';' .
            'FlyzooApi.Signature = ' . json_encode($fzsig) . ';' .
            'FlyzooApi.AccessRoles = ' . json_encode(flyzoo_get_access_roles()) . ';' .
            'FlyzooApi.Language = ' . json_encode(get_option('FlyzooLanguage')) . ';';

            if (get_option("FlyzooEnableBuddyPress") == true) {
                $api .= 'FlyzooApi.Friends = ' . json_encode($friends) . ';' .
                'FlyzooApi.Avatar = ' . json_encode(flyzoo_get_wp_avatar()) . ';' .

                'FlyzooApi.Profile = ' . json_encode($profile_url) . ';' ;
            }


            $api .='</script>';
            echo($api);
        }
    }
    new FlyzooWidget();
?>
