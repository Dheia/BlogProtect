<?php namespace KurtJensen\BlogProtect\Components;

use Cms\Classes\ComponentBase;
use Auth;
use DB;
use RainLab\User\Components\Account;
use RainLab\User\Models\User as User;
use ShahiemSeymor\Roles\Models\UserPermission as Permission;
use ShahiemSeymor\Roles\Models\UserGroup as UserGroup;
use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category as Category;
use RainLab\Blog\Components\Post;
use KurtJensen\BlogProtect\Models\Settings;


class ProtectedPost extends Post
{
    use \KurtJensen\BlogProtect\Traits\LoadPermissions;

    public function componentDetails()
    {
        return [
            'name'        => 'Protected Post',
            'description' => 'Displays a protected blog post on the page.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'rainlab.blog::lang.settings.post_slug',
                'description' => 'rainlab.blog::lang.settings.post_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'categoryPage' => [
                'title'       => 'rainlab.blog::lang.settings.post_category',
                'description' => 'rainlab.blog::lang.settings.post_category_description',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
            ],
        ];
    }

    public function onRun()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->post = $this->page['post'] = $this->loadPost();
    }

    protected function loadPost()
    {
        $slug = $this->property('slug');
        
        $permissions = $this->loadPermissions();

        $post = BlogPost::whereHas('categories', 
                    function($q) use ($permissions) { 
                        $q->whereIn('permission_id', $permissions); 
                    })
                    ->where('slug', '=', $slug)->first(); 

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if ($post && $post->categories->count()) {
            $post->categories->each(function($category){
                $category->setUrl($this->categoryPage, $this->controller);
            });
        }

        return $post;
    }
}
