<?php

/**
 * Shortcut view to display friends picker
 */

$vars['callback'] = 'elgg_tokeninput_search_friends';

echo elgg_view('input/tokeninput', $vars);
