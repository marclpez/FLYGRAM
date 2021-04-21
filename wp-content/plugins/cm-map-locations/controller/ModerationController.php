<?php

namespace com\cminds\maplocations\controller;

use com\cminds\maplocations\model\Route;
use com\cminds\maplocations\model\Settings;
use com\cminds\maplocations\model\Labels;
use com\cminds\maplocations\lib\Email;

class ModerationController extends Controller {
	
	const PARAM_ACTION = 'cmloc_moderation_action';
	const PARAM_ROUTE_ID = 'post';
	const PARAM_CHALLENGE_SEED = 'cmloc_challenge_seed';
	const PARAM_CHALLENGE_RESULT = 'cmloc_challenge_result';
	const PARAM_REFERER = 'referer';
	const PARAM_NONCE = 'nonce';
	
	const ACTION_ACCEPT = 'accept';
	const ACTION_TRASH = 'trash';
	

	protected static $filters = array(
		'cmloc_options_config',
		'cmloc_editor_allowed_statuses',
		'cmloc_dashboard_msg' => array('args' => 3),
		'cmloc_email_headers',
		'post_row_actions' => array('args' => 2),
	);
	protected static $actions = array(
		'cmloc_route_after_save' => array('args' => 1),
		'cmloc_route_accepted_by_moderator' => array('args' => 1),
		'cmloc_route_trashed_by_moderator' => array('args' => 1),
		'init',
	);
	
	
	
