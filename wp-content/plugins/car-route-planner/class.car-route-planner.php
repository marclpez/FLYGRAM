<?php
class CarRoutePlanner {

   const WIDGET_SERVER_URL_OPTION_NAME = 'crp-server-url';
   const DEFAULT_SERVER_URL = 'https://www.drivebestway.com';
   const TRANSIENT_CONFIG_SLUG = 'crp-transient-config';
   const TRANSIENT_CONFIG_SECONDS = 86400;
   const CONFIG_GET_TIMEOUT = 10;
   const SHORTCODE = "crp";
   const AUTOCOMPLETE_PATH = "/distance-ajax/atc/TERM.json";
   const CSS_PATH = '/widget/v1/widget.css';
   const CLIENT_SCRIPT_PATH = '/widget/v1/client.js';
   const CONFIG_PATH = '/widget/v1/cms/config';

   public static function getShortcodeOptions() {
      return array(
         'language',
         'currency',
         'measure',
         'text_color',
         'background_color',
         'accent_color',
         'default_from',
         'hide_from',
         'default_to',
         'hide_to',
         'show_via',
         'default_via',
         'show_fuel_calc',
         'default_fuel_consumption',
         'default_fuel_price',
         'show_speed_profile',
         'default_speed_limit_motorway',
         'default_speed_limit_other',
         'show_result_length',
         'show_result_driving_time',
         'show_result_fuel_amount',
         'show_result_fuel_cost',
         'show_result_customized_cost',
         'customized_cost_formula',
         'customized_cost_label',
         'show_result_map',
         'show_result_scheme',
         'calculate_instantly',
         'only_countries',
         'prefer_countries',
      );
   }

   public static function getPluginOptions() {
      $optionsArray = array();
      foreach (self::getShortcodeOptions() as $shortcodeString) {
         $optionsArray[] = 'crp_' . $shortcodeString;
      }
      $optionsArray[] = 'crp_shortcode_type';
      return $optionsArray;
   }

   public static function init() {
      load_plugin_textdomain('car-route-planner', false, dirname( plugin_basename(__FILE__) ) . '/languages/');
      self::addShortcode();
   }

   public static function pluginDeactivation() {
      foreach (self::getPluginOptions() as $optionString) {
         delete_option($optionString);
      }
      delete_transient(self::TRANSIENT_CONFIG_SLUG);
      remove_shortcode(self::SHORTCODE);
   }

   public static function getConfig() {
      $configArray = get_transient(self::TRANSIENT_CONFIG_SLUG);
      return $configArray === false ? self::refetchConfig() : $configArray;
   }

   public static function refetchConfig() {
       $configArray = self::retrieveConfig();
       if ($configArray) {
           set_transient(self::TRANSIENT_CONFIG_SLUG, $configArray, self::TRANSIENT_CONFIG_SECONDS);
       }
       return $configArray;
   }

   private static function retrieveConfig() {
      $request = wp_remote_get(
         self::DEFAULT_SERVER_URL . self::CONFIG_PATH, array('timeout' => self::CONFIG_GET_TIMEOUT)
      );
      if(is_wp_error($request)) {
         return array();
      }
      $bodyString = wp_remote_retrieve_body($request);
      return json_decode($bodyString, true);
   }

   public static function setDefaultOptions() {

      $currentWpLocale = get_locale();
      $settingsArray = self::getDomainSettings($currentWpLocale);

      update_option("crp_language", $settingsArray['language'], true);
      update_option("crp_currency", $settingsArray['currency'], true);
      update_option("crp_measure", $settingsArray['measurement'], true);
      update_option("crp_text_color", '#000000', true);
      update_option("crp_background_color", '#ffffff', true);
      update_option("crp_accent_color", '#269adb', true);
      update_option("crp_show_fuel_calc", 'on', true);
      update_option("crp_show_speed_profile", 'on', true);
      update_option("crp_show_result_length", 'on', true);
      update_option("crp_show_result_driving_time", 'on', true);
      update_option("crp_show_result_fuel_amount", 'on', true);
      update_option("crp_show_result_fuel_cost", 'on', true);
      update_option("crp_show_result_map", 'on', true);
      update_option("crp_show_result_scheme", 'on', true);
      update_option("crp_show_result_customized_cost", '', true);
      update_option("crp_shortcode_type", 'simple');
   }

