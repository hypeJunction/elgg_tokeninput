<?php

return [
	'plugin' => [
		'name' => 'Tokenizing Autocomplete',
		'version' => '6.0.0',
		'dependencies' => [
			'search' => [
				'position' => 'after',
			],
		],
	],

	'bootstrap' => \hypeJunction\Tokeninput\Bootstrap::class,

	'events' => [
		'action' => [
			'all' => [
				'elgg_tokeninput_explode_field_values' => ['priority' => 1],
			],
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
