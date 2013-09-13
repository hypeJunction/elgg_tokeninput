<?php

$vars['name'] = 'members';
$vars['callback'] = 'elgg_tokeninput_search_users';
$vars['multiple'] = true;

echo elgg_view('input/tokeninput', $vars);
