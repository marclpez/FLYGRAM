<?php

add_action ('wp', 'bps_set_request');
function bps_set_request ()
{
	bps_set_directory ();

	if (isset ($_REQUEST['bps_debug']))
	{
		$cookie = apply_filters ('bps_cookie_name', 'bps_debug');
		setcookie ($cookie, 1, 0, COOKIEPATH);
	}

	$showing_errors = isset ($_REQUEST['bps_errors']);
	$persistent = bps_get_option ('persistent', '1') || $showing_errors;
	$new_search = isset ($_REQUEST[bp_core_get_component_search_query_arg ('members')]);

	if ($new_search || !$persistent)
		if (!isset ($_REQUEST[BPS_FORM]))  $_REQUEST[BPS_FORM] = 'clear';

	$cookie = apply_filters ('bps_cookie_name', 'bps_request');
	if (isset ($_REQUEST[BPS_FORM]))
	{
		if ($_REQUEST[BPS_FORM] != 'clear')
		{
			$_REQUEST['bps_directory'] = bps_current_page ();
			setcookie ($cookie, http_build_query ($_REQUEST), 0, COOKIEPATH);

			list (, $errors) = bps_get_form_fields ($_REQUEST[BPS_FORM]);
			if ($errors)  _bps_redirect_on_errors ($errors);
		}
		else
		{
			setcookie ($cookie, '', 0, COOKIEPATH);
		}
	}
	else if ($showing_errors)
	{
		setcookie ($cookie, '', 0, COOKIEPATH);
	}
}

function bps_get_request2 ($type, $form=0)		// published interface, 20190324
{
	$request = _bps_clean_request ();

	if (!empty ($request))  switch ($type)
	{
	case 'form':
		if ($request[BPS_FORM] != $form)  $request = array ();
		break;

	case 'filters':
	case 'search':
		$current = bps_current_page ();
		if (empty ($request['bps_directory']) || $request['bps_directory'] != $current)  $request = array ();
		break;
	}

	$request = apply_filters ('bps_request', $request, $type, $form);
	if (bps_debug ())
	{
		echo "<!--\n";
		echo "type $type, $form\n";
		echo "request "; print_r ($request);
		echo "-->\n";
	}

	return $request;
}

function _bps_clean_request ()
{
	static $clean;
	if (isset ($clean))  return $clean;

	$request = $_REQUEST;
	if (empty ($request[BPS_FORM]))
	{
		$cookie = apply_filters ('bps_cookie_name', 'bps_request');
		if (empty ($_COOKIE[$cookie]))
		{
			$clean = array ();				// no search
		}
		else
		{
			parse_str (stripslashes ($_COOKIE[$cookie]), $request);
			if (empty ($request[BPS_FORM]))
				$clean = array ();					// bad cookie
			else if ($request[BPS_FORM] == 'clear')
				$clean = array ();					// bad cookie
			else
				$clean = bps_clean ($request);		// saved search
		}
	}
	else if ($request[BPS_FORM] == 'clear')
	{
		$clean = array ();				// clear search
	}
	else
	{
		$clean = bps_clean ($request);	// new search
	}

	return $clean;
}

function bps_clean ($request)		// $request[BPS_FORM] is set and != 'clear'
{
	$clean = array ();

	$form = $clean[BPS_FORM] = $request[BPS_FORM];
	$meta = bps_meta ($form);

	foreach ($meta['field_code'] as $k => $code)
	{
		$filter = $meta['field_mode'][$k];
		$key = bps_key ($code, $filter);
		if (!isset ($request[$key]))  continue;
		if (bps_Fields::is_empty_value ($request[$key], $filter))  continue;

		$clean[$key] = $request[$key];
	}

	$clean['bps_directory'] = $request['bps_directory'];
	return $clean;
}

