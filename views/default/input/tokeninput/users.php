<?php

/**
 * Shortcut view to display users picker
 */

$vars['callback'] = 'elgg_tokeninput_search_users';

echo elgg_view('input/tokeninput', $vars);
