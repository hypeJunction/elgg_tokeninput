<?php

$path = '';
if (file_exists("$plugin_root/vendor/autoload.php")) {
	// composer was run locally
	$path = __DIR__ . '/';
}

return [
	'default' => [
		'js/jquery.tokeninput.js' => $path . 'vendor/bower-asset/jquery-tokeninput/build/jquery.tokeninput.min.js',
	]
];