   private static function getDomainSettings($localeString, $currencyString = null) {
      $configArray = self::getConfig();
      $languageString = substr($localeString, 0, 2);
      $domainSettingsArray = null;
      foreach ($configArray['i18nSettings'] as $domainArray) {
         $domainLanguageString = substr($domainArray['hreflang'], 0, 2);
         if (($domainLanguageString == $languageString) and ($domainArray['currency'] == $currencyString)) {
            $domainSettingsArray = $domainArray;
            break;
         }
      }
      if (! $domainSettingsArray) {
         foreach ($configArray['i18nSettings'] as $domainArray) {
            $hreflangLocale = str_replace("-", "_", $domainArray['hreflang']);
            if ($hreflangLocale == $localeString) {
               $domainSettingsArray = $domainArray;
               break;
            }
         }
      }
      if (! $domainSettingsArray) {
         foreach ($configArray['i18nSettings'] as $domainArray) {
            $domainLanguageString = substr($domainArray['hreflang'], 0, 2);
            if ($domainLanguageString == $languageString) {
               $domainSettingsArray = $domainArray;
               break;
            }
         }
      }
      if (! $domainSettingsArray) {
         foreach ($configArray['i18nSettings'] as $domainArray) {
            if (isset($domainArray['default'])) {
               $domainSettingsArray = $domainArray;
               break;
            }
         }
      }

      return $domainSettingsArray;
   }

   public static function getDomainUrl($usePluginOptions = false) {
      $localeString = $currencyString = null;
      if ($usePluginOptions) {
         $localeString = get_option('crp_language', null);
         $currencyString = get_option('crp_currency', null);
      }
      if ($localeString === null) {
         $localeString = get_locale();
      }
      $domainSettingsArray = self::getDomainSettings($localeString, $currencyString);
      return $domainSettingsArray['url'];
   }

   private static function getDomainUrlByLocaleAndCurrency($localeString, $currencyString) {
      $domainSettingsArray = self::getDomainSettings($localeString, $currencyString);
      return $domainSettingsArray['url'];
   }

   private static function getSimpleFormSubmitUrl() {
      $domainSettingsArray = self::getDomainSettings(get_locale());
      return $domainSettingsArray['submitUrl'];
   }

   private static function addShortcode() {
      add_shortcode(self::SHORTCODE, array('CarRoutePlanner', 'shortcodeHandler'));
   }

   public static function shortcodeHandler($attrsArray) {
      if (!$attrsArray) {
         $htmlString = self::getHtmlForSimpleShortcode();
      } else {
         $htmlString = self::getHtmlForAdvancedShortcode($attrsArray);
      }
      return $htmlString;
   }

   private static function getHtmlForSimpleShortcode() {

      $autocompleteUrlString = self::getDomainUrl() . self::AUTOCOMPLETE_PATH;
      $fromLabelString = __("From", 'car-route-planner');
      $toLabelString = __("To", 'car-route-planner');
      $submitString = __("Calculate", 'car-route-planner');
      $actionString = self::getSimpleFormSubmitUrl();
      $utmSourceString = esc_attr(self::getSiteDomain());

      wp_enqueue_script(
         'crp-autocomplete',
         plugin_dir_url(__FILE__) . 'js/autocomplete.js',
         array('jquery','jquery-ui-autocomplete'),
         null,
         true
      );

      wp_enqueue_script(
         'crp-simple',
         plugin_dir_url(__FILE__) . 'js/simple.js',
         array('crp-autocomplete'),
         null,
         true
      );

      $wp_scripts = wp_scripts();
      wp_enqueue_style(
         'car-route-planner-admin-ui-css',
         '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/smoothness/jquery-ui.css'
      );

      wp_enqueue_style(
         'car-route-planner-client-css',
         plugin_dir_url(__FILE__) . 'css/client.css'
      );

      $htmlString = <<<EOL
            <form class="crp-simple-form" action="{$actionString}">
               <div class="crp-table">
                  <div class="crp-tr">
                     <div class="crp-td">
                        <label for="crp-from">{$fromLabelString}</label>
                     </div>
                     <div class="crp-td">
                        <input type="text" name="from" id="crp-from">
                     </div>
                  </div>
                  <div class="crp-tr">
                     <div class="crp-td">
                        <label for="crp-to">{$toLabelString}</label>
                     </div>
                     <div class="crp-td">
                        <input type="text" name="to" id="crp-to">
                     </div>
                  </div>
               </div>
               <input type="hidden" name="utm_source" value="{$utmSourceString}">
               <input type="hidden" name="utm_medium" value="wp_plugin">
               <input type="hidden" name="utm_campaign" value="widget">
               <input type="hidden" name="utm_content" value="simple_form_submit">
               <input type="submit" value="{$submitString}" class="crp-submit">
               </table>
            </form>
            <script>var crpAutocompleteUrl = "{$autocompleteUrlString}";</script>
EOL;
      return $htmlString;
   }

