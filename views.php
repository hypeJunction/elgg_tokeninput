<?php

$plugin_root = __DIR__;
$root = dirname(dirname($plugin_root));
$alt_root = dirname(dirname(dirname($initial_root)));

if (file_exists("$plugin_root/vendor/autoload.php")) {
	$path = $plugin_root;
} else if (file_exists("$root/vendor/autoload.php")) {
	$path = $root;
} else {
	$path = $alt_root;
}

return [
	'default' => [
		'jquery.tokeninput.js' => $path . '/vendor/bower-asset/jquery-tokeninput/build/jquery.tokeninput.min.js',

		// BC
		'tokeninput/lib.js' => $path . '/views/default/components/tokeninput.js',
		'tokeninput/stylesheet.css' => __DIR__ . '/views/default/components/tokeninput.css',
	]
];

