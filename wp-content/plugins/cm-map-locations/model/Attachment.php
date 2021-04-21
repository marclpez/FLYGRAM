<?php

namespace com\cminds\maplocations\model;

use com\cminds\maplocations\App;

class Attachment extends PostType {
	
	const POST_TYPE = 'attachment';
	
	const IMAGE_SIZE_THUMB = 'thumbnail';
	const IMAGE_SIZE_MEDIUM = 'medium';
	const IMAGE_SIZE_LARGE = 'large';
	const IMAGE_SIZE_FULL = 'full';
	
	const META_WP_ATTACHED_FILE = '_wp_attached_file';
	
	const UPLOAD_DIR = 'cmloc';
	const UPLOAD_DIR_LOCATION_ICONS = 'location-icons';
	const UPLOAD_DIR_MEDIA = 'media';
	
	
	static $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');
	
	
	static function bootstrap() {
		parent::bootstrap();
		add_filter('wp_get_attachment_url', function($url, $postId) {
			if ($post = get_post($postId) AND $post->post_type == Attachment::POST_TYPE AND $post->post_mime_type == 'video/youtube') {
				$url = get_post_meta($postId, Attachment::META_WP_ATTACHED_FILE, true);
			}
			return $url;
		}, 10, 2);
	}
	

	/**
	 * Get instance
	 *
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\maplocations\model\Attachment
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	static function registerPostType() {
		// do not register
	}
	
	
	static function getForPost($postId) {
		$posts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => Attachment::POST_TYPE,
			'post_status' => 'any',
			'post_parent' => $postId,
			'orderby' => 'menu_order',
			'order' => 'asc',
// 			'cache_results' => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		));
		return array_filter(array_map(array(__CLASS__, 'getInstance'), $posts));
	}
	
	
	/**
	 * 
	 * @param unknown $url
	 * @return \com\cminds\maplocations\model\Attachment
	 */
	static function getByUrl($url) {
		global $wpdb;
		if ($path = parse_url($url, PHP_URL_PATH)) {
			$dir = wp_upload_dir();
			$path = substr($path, strlen(parse_url($dir['baseurl'], PHP_URL_PATH))+1, 9999);
			$sql = $wpdb->prepare("SELECT p.* FROM $wpdb->postmeta m
				JOIN $wpdb->posts p ON p.ID = m.post_id
				WHERE m.meta_key = %s
				AND m.meta_value LIKE %s
				AND p.post_type = %s",
				self::META_WP_ATTACHED_FILE,
				$path,
				static::POST_TYPE
			);
			$post = $wpdb->get_row($sql);
			if ($post) {
				return static::getInstance($post);
			}
		}
	}
	
	
	static function create($filePath, $mimeType, $parentPostId, $title = '', $description = '') {
		if (empty($mimeType)) {
			$type = wp_check_filetype( basename( $filePath ), null );
			$mimeType = $filetype['type'];
		}
		if (empty($title)) {
			$title = sanitize_title(basename($filePath));
		}
		$post = array(
			'post_title' => $title,
			'post_content' => $description,
			'post_status' => 'inherit',
			'post_mime_type' => $mimeType,
		);
		$attach_id = wp_insert_attachment($post, $filePath, $parentPostId);
	
		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filePath );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	
		return $attach_id;
	
	}
	
	
	function getImageUrl($size = self::IMAGE_SIZE_FULL, $icon = false) {
		if ($this->isImage()) {
			$result = wp_get_attachment_image_src($this->getId(), $size, $icon);
			if (!empty($result[0])) {
				return $result[0];
			}
		}
		else if ($this->isVideo()) {
			return App::url('asset/img/play-video.png');
		}
	}
	

	function getUrl() {
		return wp_get_attachment_url($this->getId());
	}
	
	
	function isImage() {
		return (strpos($this->post->post_mime_type, 'image') !== false);
	}
	
	function isVideo() {
		return (strpos($this->post->post_mime_type, 'video') !== false);
	}

	
	static function createYouTube($parentPostId, $url) {
		$attachment = new static(array(
			'post_parent' => $parentPostId,
			'post_author' => get_current_user_id(),
			'post_type' => static::POST_TYPE,
			'post_status' => 'inherit',
			'ping_status' => 'closed',
			'comment_status' => 'closed',
			'post_mime_type' => 'video/youtube',
		));
		$attachment->save();
		update_post_meta($attachment->getId(), '_wp_attached_file', $url);
		return $attachment;
	}
	
	
	static function isYouTubeUrl($url) {
		return preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//', $url);
	}
	
	

	static function upload($file, $targetDirectory, $addRandom = true) {
		if (is_uploaded_file($file['tmp_name'])) {
			$destination = trailingslashit($targetDirectory);
			if ($addRandom) {
				$destination .= md5(microtime() . $file['name'] . $file['tmp_name']) .'_';
			}
			preg_match('/^(.+)\.(\w+)$/', $file['name'], $match);
			$destination .= sanitize_title($match[1]) .'.'. strtolower($match[2]);
			if (move_uploaded_file($file['tmp_name'], $destination)) {
				chmod($destination, 0666);
				return $destination;
			} else throw new \Exception('Failed to move uploaded file.');
		} else throw new \Exception('This is not uploaded file.');
	}
	
	
	public static function getUploadDir($name) {
		$uploadDir = wp_upload_dir();
		if ($uploadDir['error']) {
			throw new \Exception(__('Error while getting wp_upload_dir():' . $uploadDir['error']));
		} else {
			$dir = $uploadDir['basedir'] . '/' . static::UPLOAD_DIR . '/' . $name . '/';
			if(!is_dir($dir)) {
				if(!wp_mkdir_p($dir)) {
					throw new \Exception(__('Script couldn\'t create the upload folder:' . $dir));
				}
			}
			return $dir;
		}
	}
	
	
	static function getUrlByPath($path) {
		$uploadDir = wp_upload_dir();
		if (!$uploadDir['error']) {
			return $uploadDir['baseurl'] . str_replace($uploadDir['basedir'], '', $path);
		}
	}
	
	
	
	static function imageResizeCrop($path, $maxWidth, $maxHeight) {
		$image = wp_get_image_editor( $path );
		if ( ! is_wp_error( $image ) ) {
			$image->resize( $maxWidth, $maxHeight, $crop = true );
			$image->save( $path );
		}
	}
	
	
	static function validateExtension($path, array $allowedExtensions) {
		$allowedExtensions = array_map('strtolower', $allowedExtensions);
		$p = explode('.', basename($path));
		$fileExt = strtolower(end($p));
		return in_array($fileExt, $allowedExtensions);
	}
	
	
	function getPostMimeType() {
		return $this->post_mime_type;
	}
	
	
	
	static function uploadMedia($file) {
		if (is_uploaded_file($file['tmp_name'])) {
			$destination = trailingslashit(static::getUploadDir(static::UPLOAD_DIR_MEDIA)) . md5(microtime() . $file['name'] . $file['tmp_name']) .'_'. $file['name'];
			if (move_uploaded_file($file['tmp_name'], $destination)) {
				chmod($destination, 0666);
				return $destination;
			} else throw new \Exception('Failed to move uploaded file.');
		} else throw new \Exception('This is not uploaded file.');
	}
	
	
	
}
