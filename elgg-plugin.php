<?php

return [
	'bootstrap' => \hypeJunction\Tokeninput\Bootstrap::class,

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
