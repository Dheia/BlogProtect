<?php namespace KurtJensen\BlogProtect\Components;

use Db;
use KurtJensen\BlogProtect\Models\Settings;
use RainLab\Blog\Components\Categories;
use RainLab\Blog\Models\Category as BlogCategory;

class ProtectedCategories extends Categories
{

    public function componentDetails()
    {
        return [
            'name' => 'Protected Category',
            'description' => 'Displays a list of protected blog categories on the page.',
        ];
    }

    protected function loadCategories()
    {

        $akeys = array_keys(\KurtJensen\Passage\Plugin::passageKeys());
        $permarray = array_merge($akeys, [Settings::get('public_perm')]);

        $categories = BlogCategory::whereIn('permission_id', $permarray)->orderBy('name');

        if (!$this->property('displayEmpty')) {
            $categories->whereExists(function ($query) {
                $query->select(Db::raw(1))
                    ->from('rainlab_blog_posts_categories')
                    ->join('rainlab_blog_posts', 'rainlab_blog_posts.id', '=', 'rainlab_blog_posts_categories.post_id')
                    ->whereNotNull('rainlab_blog_posts.published')
                    ->where('rainlab_blog_posts.published', '=', 1)
                    ->whereRaw('rainlab_blog_categories.id = rainlab_blog_posts_categories.category_id');
            });
        }

        $categories = $categories->get();

        /*
         * Add a "url" helper attribute for linking to each category
         */
        $categories->each(function ($category) {
            $category->setUrl($this->categoryPage, $this->controller);
        });

        return $categories;
    }
}
