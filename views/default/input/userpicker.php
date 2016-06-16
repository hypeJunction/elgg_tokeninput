<?php

if (empty($vars['name'])) {
	$vars['name'] = 'members';
}

$vars['callback'] = 'elgg_tokeninput_search_users';

$limit = elgg_extract('limit', $vars);
if (!$limit || $limit > 1) {
	$vars['multiple'] = true;
} else {
	$vars['multiple'] = false;
	$vars['autoexplode'] = false;
}

if (isset($vars['values'])) {
	$vars['value'] = elgg_extract('values', $vars);
	unset($vars['values']);
}

echo elgg_view('input/tokeninput', $vars);
