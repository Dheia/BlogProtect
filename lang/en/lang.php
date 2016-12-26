<?php

return [
	'plugin' => [
		'name' => 'BlogProtect',
		'description' => 'Restrict RainLab Blog Post viewers by category permission',
		'permission_label' => 'Change Blog Protect Settings',
	],
	'settings' => [
		'label' => 'Blog Protect',
		'description' => 'Configure blog category protection.',
	],

	'categories' => [
		'name' => 'Protected Category',
		'description' => 'Displays a list of protected blog categories on the page.',
	],

	'post' => [
		'name' => 'Protected Post',
		'description' => 'Displays a protected blog post on the page.',
	],

	'posts' => [
		'name' => 'Protected Post List',
		'description' => 'Displays a list of latest protected blog posts on the page.',
	],

	'rssfeed' => [
		'title' => 'Protected RSS Feed',
		'description' => 'Generates an RSS feed containing protected posts from the blog.',
	],

	'added_columns' => [
		'permission_id_label' => 'Permision',
		'category_id_label' => 'ID',
	],

	'added_fields' => [
		'permission_id_label' => 'Permision for this Category',
		'permission_id_comment' => 'Set the permision for this category of posts.',
	],
];