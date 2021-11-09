<?php namespace Asped\BlogProtect\Components;

use Asped\BlogProtect\Models\Settings;
use Winter\Blog\Components\Post;
use Winter\Blog\Models\Post as BlogPost;

class ProtectedPost extends Post {

	public function componentDetails() {
		return [
			'name' => 'asped.blogprotect::lang.post.name',
			'description' => 'asped.blogprotect::lang.post.description',
		];
	}

	protected function loadPost() {

		$slug = $this->property('slug');

		$akeys = array_keys(app('PassageService')::passageKeys());
		$permarray = array_merge($akeys, [Settings::get('public_perm')]);

		$post = new BlogPost;

		$post = $post->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')
		? $post->transWhere('slug', $slug)
		: $post->where('slug', $slug);

		$post = $post->whereHas('categories',
			function ($q) use ($permarray) {
				$q->whereIn('permission_id', $permarray);
			})
			->where('slug', '=', $slug)->first();
/*
 * Add a "url" helper attribute for linking to each category
 */
		if ($post && $post->categories->count()) {
			$post->categories->each(function ($category) {
				$category->setUrl($this->categoryPage, $this->controller);
			});
		}

		return $post;
	}
}
