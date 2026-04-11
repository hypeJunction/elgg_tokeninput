<?php

return [
	'bootstrap' => \hypeJunction\Tokeninput\Bootstrap::class,

	'views' => [
		'default' => [
			'jquery.tokeninput.js' => __DIR__ . '/vendor/bower-asset/jquery-tokeninput/build/jquery.tokeninput.min.js',
		],
	],

	'routes' => [
		'tokeninput' => [
			'path' => '/tokeninput/{segments}',
			'resource' => 'tokeninput',
			'requirements' => [
				'segments' => '.+',
			],
			'defaults' => [
				'segments' => '',
			],
		],
	],

	'view_extensions' => [
		'elgg.css' => [
			'tokeninput/stylesheet.css' => [],
		],
		'admin.css' => [
			'tokeninput/stylesheet.css' => [],
		],
		'theme_sandbox/forms' => [
			'theme_sandbox/forms/elgg_tokeninput' => [],
		],
		'input/tokeninput' => [
			'tokeninput/require' => [],
		],
	],
];
