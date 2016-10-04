<?php namespace KurtJensen\BlogProtect\Components;

use KurtJensen\BlogProtect\Models\Settings;
use RainLab\Blog\Components\Posts;
use RainLab\Blog\Models\Category;
use RainLab\Blog\Models\Post;

class ProtectedPosts extends Posts
{
    public $permarray = [];

    public function componentDetails()
    {
        return [
            'name' => 'Protected Post List',
            'description' => 'Displays a list of latest protected blog posts on the page.',
        ];
    }

    protected function listPosts()
    {
        $category = $this->category ? $this->category->id : null;

        /*
         * List all the posts, eager load their categories
         */

        $posts = Post::whereHas('categories',
            function ($q) {
                $q->whereIn('permission_id', $this->permarray)->
                    where('permission_id', '!=', Settings::get('deny_perm'));
            })->
            with('categories')->
            listFrontEnd([
            'page' => $this->property('pageNumber'),
            'sort' => $this->property('sortOrder'),
            'perPage' => $this->property('postsPerPage'),
            'category' => $category,
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

    protected function loadCategory()
    {

        $akeys = array_keys(\KurtJensen\Passage\Plugin::passageKeys());
        $this->permarray = array_merge($akeys, [Settings::get('public_perm')]);

        //$category = parent::loadCategory();

        if (!$categoryId = $this->property('categoryFilter')) {
            return null;
        }

        if (!$category = Category::whereSlug($categoryId)->
            whereIn('permission_id', $this->permarray)->
            first()) {
            return null;
        }

        return $category;
    }
}
