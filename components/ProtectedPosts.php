<?php namespace Asped\BlogProtect\Components;

use Asped\BlogProtect\Models\Settings;
use Winter\Blog\Components\Posts;
use Winter\Blog\Models\Category as BlogCategory;
use Winter\Blog\Models\Post as BlogPost;

class ProtectedPosts extends Posts {
	public $permarray = null;

	public function componentDetails() {
		return [
			'name' => 'asped.blogprotect::lang.posts.name',
			'description' => 'asped.blogprotect::lang.posts.description',
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
				$q->whereIn('permission_id', $this->getPermissions());
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

	protected function getPermissions() {
		if (!$this->permarray === null) {
			return $this->permarray;
		}
		$akeys = array_keys(app('PassageService')::passageKeys());
		$this->permarray = array_merge($akeys, [Settings::get('public_perm')]);
		return $this->permarray;
	}

	protected function loadCategory() {
		if (!$slug = $this->property('categoryFilter')) {
			return null;
		}

		$category = new BlogCategory;

		$category = $category->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')
		? $category->transWhere('slug', $slug)
		: $category->where('slug', $slug);

		$category = $category->
			whereIn('permission_id', $this->getPermissions())->// Added to query to limit categories
			first();

		return $category ?: null;
	}
}
