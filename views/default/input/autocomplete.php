<?php

if (isset($vars['match_on'])) {
	$match_on = (array) $vars['match_on'];
	unset($vars['match_on']);

	$match_on = array_values($match_on);
	$match_on = (count($match_on) > 1) ? 'all' : $match_on[0];

	switch ($match_on) {
		default :
		case 'all' :
			break;

		case 'users' :
			$callback = 'elgg_tokeninput_search_users';
			$vars['is_elgg_autocomplete'] = true;
			break;

		case 'groups' :
			$callback = 'elgg_tokeninput_search_groups';
			break;

		case 'friends' :
			$callback = 'elgg_tokeninput_search_friends';
			$vars['is_elgg_autocomplete'] = true;
			break;
	}
}

if (isset($vars['match_owner'])) {
	$callback = 'elgg_tokeninput_search_owned_entities';
	unset($vars['match_owner']);
}

$vars['callback'] = $callback;

echo elgg_view('input/tokeninput', $vars);