   private static function getHtmlForAdvancedShortcode($attrsArray) {
      $dataAttrsArray = array();
      $shortcodeOptionsArray = self::getShortcodeOptions();
      $colorAttrsArray = array();
      foreach ($attrsArray as $attrNameString => $attrValue) {
         if (in_array($attrNameString, array('text_color', 'background_color', 'accent_color'), true)) {
            $colorAttrsArray[$attrNameString] = $attrValue;
            continue;
         }
         if (is_numeric($attrNameString) and in_array($attrValue, $shortcodeOptionsArray, true)) {
            $dataAttrsArray[] = 'data-' . str_replace('_', '-', $attrValue);
            continue;
         }
         if (in_array($attrNameString, $shortcodeOptionsArray, true) and strlen($attrValue)) {
            $dataAttrsArray[] = 'data-' . str_replace('_', '-', $attrNameString) . '="' . esc_attr($attrValue) . '"';
         }
      }

      $localeString = get_locale();
      $currencyString = null;
      if (isset($attrsArray['language']) and strlen($attrsArray['language'])) {
         $localeString = $attrsArray['language'];
      }
      if (isset($attrsArray['currency']) and strlen($attrsArray['currency'])) {
         $currencyString = $attrsArray['currency'];
      }
      $domainUrlString = self::getDomainUrlByLocaleAndCurrency($localeString, $currencyString);

      $cssUrlString = self::getCssUrl($colorAttrsArray, $domainUrlString);
      $dataAttrsArray[] = 'data-css="' . esc_attr($cssUrlString) . '"';

      $utmParamsArray = array(
         'utm_source=' . urlencode(self::getSiteDomain()),
         'utm_medium=wp_plugin',
         'utm_campaign=widget',
         'utm_content=attribution_link_image'
      );
      $domainUrlWithUtmParamsString = esc_attr($domainUrlString . '/?' . join('&', $utmParamsArray));

      $dataString = join(' ', $dataAttrsArray);
      $altStringVariants = [
          $domainUrlString, $domainUrlString . '/',
          parse_url($domainUrlString, PHP_URL_HOST),
          parse_url($domainUrlString, PHP_URL_HOST) . '/',
      ];
      $altString = $altStringVariants[crc32(self::getSiteDomain()) % count($altStringVariants)];
      $linkString = '<a class="rp-widget-link" rel="noopener" target="_blank" href="' . $domainUrlWithUtmParamsString . '" '
          . $dataString . '><img src="data:image/svg+xml;base64,'
          . base64_encode('<'.'?xml version="1.0" encoding="UTF-8" standalone="no"?'.'><svg xmlns="http://www.w3.org/2000/svg" height="105" width="135" x="0px" y="0px" viewBox="0 0 45 35"><path fill="' . (isset($colorAttrsArray['text_color']) ? $colorAttrsArray['text_color'] : '#000') . '" fill-opacity="0.3" d="M 45,12 C 45,5.383 39.617,0 33,0 30.072,0 27.247,1.076 25.045,3.031 24.632,3.398 24.594,4.03 24.961,4.443 25.328,4.857 25.96,4.894 26.373,4.527 28.209,2.897 30.561,2 33,2 c 5.514,0 10,4.486 10,10 0,6.897 -7.69,14.522 -10,16.656 -1.354,-1.248 -4.549,-4.376 -6.966,-8.148 -0.298,-0.467 -0.917,-0.604 -1.382,-0.303 -0.465,0.298 -0.6,0.916 -0.303,1.381 3.291,5.137 7.811,9.013 8.002,9.176 0.188,0.159 0.417,0.238 0.648,0.238 0.23,0 0.461,-0.079 0.648,-0.238 C 34.111,30.367 45,21.001 45,12 Z M 24,12 C 24,5.383 18.617,0 12,0 5.383,0 0,5.383 0,12 0,21.001 10.889,30.367 11.352,30.762 11.539,30.921 11.77,31 12,31 12.23,31 12.461,30.921 12.648,30.762 13.111,30.367 24,21.001 24,12 Z M 12,28.656 C 9.689,26.523 2,18.906 2,12 2,6.486 6.486,2 12,2 17.514,2 22,6.486 22,12 22,18.897 14.31,26.522 12,28.656 Z M 12,7 c -2.757,0 -5,2.243 -5,5 0,2.757 2.243,5 5,5 2.757,0 5,-2.243 5,-5 0,-2.757 -2.243,-5 -5,-5 z m 0,8 c -1.654,0 -3,-1.346 -3,-3 0,-1.654 1.346,-3 3,-3 1.654,0 3,1.346 3,3 0,1.654 -1.346,3 -3,3 z m 26,-3 c 0,-2.757 -2.243,-5 -5,-5 -2.757,0 -5,2.243 -5,5 0,2.757 2.243,5 5,5 2.757,0 5,-2.243 5,-5 z m -8,0 c 0,-1.654 1.346,-3 3,-3 1.654,0 3,1.346 3,3 0,1.654 -1.346,3 -3,3 -1.654,0 -3,-1.346 -3,-3 z M 14.29,33.29 C 14.11,33.479 14,33.74 14,34 c 0,0.26 0.11,0.52 0.29,0.71 0.19,0.18 0.45,0.29 0.71,0.29 0.26,0 0.52,-0.11 0.7,-0.29 0.19,-0.19 0.3,-0.44 0.3,-0.71 0,-0.26 -0.11,-0.521 -0.29,-0.71 -0.38,-0.37 -1.04,-0.37 -1.42,0 z m -5,0 C 9.11,33.479 9,33.74 9,34 9,34.26 9.11,34.52 9.29,34.71 9.48,34.89 9.74,35 10,35 10.26,35 10.52,34.89 10.71,34.71 10.89,34.52 11,34.26 11,34 c 0,-0.26 -0.11,-0.521 -0.29,-0.71 -0.37,-0.37 -1.03,-0.38 -1.42,0 z m 10,0 C 19.11,33.479 19,33.74 19,34 c 0,0.26 0.11,0.52 0.29,0.71 0.19,0.18 0.45,0.29 0.71,0.29 0.26,0 0.52,-0.11 0.71,-0.29 C 20.89,34.52 21,34.26 21,34 c 0,-0.26 -0.11,-0.521 -0.29,-0.71 -0.38,-0.37 -1.04,-0.37 -1.42,0 z m 10,0 C 29.109,33.479 29,33.74 29,34 c 0,0.26 0.109,0.52 0.29,0.71 0.19,0.18 0.45,0.29 0.71,0.29 0.26,0 0.52,-0.11 0.71,-0.29 C 30.891,34.52 31,34.26 31,34 c 0,-0.26 -0.109,-0.521 -0.29,-0.71 -0.38,-0.37 -1.04,-0.37 -1.42,0 z m 5,0 C 34.109,33.479 34,33.74 34,34 c 0,0.26 0.109,0.52 0.29,0.71 0.19,0.18 0.45,0.29 0.71,0.29 0.26,0 0.52,-0.11 0.71,-0.29 C 35.891,34.52 36,34.26 36,34 c 0,-0.26 -0.109,-0.521 -0.29,-0.71 -0.38,-0.37 -1.04,-0.37 -1.42,0 z m -10,0 C 24.109,33.479 24,33.74 24,34 c 0,0.27 0.109,0.529 0.29,0.71 0.19,0.18 0.45,0.29 0.71,0.29 0.26,0 0.52,-0.11 0.699,-0.29 C 25.89,34.52 26,34.27 26,34 c 0,-0.26 -0.109,-0.521 -0.29,-0.71 -0.38,-0.37 -1.04,-0.37 -1.42,0 z"/></svg>')
          . '" height="16px" width="21px" alt="' . $altString . '" /></a>';
      $scriptString = '<script async="async" src="' . $domainUrlString . self::CLIENT_SCRIPT_PATH . '"></script>';
      $styleString = '<style>.rp-widget-link-container{text-align:right;}</style>';

      return $linkString . $scriptString . $styleString;
   }

   private static function getCssUrl($argsArray, $domainUrlString) {
      $cssUrlString = $domainUrlString . CarRoutePlanner::CSS_PATH;
      $colorParams = array();
      if (isset($argsArray['text_color'])) {
         $colorParams[] = 'tc=' . str_replace('#', '', $argsArray['text_color']);
      }
      if (isset($argsArray['background_color'])) {
         $colorParams[] = 'bc=' . str_replace('#', '', $argsArray['background_color']);
      }
      if (isset($argsArray['accent_color'])) {
         $colorParams[] = 'pc=' . str_replace('#', '', $argsArray['accent_color']);
      }
      $cssUrlString .= '?' . join('&', $colorParams);

      return $cssUrlString;
   }

   private static function getSiteDomain() {
      return parse_url(get_site_url(), PHP_URL_HOST);
   }
}