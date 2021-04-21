<?php



$displayCategories = function($parentId) use ($categories, &$displayCategories) {
	if (!empty($categories[$parentId]) AND is_array($categories[$parentId])) {
		foreach ($categories[$parentId] as $category) {
			echo '<li>';
			printf('<label><input type="checkbox" value="%s" /> %s</label>', esc_attr($category->getName()), esc_html($category->getName()));
			if (!empty($categories[$category->getId()])) {
				echo '<ul>';
				$displayCategories($category->getId());
				echo '</ul>';
			}
			echo '</li>';
		}
	}
};


?>

<div class="cmloc-map-category-filter">
	<ul>
		<?php $displayCategories(0); ?>
	</ul>
</div>