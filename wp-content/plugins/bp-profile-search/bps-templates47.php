<?php

function bps_escaped_filters_data47 ()
{
	list ($request, $full) = bps_template_args ();

	$F = new stdClass;
	$action = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$action = add_query_arg (BPS_FORM, 'clear', $action);
	$F->action = $full? esc_url ($action): '';
	$F->fields = array ();

	$fields = bps_parse_request ($request);
	foreach ($fields as $f)
	{
		if (!isset ($f->filter))  continue;
		if (!bps_Fields::set_display ($f, $f->filter))  continue;

		if (empty ($f->label))  $f->label = $f->name;

		$f->min = isset ($f->value['min'])? $f->value['min']: '';
		$f->max = isset ($f->value['max'])? $f->value['max']: '';
		$f->values = (array)$f->value;

		do_action ('bps_field_before_filters', $f);
		$F->fields[] = $f;
	}

	do_action ('bps_before_filters', $F);
	usort ($F->fields, 'bps_sort_fields');

	foreach ($F->fields as $f)
	{
		$f->label = esc_attr ($f->label);
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		foreach ($f->values as $k => $value)  $f->values[$k] = stripslashes ($value);

		foreach ($f->options as $key => $label)  $f->options[$key] = esc_attr ($label);
	}

	return $F;
}

function bps_sort_fields ($a, $b)
{
	return ($a->order <= $b->order)? -1: 1;
}

function bps_print_filter ($f)
{
	if (!empty ($f->options))
	{
		$values = array ();
		foreach ($f->options as $key => $label)
			if (in_array ($key, $f->values))  $values[] = $label;
	}

	switch ($f->filter)
	{
	case 'range':
	case 'age_range':
		if (!isset ($f->value['max']))
			return sprintf (esc_html__('min: %1$s', 'bp-profile-search'), $f->value['min']);
		if (!isset ($f->value['min']))
			return sprintf (esc_html__('max: %1$s', 'bp-profile-search'), $f->value['max']);
		return sprintf (esc_html__('min: %1$s, max: %2$s', 'bp-profile-search'), $f->value['min'], $f->value['max']);

	case '':
		if (isset ($values))
			return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $f->value);

	case 'contains':
		return sprintf (esc_html__('contains: %1$s', 'bp-profile-search'), $f->value);

	case 'like':
		return sprintf (esc_html__('is like: %1$s', 'bp-profile-search'), $f->value);

	case 'one_of':
		if (count ($values) == 1)
			return sprintf (esc_html__('is: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('is one of: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'match_any':
		if (count ($values) == 1)
			return sprintf (esc_html__('match: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('match any: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'match_all':
		if (count ($values) == 1)
			return sprintf (esc_html__('match: %1$s', 'bp-profile-search'), $values[0]);
		return sprintf (esc_html__('match all: %1$s', 'bp-profile-search'), implode (', ', $values));

	case 'distance':
		if ($f->value['units'] == 'km')
			return sprintf (esc_html__('is within: %1$s km of %2$s', 'bp-profile-search'), $f->value['distance'], $f->value['location']);
		return sprintf (esc_html__('is within: %1$s miles of %2$s', 'bp-profile-search'), $f->value['distance'], $f->value['location']);

	default:
		$output = apply_filters ('bps_filters_template_field', 'none', $f);
		if ($output != 'none')
			return $output;
		return "BP Profile Search: undefined filter <em>$f->filter</em>";
	}
}
