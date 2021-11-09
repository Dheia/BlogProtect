<?php namespace Asped\BlogProtect\Components;

use Db;
use Asped\BlogProtect\Models\Settings;
use Winter\Blog\Components\Categories;
use Winter\Blog\Models\Category as BlogCategory;

class ProtectedCategories extends Categories {
	public $permarray = null;

	public function componentDetails() {
		return [
			'name' => 'asped.blogprotect::lang.categories.name',
			'description' => 'asped.blogprotect::lang.categories.description',
		];
	}

	protected function getPermissions() {
		if ($this->permarray === null) {
			$akeys = array_keys(app('PassageService')::passageKeys());
			$this->permarray = array_merge($akeys, [Settings::get('public_perm')]);
		}
		return $this->permarray;
	}

	protected function loadCategories() {

		$categories = BlogCategory::whereIn('permission_id', $this->getPermissions())->orderBy('name');

		if (!$this->property('displayEmpty')) {
			$categories->whereExists(function ($query) {
				$prefix = Db::getTablePrefix();
				$query->select(Db::raw(1))
					->from('winter_blog_posts_categories')
					->join('winter_blog_posts', 'winter_blog_posts.id', '=', 'winter_blog_posts_categories.post_id')
					->whereNotNull('winter_blog_posts.published')
					->where('winter_blog_posts.published', '=', 1)
					->whereRaw($prefix . 'winter_blog_categories.id = ' . $prefix . 'winter_blog_posts_categories.category_id');
			});
		}

		$categories = $categories->getNested();

		/*
			         * Add a "url" helper attribute for linking to each category
		*/
		return $this->linkCategories($categories);
	}

	protected function linkCategories($categories) {
		return $categories->each(function ($category) {
			$category->setUrl($this->categoryPage, $this->controller);

			if ($category->children) {
				$this->linkCategories($category->children);
			}
		});
	}
}
