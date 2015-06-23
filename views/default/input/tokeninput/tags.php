<?php

/**
 * @uses $vars['tag_names'] Tag names to search in. Defaults to registered tag metadata names.
 */

$vars['strict'] = false;
$vars['callback'] = 'elgg_tokeninput_search_tags';
$vars['multiple'] = true;

if (is_string($vars['value'])) {
	$vars['value'] = string_to_tag_array($vars['value']);
}

if (isset($vars['tag_names'])) {
	$vars['query']['tag_names'] = (is_array($vars['tag_names'])) ? implode(',', $vars['tag_names']) : $vars['tag_names'];
	unset($vars['tag_names']);
}

echo elgg_view('input/tokeninput', $vars);