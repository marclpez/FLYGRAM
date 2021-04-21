<?php

namespace com\cminds\maplocations\metabox;

use com\cminds\maplocations\App;

abstract class TermCustomField {
	
	const SLUG = '';
	const NAME = '';
	const FIELD_PRIORITY = 10;
	const SAVE_TERM_PRIORITY = 10;
	
	static protected $supportedTaxonomies = array();
	static protected $fields = array();
	
	
	static function bootstrap() {
		foreach (static::$supportedTaxonomies as $taxonomy) {
			add_action($taxonomy . '_add_form_fields', array(get_called_class(), 'displayFields'), static::FIELD_PRIORITY, 1);
			add_action($taxonomy . '_edit_form_fields', array(get_called_class(), 'displayFields'), static::FIELD_PRIORITY, 1);
			add_action('edited_' . $taxonomy, array(get_called_class(), 'afterSave'), static::SAVE_TERM_PRIORITY, 2);
			add_action('created_' . $taxonomy, array(get_called_class(), 'afterSave'), static::SAVE_TERM_PRIORITY, 2);
		}
	}
	
	
	static function save($term_id) {}
	
	
	static function displayFields($term = null) {
		static::renderNonceField($term);
		foreach (static::$fields as $fieldName => $fieldLabel) {
			$method = 'get_field_' . $fieldName;
			if (method_exists(get_called_class(), $method)) {
				$content = call_user_func(array(get_called_class(), $method), $fieldName, $term);
				if (strlen($content) > 0) {
					if (empty($term)) {
						echo '<div class="form-field field-'. esc_attr($fieldName) .'">
							<label for="'. esc_attr($fieldName) .'">'. $fieldLabel .'</label>
							'. $content .'
						</div>';
					} else {
						echo '<tr class="form-field field-'. esc_attr($fieldName) .'">
							<th scope="row"><label for="'. esc_attr($fieldName) .'">'. $fieldLabel .'</label></th>
							<td>'. $content .'</td>
						</tr>';
					}
				}
			}
		}
	}
	
	
	static function afterSave($term_id, $tt_id = null) {
		if (static::validateNonce($term_id)) {
			static::save($term_id);
		}
	}
	
	
	static function validateNonce($term_id) {
		$field = static::getNonceFieldName();
		return (!empty($_POST[$field]) AND wp_verify_nonce($_POST[$field], $field));
	}
	
	
	protected static function renderNonceField($term) {
		$field = static::getNonceFieldName();
		printf('<input type="hidden" name="%s" value="%s" />', $field, wp_create_nonce($field));
	}
	
	
	static function getNonceFieldName() {
		return static::getId() . 'nonce_term';
	}
	
	static function getId() {
		return App::prefix('-' . static::SLUG);
	}
	
}
