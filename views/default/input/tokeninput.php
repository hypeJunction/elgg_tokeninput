<?php

/**
 * Displays a tokenizing autocomplete input
 *
 * You can change the default configuration of the autocomplete instance by, prefixing the configuration parameter with data- and hyphenating capitalized parameter names, e.g.
 * to modify the hintText value, use $vars['data-hint-text']
 * 
 * @uses $vars['class'] Optional. Additional CSS class
 * @uses $vars['name'] Input name
 * @uses $vars['value'] Current value (a guid, an array of guids or an array of entities)
 * @uses $vars['multiple'] Allow multipe inputs
 *
 * @uses $vars['callback'] Callback function used to perform the search
 * @uses $vars['query'] Additional options to be passed as query elements
 */
$vars['id'] = substr(md5(microtime() . rand()), 0, 10);

elgg_load_library('elgg.tokeninput');

elgg_load_js('jquery.tokeninput.js');
elgg_load_js('elgg.tokeninput.js');
elgg_load_css('elgg.tokeninput.css');

if (!isset($vars['name'])) {
	$vars['name'] = 'tokeninput';
}

// Add tokeninput class for JS initialization
if (isset($vars['class'])) {
	$vars['class'] = "elgg-input-tokeninput {$vars['class']}";
} else {
	$vars['class'] = "elgg-input-tokeninput";
}

// Set input type
$vars['type'] = 'text';

// Prepare values
$value = elgg_extract('value', $vars, array());
if ($value && !is_array($value)) {
	$value = array($value);
}
foreach ($value as $selected) {
	$values[] = elgg_tokeninput_export_entity($selected);
}
$vars['data-pre-populate'] = ($values) ? json_encode($values) : '[]';

// Limit number of possible values
$vars['data-token-limit'] = (!$vars['multiple']) ? 1 : null;


// Prepare query
if (isset($vars['query'])) {
	$query = elgg_extract('query', $vars);
	unset($vars['query']);
}

if ($query && !is_array($query)) {
	$query = array($query);
} else if (empty($query)) {
	$query = array();
}

if (isset($vars['callback'])) {
	$query['callback'] = $vars['callback'];
	unset($vars['callback']);
}

$vars['href'] = elgg_http_add_url_query_elements(elgg_normalize_url(ELGG_TOKENINPUT_PAGEHANDLER), $query);

$attributes = elgg_format_attributes($vars);

// Add a hidden field to use in the action hook to unserialize the values
echo elgg_view('input/hidden', array(
	'name' => 'elgg_tokeninput_fields[]',
	'value' => $vars['name']
));

echo "<input $attributes />";