function bps_get_form_fields ($form)
{
	static $form_fields = array ();
	static $errors = array ();

	if (isset ($form_fields[$form]))  return [$form_fields[$form], $errors[$form]];

	list (, $fields) = bps_get_fields ();
	$request = bps_get_request ('form', $form);

	$form_fields[$form] = array ();
	$errors[$form] = 0;
	$meta = bps_meta ($form);
	foreach ($meta['field_code'] as $k => $code)
	{
		if (empty ($fields[$code]))  continue;

		$f = clone $fields[$code];

		$filter = $meta['field_mode'][$k];
		$f->display = bps_Fields::get_display ($f, $filter);
		if ($f->display == false)  continue;

		switch ($f->display)
		{
		case 'selectbox':
			$f->options = array ('' => '') + $f->options;
			break;

		case 'multiselectbox':
			$f->multiselect_size = 4;
			break;
		}

		$label = $meta['field_label'][$k];
		$f->label = $label? bps_wpml ($form, $code, 'label', $label): $f->name;

		if ($label)		// to be removed
			$form_fields[$form][] = bps_set_hidden_field ($code. '_label', $f->label);

		$description = $meta['field_desc'][$k];
		if ($description == '-')
			$f->description = '';
		else if ($description)
			$f->description = bps_wpml ($form, $code, 'comment', $description);

		$f->form_id = $form;
		$f->value = bps_Fields::get_empty_value ($filter);
		$f->html_name = bps_key ($code, $filter);
		$f->mode = bps_Fields::get_filter_label ($filter);
		$f->required = (strpos ($f->label, '*') === 0);
		$f->error_message = '';

		do_action ('bps_field_before_search_form', $f);

		if (!empty ($request))
		{
			$key = $f->html_name;
			if (isset ($request[$key]))
			{
				$f->filter = $filter;
				$f->value = $request[$key];
			}

			$f->error_message = _bps_validate_field ($f);
			if ($f->error_message)  $errors[$form] += 1;
		}

		$form_fields[$form][] = $f;
	}

	return [$form_fields[$form], $errors[$form]];
}

function _bps_validate_field ($f)
{
	$error_message = '';
	$value = $f->value;
	$required = $f->required;
	$display = $f->display;
	if ($display == 'textbox' && $f->format == 'decimal')  $display = 'decimal';

	switch ($display)
	{
	case 'textbox':
		$exp = bps_is_expression ($value);
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter a value or a search expression', 'bp-profile-search');
		else if (($exp == 'and' || $exp == 'mixed') && $f->filter == '')
			$error_message = __('AND expression not allowed here, use only OR', 'bp-profile-search');
		else if ($exp == 'mixed')
			$error_message = __('mixed expression not allowed, use only AND or only OR', 'bp-profile-search');
		break;

	case 'integer':
	case 'decimal':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter a value', 'bp-profile-search');
		break;

	case 'integer-range':
	case 'range':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter at least a value', 'bp-profile-search');
		break;

	case 'date':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter a date', 'bp-profile-search');
		break;

	case 'date-range':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter at least a date', 'bp-profile-search');
		break;

	case 'distance':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please enter a distance and select a location', 'bp-profile-search');
		else if ($value['distance'] === '' && $value['location'] !== '')
			$error_message = __('please enter a distance', 'bp-profile-search');
		else if ($value['distance'] !== '' && $value['location'] === '')
			$error_message = __('please select a location', 'bp-profile-search');
		break;

	case 'radio':
	case 'selectbox':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please select an option', 'bp-profile-search');
		break;

	case 'checkbox':
	case 'multiselectbox':
	case 'range-select':
		if ($required && !isset ($f->filter))
			$error_message = __('this field is required, please select at least an option', 'bp-profile-search');
		break;
	}

	$error_message = apply_filters ('bps_validate_field', $error_message, $f);
	return $error_message;
}

function bps_key ($code, $filter)
{
	$key = ($filter == '')? $code: $code. '_'. $filter;
	return $key;
}

function bps_debug ()
{
	$cookie = apply_filters ('bps_cookie_name', 'bps_debug');
	return isset ($_REQUEST['bps_debug'])? true: isset ($_COOKIE[$cookie]);
}

function _bps_redirect_on_errors ($errors)
{
	$redirect = parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
	$redirect = add_query_arg ('bps_errors', $errors, $redirect);
	wp_safe_redirect ($redirect);
	exit;
}
