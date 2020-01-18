<?php

return [
	'admin' => [
		'href' => '/admin',
		'name' => 'Admin',
		'child' => [
			'index' => [
				['href' => '/admin/users', 'name' => 'Admin user list'],
			],
		]
	],
	'main' => [
		'href' => '/edit',
		'name' => 'New VPN',
	]
];