<?php

namespace com\cminds\maplocations\helper;

use com\cminds\maplocations\shortcode\LocationSnippetShortcode;

use com\cminds\maplocations\controller\RouteController;

use com\cminds\maplocations\model\Settings;

use com\cminds\maplocations\controller\FrontendController;

use com\cminds\maplocations\model\Category;

use com\cminds\maplocations\model\Route;

use com\cminds\maplocations\model\Attachment;

use com\cminds\maplocations\model\Labels;

use com\cminds\maplocations\App;

class RouteView {
	
	
	static protected $travelModeIcons = array(
		'WALKING' => 'dashicons dashicons-universal-access',
		'BICYCLING' => 'fa fa-bicycle',
		'DRIVING' => 'fa fa-car',
		'DIRECT' => 'dashicons dashicons-sticky'
	);
	

	static function displayTermsInlineNav($title, $class, array $items) {
		?><div class="cmloc-route-<?php echo $class; ?>">
			<strong><?php echo $title; ?>:</strong>
			<ul class="cmloc-route-<?php echo $class; ?>-list cmloc-inline-nav"><?php
				foreach ($items as $item):
					printf('<li><a href="%s">%s</a></li>',
						esc_attr($item->getPermalink()),
						esc_html($item->getName())
					);
				endforeach; ?>
			</ul>
		</div><?php
	}
	
	
	static function displayImages(array $images, $class, $id) {
		?><ul class="cmloc-inline-gallery"><?php foreach ($images as $image):
			printf('<li><a href="%s" class="cmloc-gallery" rel="gallery-%s-%d"><img src="%s" alt="%s" /></a></li>',
				esc_attr($image->isImage() ? $image->getImageUrl(Attachment::IMAGE_SIZE_LARGE) : $image->getUrl()),
				$class,
				$id,
				esc_attr($image->getImageUrl(Attachment::IMAGE_SIZE_MEDIUM)),
				esc_attr('Image')
			);
		endforeach; ?></ul><?php
	}
	
	
	static function displayRating(Route $route) {
		$canRate = ($route->canRate() AND !$route->didUserRate());
		$rateTitle = Labels::getLocalized('rate_btn_title');
		$out = sprintf('<ul class="cmloc-rating" data-rating="%d" data-can-rate="%d">',
			round($route->getRate()),
			($canRate ? 1 : 0)
		);
		for ($i=1; $i<=5; $i++) {
			$out .= sprintf('<li data-rate="%d"%s></li>',
				$i,
				($canRate ? ' title="'. esc_attr(sprintf($rateTitle, $i)) .'"' : '')
			);
		}
		$out .= '</ul>';
		$out .= '<span class="cmloc-votes-number">('. intval($route->getVotesNumber()) .')</span>';
		return $out;
	}
	
	
	static function categoriesFilter($currentCategoryId, array $categories, $parent = 0, $depth = 0) {
		$out = '';
		if (!empty($categories[$parent])) {
			foreach ($categories[$parent] as $categoryId => $category) {
				$url = home_url('/' . Category::getUrlPart()) . '/' . $category->getSlug() .'/';
				$out .= sprintf('<option value="%s"%s>%s</option>',
					esc_attr($url),
					selected($currentCategoryId, $categoryId, $echo = false),
					str_repeat('&ndash;', $depth) . ' ' . esc_html($category->getName())
				);
				$out .= self::categoriesFilter($currentCategoryId, $categories, $categoryId, $depth+1);
			}
		}
		return $out;
	}
	
	
	static function businessCategoriesFilter($currentCategoryId, array $categories, $parent = 0, $depth = 0) {
		$out = '';
		if (!empty($categories[$parent])) {
			foreach ($categories[$parent] as $categoryId => $category) {
				$out .= sprintf('<option value="%s"%s>%s</option>',
					esc_attr($categoryId),
					selected($currentCategoryId, $categoryId, $echo = false),
					str_repeat('&ndash;', $depth) . ' ' . esc_html($category->getName())
				);
				$out .= self::categoriesFilter($currentCategoryId, $categories, $categoryId, $depth+1);
			}
		}
		return $out;
	}
	
	
	static function getRefererUrl() {
		$isTheSameHost = function($a, $b) {
			return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
		};
		$canUseReferer = (!empty($_SERVER['HTTP_REFERER'])
			AND $isTheSameHost($_SERVER['HTTP_REFERER'], site_url())
		);
		if (!empty($_GET['backlink'])) { // GET backlink param
			return base64_decode(urldecode($_GET['backlink']));
		}
		else if (!empty($_POST['backlink'])) { // POST backlink param
			return $_POST['backlink'];
		}
		else if ($canUseReferer) { // HTTP referer
			return $_SERVER['HTTP_REFERER'];
		} else { // index page
    		return FrontendController::getUrl();
    	}
	}
	
	
	static function getDisplayParams(array $displayParams) {
		$out = '';
		foreach ($displayParams as $param) {
			$out .= ' data-show-param-'. str_replace('_', '-', str_replace('_cmloc_', '', $param)) .'="1"';
		}
		return $out;
	}
	
	
	static function getTravelModeMenu($currentTravelMode, $showTitle = true, $labelsAsTooltip = false) {
		$out = '';
		if ($showTitle) $out .= sprintf('<li><strong>%s:</strong></li>', Labels::getLocalized('travel_mode'));
		foreach (Route::$travelModes as $mode) {
			if (!empty(self::$travelModeIcons[$mode])) {
				$iconClass = self::$travelModeIcons[$mode];
			} else {
				$iconClass = '';
			}
			$title = Labels::getLocalized('travel_mode_'. strtolower($mode));
			$out .= sprintf('<li%s><a href="" data-mode="%s"%s><i class="%s"></i>%s</a></li>',
				($currentTravelMode == $mode ? ' class="current"' : ''),
				esc_attr(strtoupper($mode)),
				($labelsAsTooltip ? ' title="Travel mode: '. esc_attr($title) .'"' : ''),
				$iconClass,
				($labelsAsTooltip ? '' : ' '. esc_html($title))
			);
		}
		return sprintf('<ul class="cmloc-inline-nav cmloc-route-travel-mode">%s</ul>', $out);
	}
	
	
	static function getFeaturedImage(Route $route, array $atts) {
		if (empty($atts['featured'])) {
			$atts['featured'] = Settings::getOption(Settings::OPTION_ROUTE_INDEX_FEATURED_IMAGE);
		}
		if (LocationSnippetShortcode::FEATURED_NONE == $atts['featured']) {
			return '';
		} else {
			if (LocationSnippetShortcode::FEATURED_ICON == $atts['featured'] AND $iconUrl = $route->getIconUrl()) {
				$imageUrl = $iconUrl;
			}
			else if ($images = $route->getImages()) {
				$imageUrl = $images[0]->getImageUrl(Attachment::IMAGE_SIZE_THUMB);
			} else {
				$imageUrl = Settings::getOption(Settings::OPTION_ROUTE_DEFAULT_IMAGE);
			}
			return sprintf('<a href="%s"><img src="%s" alt="Image" /></a>',
				esc_attr($route->getPermalink()),
				esc_attr($imageUrl)
			);
		}
	}
	
	
	static function getFullMap(Route $route, $atts, $mapId = null) {
		if (empty($mapId)) {
			$mapId = 'cmloc-route-'. mt_rand();
		}
		echo RouteController::displaySingleMap($route, $atts, $mapId);
// 		echo RouteController::loadFrontendView('single-map', compact('route', 'mapId', 'atts'));
	}
	
}
