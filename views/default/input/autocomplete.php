<?php

if (isset($vars['match_on'])) {
	switch ($vars['match_on']) {
		default :
		case 'all' :
			break;

		case 'users' :
			$callback = 'elgg_tokeninput_search_users';
			break;

		case 'groups' :
			$callback = 'elgg_tokeninput_search_groups';
			break;

		case 'friends' :
			$callback = 'elgg_tokeninput_search_friends';
			break;
	}
	unset($vars['match_on']);
}

if (isset($vars['match_owner'])) {
	$callback = 'elgg_tokeninput_search_owned_entities';
	unset($vars['match_owner']);
}

$vars['callback'] = $callback;

echo elgg_view('input/tokeninput', $vars);