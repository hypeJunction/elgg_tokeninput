<?php
/**
 * Displays a tokenizing autocomplete input
 *
 * You can change the default configuration of the autocomplete instance by, prefixing the configuration parameter with data- and hyphenating capitalized parameter names, e.g.
 * to modify the hintText value, use $vars['data-hint-text']
 * 
 * @uses $vars['class'] Optional. Additional CSS class
 * @uses $vars['name'] Input name
 * @uses $vars['value'] Current value (a guid, an array of guids or an array of entities or an array of tags)
 * @uses $vars['multiple'] Allow multipe inputs
 * @uess $vars['limit'] Limit number of tokens to a certain value
 *
 * @uses $vars['callback'] Callback function used to perform the search
 * @uses $vars['endpoint'] Endpoint to use for seach. Default '/tokeninput'
 * @uses $vars['query'] Additional options to be passed as key-value parameters with the URL query
 *
 * @uses $vars['strict'] Toggle strict mode. If set to false, free input mode will be enabled and user will be given an option to add an arbitrary value, if no matching records found
 * @uses $vars['autoexplode'] Attempt to explode values passed to the action into an array. This will add additional hidden inputs that will be used by 'action','all' hook
 * @uses $vars['sortable'] Make tokens sortable
 *
 */
$vars['id'] = substr(md5(microtime() . rand()), 0, 10);

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
if ($value) {
	if (is_string($value)) {
		$delimiter = elgg_extract('data-token-delimiter', $vars, ',');
		$value = explode($delimiter, $value);
	}
} else {
	$value = array();
}

foreach ($value as $selected) {
	if (!empty($vars['is_elgg_autocomplete'])) {
		// Elgg autocomplete uses usernames
		$user = get_user_by_username($selected);
		if ($user) {
			$selected = $user;
		}
	}
	$values[] = elgg_tokeninput_export_entity($selected);
}
$vars['data-pre-populate'] = json_encode($values);

// Limit number of possible values
if (isset($vars['limit'])) {
	$limit = elgg_extract('limit', $vars, null);
	unset($vars['limit']);
}
$vars['data-token-limit'] = (!$vars['multiple']) ? 1 : $limit;
if (elgg_extract('sortable', $vars, false) && $vars['data-token-limit'] !== 1) {
	$vars['data-sortable'] = true;
}

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

// Add strict mode value to the URL query
$strict = elgg_extract('strict', $vars, true);
$query['strict'] = $strict;
$vars['data-allow-free-tagging'] = !$strict;
unset($vars['strict']);

if (isset($vars['callback'])) {
	$query['callback'] = $vars['callback'];
	unset($vars['callback']);

	$query['ts'] = time();
	$query['hmac'] = hash_hmac('sha256', $query['ts'] . $query['callback'], elgg_tokeninput_get_secret());
}

$endpoint = elgg_extract('endpoint', $vars, '/tokeninput');
unset($vars['endpoint']);

$vars['data-href'] = urldecode(elgg_http_add_url_query_elements(elgg_normalize_url($endpoint), $query));

$vars['data-placeholder'] = elgg_extract('placeholder', $vars, elgg_echo('tokeninput:text:placeholder'));

$autoexplode = elgg_extract('autoexplode', $vars, true);
unset($vars['autoexplode']);

if ($autoexplode) {
	// Add a hidden field to use in the action hook to unserialize the values
	echo elgg_view('input/hidden', array(
		'name' => 'elgg_tokeninput_fields[]',
		'value' => $vars['name']
	));
	if (!empty($vars['is_elgg_autocomplete'])) {
		echo elgg_view('input/hidden', array(
			'name' => 'elgg_tokeninput_autocomplete[]',
			'value' => $vars['name']
		));
	}
}

echo elgg_format_element('input', $vars);
