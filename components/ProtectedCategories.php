<?php namespace KurtJensen\BlogProtect\Components;

use DB;
use RainLab\Blog\Components\Categories;
use RainLab\Blog\Models\Category as BlogCategory;

class ProtectedCategories extends Categories
{
    use \KurtJensen\BlogProtect\Traits\LoadPermissions;

    public function componentDetails()
    {
        return [
            'name'        => 'Protected Category',
            'description' => 'Displays a list of protected blog categories on the page.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'rainlab.blog::lang.settings.category_slug',
                'description' => 'rainlab.blog::lang.settings.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'displayEmpty' => [
                'title'       => 'rainlab.blog::lang.settings.category_display_empty',
                'description' => 'rainlab.blog::lang.settings.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0
            ],
            'categoryPage' => [
                'title'       => 'rainlab.blog::lang.settings.category_page',
                'description' => 'rainlab.blog::lang.settings.category_page_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'Links',
            ],
        ];
    }

    public function onRun()
    {
        $this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    protected function loadCategories()
    {
        $permissions = $this->loadPermissions();
        $categories = BlogCategory::whereIn('permission_id', $permissions)->orderBy('name');
        if (!$this->property('displayEmpty')) {
            $categories->whereExists(function($query) {
                $query->select(DB::raw(1))
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
        $categories->each(function($category){
            $category->setUrl($this->categoryPage, $this->controller);
        });

        return $categories;
    }
}
