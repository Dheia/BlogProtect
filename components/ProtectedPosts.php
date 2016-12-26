<?php namespace KurtJensen\BlogProtect\Components;

use KurtJensen\BlogProtect\Models\Settings;
use RainLab\Blog\Components\Posts;
use RainLab\Blog\Models\Category as BlogCategory;
use RainLab\Blog\Models\Post as BlogPost;

class ProtectedPosts extends Posts {
	public $permarray = [];

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.blogprotect::lang.posts.name',
			'description' => 'kurtjensen.blogprotect::lang.posts.description',
		];
	}

	protected function listPosts() {
		$category = $this->category ? $this->category->id : null;

		/*
			         * List all the posts, eager load their categories
		*/

		$posts = BlogPost::
			with('categories')->
			whereHas('categories', // Added to query to limit categories
			function ($q) {
				$q->whereIn('permission_id', $this->permarray);
			})->
			listFrontEnd([
			'page' => $this->property('pageNumber'),
			'sort' => $this->property('sortOrder'),
			'perPage' => $this->property('postsPerPage'),
			'search' => trim(input('search')),
			'category' => $category,
			'exceptPost' => $this->property('exceptPost'),
		]);

		/*
			         * Add a "url" helper attribute for linking to each post and category
		*/
		$posts->each(function ($post) {
			$post->setUrl($this->postPage, $this->controller);

			$post->categories->each(function ($category) {
				$category->setUrl($this->categoryPage, $this->controller);
			});
		});

		return $posts;
	}

	protected function loadCategory() {
		// Load permissions
		$akeys = array_keys(\KurtJensen\Passage\Plugin::passageKeys());
		$this->permarray = array_merge($akeys, [Settings::get('public_perm')]);

		if (!$slug = $this->property('categoryFilter')) {
			return null;
		}

		$category = new BlogCategory;

		$category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
		? $category->transWhere('slug', $slug)
		: $category->where('slug', $slug);

		$category = $category->
			whereIn('permission_id', $this->permarray)->// Added to query to limit categories
			first();

		return $category ?: null;
	}
}