	static function init() {
		$action = filter_input(INPUT_GET, static::PARAM_ACTION);
		if ($action) {
			$routeId = filter_input(INPUT_GET, static::PARAM_ROUTE_ID);
			$nonce = filter_input(INPUT_GET, static::PARAM_NONCE);
			if (!empty($nonce) AND wp_verify_nonce($nonce, $action)) {
				
				// Moderation from dashboard:
				
				if (static::ACTION_ACCEPT == $action AND $route = Route::getInstance($routeId)) {
					$route->acceptByModerator();
					if ($referer = filter_input(INPUT_GET, static::PARAM_REFERER)) {
						$url = $referer;
					} else {
						$url = add_query_arg('post_type', Route::POST_TYPE, admin_url('edit.php'));
					}
					wp_safe_redirect($url);
					exit;
				}
				
			} else {
				
				// Anonymous moderation from email link:
				
				$seed = filter_input(INPUT_GET, static::PARAM_CHALLENGE_SEED);
				$challengeResult = filter_input(INPUT_GET, static::PARAM_CHALLENGE_RESULT);
				if (!empty($routeId) AND !empty($seed) AND !empty($challengeResult)
						AND $challengeResult == static::generateChallenge($routeId, $action, $seed)
						AND $route = Route::getInstance($routeId) AND $route->getStatus() == 'pending') {
					
					switch ($action) {
						case static::ACTION_ACCEPT:
							$route->acceptByModerator();
							echo Labels::getLocalized('moderation_msg_published');
							break;
						case static::ACTION_TRASH:
							$route->trashByModerator();
							echo Labels::getLocalized('moderation_msg_trashed');
							break;
					}
					
					echo '<p><a href="'. esc_attr(site_url('/')) .'">'. Labels::getLocalized('moderation_msg_return_to_website') .'</a></p>';
					exit;
					
				}
			}
		}
	}
	
	
	static function cmloc_options_config($config) {
		
		$routeTokens = implode(' ', array_keys(Route::getShortcodeTokensFuncMap()));
		
		return array_merge($config, array(
			
			Settings::OPTION_ROUTE_MODERATION_ENABLE => array(
				'type' => Settings::TYPE_BOOL,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'default' => 0,
				'title' => 'Enable moderation',
				'desc' => 'If enabled, admin have to approve the location before it will be visible for the other users on the front-end.',
			),
			Settings::OPTION_ROUTE_MODERATION_EMAILS => array(
				'type' => Settings::TYPE_CSV_LINE,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'default' => get_bloginfo('admin_email'),
				'title' => 'Moderators email addresses',
				'desc' => 'Comma separated email addressess of the moderators that the notifications will be send to.',
			),
			
			// Notifications
			Settings::OPTION_MODERATOR_EMAIL_SUBJECT => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'moderation',
				'subcategory' => 'notifications',
				'default' => '[[blogname]] Location pending review: [name]',
				'title' => 'Moderator\'s email subject',
				'desc' => 'Subject of the notification email that moderator will receive after user created a location. '
						.'You can use the same shortcodes as in the content.',
			),
			Settings::OPTION_MODERATOR_EMAIL_CONTENT => array(
				'type' => Settings::TYPE_RICH_TEXT,
				'category' => 'moderation',
				'subcategory' => 'notifications',
				'default' => 'Please review the following location:<br><br>Location name: [name]<br>Preview: <a href="[permalink]">[permalink]</a>'
						. '<br><br><a href="[accepturl]">Accept location</a> or <a href="[trashurl]">Trash</a>',
				'title' => 'Moderator\'s email content template',
				'desc' => 'Template for the notification email that moderator will receive after user created a location. '
						.'You can use the following shortcodes:<br>[accepturl] [trashurl] [blogname] [siteurl] [ip] ' . $routeTokens,
			),
			Settings::OPTION_ROUTE_ACCEPTED_USER_EMAIL_SUBJECT => array(
				'type' => Settings::TYPE_STRING,
				'category' => 'moderation',
				'subcategory' => 'notifications',
				'default' => '[[blogname]] Your location has been [action]: [name]',
				'title' => 'User\'s notification email subject',
				'desc' => 'Subject of the notification email send to the location\'s author after moderator has performed an action. '
						.'You can use the same shortcodes as in the content.',
			),
			Settings::OPTION_ROUTE_ACCEPTED_USER_EMAIL_CONTENT => array(
				'type' => Settings::TYPE_RICH_TEXT,
				'category' => 'moderation',
				'subcategory' => 'notifications',
				'default' => 'Moderator has [action] your location: <a href="[permalink]">[name]</a>',
				'title' => 'User\'s notification email content',
				'desc' => 'Template for the notification email send to the location\'s author after moderator has performed an action. '
						.'You can use the following shortcodes:<br>[action] [blogname] [siteurl] ' . $routeTokens,
			),
			
		));
	}
	
	
	static function cmloc_route_after_save(Route $route) {
		if ($route->getStatus() == 'publish') {

            $userId = get_current_user_id();
            $moderationEnabled = apply_filters('cmloc_route_moderation_enabled',
                Settings::getOption(Settings::OPTION_ROUTE_MODERATION_ENABLE), $route->getId(), $userId);

            if (current_user_can('manage_options')) {
				// ok
			}
			else if ( $moderationEnabled AND !$route->isAcceptedByModerator()) {
				
				// Set status to pending
				$route->setStatus('pending');
				
				// Send notification email to the moderator
				$emails = Settings::getOption(Settings::OPTION_ROUTE_MODERATION_EMAILS);
				$tokens = Route::getShortcodeTokensFuncMap();
				foreach ($tokens as $token => &$func) {
					$func = call_user_func(array($route, $func));
				}
				$tokens['[blogname]'] = get_bloginfo('name');
				$tokens['[siteurl]'] = get_bloginfo('url');
				$tokens['[accepturl]'] = static::generateActionUrl($route->getId(), static::ACTION_ACCEPT);
				$tokens['[trashurl]'] = static::generateActionUrl($route->getId(), static::ACTION_TRASH);
				$tokens['[ip]'] = $_SERVER['REMOTE_ADDR'];
				$subject = strtr(Settings::getOption(Settings::OPTION_MODERATOR_EMAIL_SUBJECT), $tokens);
				$body = strtr(Settings::getOption(Settings::OPTION_MODERATOR_EMAIL_CONTENT), $tokens);
				Email::send($emails, $subject, $body);
				
			} else {
				// ok
			}
			$route->save();
// 			exit;
		}
	}
	
	
	static protected function generateActionUrl($routeId, $action) {
		$seed = mt_rand();
		$challengeResult = static::generateChallenge($routeId, $action, $seed);
		return add_query_arg(array(
			static::PARAM_ACTION => $action,
			static::PARAM_ROUTE_ID => $routeId,
			static::PARAM_CHALLENGE_SEED => $seed,
			static::PARAM_CHALLENGE_RESULT => $challengeResult,
		), site_url('/'));
	}
	
	
	static protected function generateChallenge($routeId, $action, $seed) {
		$input = implode('|', array(NONCE_KEY, $seed, $action, $routeId, NONCE_SALT));
		return sha1($input);
	}
	
	
	static function cmloc_editor_allowed_statuses(array $statuses) {
		$statuses['pending'] = Labels::getLocalized('location_status_pending');
		return $statuses;
	}
	
	
	static function cmloc_dashboard_msg($text, $msg, $class) {
		if ('route_save_success' == $msg AND $route = FrontendController::getRoute()) {
			if ($route->getStatus() == 'pending') {
				$text .= ' ' . Labels::getLocalized('location_save_success_pending');
			}
		}
		return $text;
	}
	
	
	static function cmloc_email_headers($headers) {
// 		if (Settings::getOption(Settings::OPTION_EMAIL_USE_HTML)) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
// 		}
		return $headers;
	}
	
	
	static function cmloc_route_accepted_by_moderator($route) {
		static::sendUserNotification($route, static::ACTION_ACCEPT);
	}
	
	
	static function cmloc_route_trashed_by_moderator($route) {
		static::sendUserNotification($route, static::ACTION_TRASH);
	}
	
	static function sendUserNotification(Route $route, $action) {
		
		$user = $route->getAuthor();
		if (!$user) return;
		
		$tokens = Route::getShortcodeTokensFuncMap();
		foreach ($tokens as $token => &$func) {
			$func = call_user_func(array($route, $func));
		}
		$tokens['[blogname]'] = get_bloginfo('name');
		$tokens['[siteurl]'] = get_bloginfo('url');
		$tokens['[action]'] = Labels::getLocalized('moderation_user_email_action_' . $action);
		$subject = strtr(Settings::getOption(Settings::OPTION_ROUTE_ACCEPTED_USER_EMAIL_SUBJECT), $tokens);
		$body = strtr(Settings::getOption(Settings::OPTION_ROUTE_ACCEPTED_USER_EMAIL_CONTENT), $tokens);
		Email::send($user->user_email, $subject, $body);
		
	}
	
	
	static function post_row_actions($actions, $post) {
		if ($post->post_type == Route::POST_TYPE AND $post->post_status == 'pending') {
			$url = add_query_arg(urlencode_deep(array(
				static::PARAM_ACTION => static::ACTION_ACCEPT,
				static::PARAM_ROUTE_ID => $post->ID,
				static::PARAM_NONCE => wp_create_nonce(static::ACTION_ACCEPT),
				static::PARAM_REFERER => $_SERVER['REQUEST_URI'],
			)), admin_url('admin.php'));
			$actions['cmloc_accept'] = sprintf('<a href="%s" onclick="return confirm(%s)" style="color:green">%s</a>',
				esc_attr($url),
				esc_attr(json_encode('Are you sure?')),
				__('Accept')
			);
		}
		return $actions;
	}
	
}
