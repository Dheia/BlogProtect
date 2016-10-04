<?php namespace KurtJensen\BlogProtect\Components;

use KurtJensen\BlogProtect\Models\Settings;
use RainLab\Blog\Components\Post;
use RainLab\Blog\Models\Post as BlogPost;

class ProtectedPost extends Post
{

    public function componentDetails()
    {
        return [
            'name' => 'Protected Post',
            'description' => 'Displays a protected blog post on the page.',
        ];
    }

    protected function loadPost()
    {

        $slug = $this->property('slug');

        $akeys = array_keys(\KurtJensen\Passage\Plugin::passageKeys());
        $permarray = array_merge($akeys, [Settings::get('public_perm')]);

        $post = BlogPost::whereHas('categories',
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
