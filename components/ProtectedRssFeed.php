<?php namespace Asped\BlogProtect\Components;

use Asped\BlogProtect\Models\Settings;
use Winter\Blog\Components\RssFeed as RainRss;
use Winter\Blog\Models\Category as BlogCategory;
use Winter\Blog\Models\Post as BlogPost;

class ProtectedRssFeed extends RainRss {
	public $permarray = [];

	public function componentDetails() {
		return [
			'name' => 'asped.blogprotect::lang.rssfeed.title',
			'description' => 'asped.blogprotect::lang.rssfeed.description',
		];
	}

	protected function listPosts() {
		$category = $this->category ? $this->category->id : null;

		/*
			         * List all the posts, eager load their categories
		*/
		$posts = BlogPost::whereHas('categories', // Added to query to limit categories
			function ($q) {
				$q->whereIn('permission_id', $this->permarray);
			})->
			with('categories')->listFrontEnd([
			'sort' => $this->property('sortOrder'),
			'perPage' => $this->property('postsPerPage'),
			'category' => $category,
		]);

		/*
			         * Add a "url" helper attribute for linking to each post and category
		*/
		$posts->each(function ($post) {
			$post->setUrl($this->postPage, $this->controller);
		});

		return $posts;
	}

	protected function loadCategory() {
		// Load permissions
		$akeys = array_keys(app('PassageService')::passageKeys());
		$this->permarray = array_merge($akeys, [Settings::get('public_perm')]);

		if (!$categoryId = $this->property('categoryFilter')) {
			return null;
		}

		if (!$category = BlogCategory::whereSlug($categoryId)->
			whereIn('permission_id', $this->permarray)->// Added to query to limit categories
			first()) {
			return null;
		}

		return $category;
	}
}
