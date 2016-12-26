<?php namespace KurtJensen\BlogProtect\Components;

use Db;
use KurtJensen\BlogProtect\Models\Settings;
use RainLab\Blog\Components\Categories;
use RainLab\Blog\Models\Category as BlogCategory;

class ProtectedCategories extends Categories {

	public function componentDetails() {
		return [
			'name' => 'kurtjensen.blogprotect::lang.categories.name',
			'description' => 'kurtjensen.blogprotect::lang.categories.description',
		];
	}

	protected function loadCategories() {

		$akeys = array_keys(\KurtJensen\Passage\Plugin::passageKeys());
		$permarray = array_merge($akeys, [Settings::get('public_perm')]);

		$categories = BlogCategory::whereIn('permission_id', $permarray)->orderBy('name');

		if (!$this->property('displayEmpty')) {
			$categories->whereExists(function ($query) {
				$prefix = Db::getTablePrefix();
				$query->select(Db::raw(1))
					->from('rainlab_blog_posts_categories')
					->join('rainlab_blog_posts', 'rainlab_blog_posts.id', '=', 'rainlab_blog_posts_categories.post_id')
					->whereNotNull('rainlab_blog_posts.published')
					->where('rainlab_blog_posts.published', '=', 1)
					->whereRaw($prefix . 'rainlab_blog_categories.id = ' . $prefix . 'rainlab_blog_posts_categories.category_id');
			});
		}

		$categories = $categories->getNested();

		/*
			         * Add a "url" helper attribute for linking to each category
		*/
		return $this->linkCategories($categories);
	}
}
