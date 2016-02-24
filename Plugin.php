<?php namespace KurtJensen\BlogProtect;

use Event;
use KurtJensen\BlogProtect\Models\Settings;
use KurtJensen\Passage\Models\Key;
use RainLab\Blog\Controllers\Categories as CategoryController;
use RainLab\Blog\Models\Category as CategoryModel;
use System\Classes\PluginBase;

/**
 * BlogProtect Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = ['RainLab.User', 'RainLab.Blog', 'KurtJensen.Passage'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'BlogProtect',
            'description' => 'Restrict RainLab Blog Post viewers by category permission',
            'author' => 'KurtJensen',
            'icon' => 'icon-lock',
        ];
    }

    public function messageURL()
    {
        return 'http://firemankurt.com/notices/';
    }

    public function registerPermissions()
    {
        return [
            'kurtjensen.blogprotect.settings' => ['label' => 'Blog Protect Settings', 'tab' => 'Blog'],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Blog Protect',
                'icon' => 'icon-pencil',
                'description' => 'Configure blog category protection.',
                'class' => 'KurtJensen\BlogProtect\Models\Settings',
                'order' => 199,
                'permissions' => ['kurtjensen.blogprotect.settings'],
            ],
        ];

    }

    public function registerComponents()
    {
        return [
            'KurtJensen\BlogProtect\Components\ProtectedPost' => 'PblogPost',
            'KurtJensen\BlogProtect\Components\ProtectedPosts' => 'PblogPosts',
            'KurtJensen\BlogProtect\Components\ProtectedCategories' => 'PblogCategories',
        ];
    }

    public function boot()
    {
        CategoryModel::extend(function ($model) {
            $model->belongsTo['permission'] = ['KurtJensen\Passage\Models\Key',
                'table' => 'kurtjensen_passage_keys',
                'key' => 'permission_id',
            ];

        });

        Event::listen('backend.list.extendColumns', function ($widget) {
            if (!$widget->getController() instanceof \RainLab\Blog\Controllers\Categories) {
                return;
            }

            if (!$widget->model instanceof \RainLab\Blog\Models\Category) {
                return;
            }

            $widget->addColumns([
                'permission_id' => [
                    'label' => 'Permission',
                    'relation' => 'permission',
                    'select' => 'concat(permission_id,\' \',kurtjensen_passage_keys.name)',
                    'type' => 'relation',
                    'searchable' => true,
                ],
                'id' => [
                    'label' => 'ID',
                    'searchable' => true,
                    'type' => 'Number',
                ],

            ]);
        });

        CategoryController::extendFormFields(function ($form, $model, $context) {

            if (!$model instanceof CategoryModel) {
                return;
            }

//            if (!$model->exists)
            //                return;

            $form->addFields([
                'permission_id' => [
                    'label' => 'Permision for this Category',
                    'comment' => 'Set the permision for this category of posts.',
                    'type' => 'dropdown',
                    'options' => $this->getPermissonIdOptions(),
                    'default' => Settings::get('default_perm', 'blog_deny_all'),
                ],
            ]);
        });
    }

    public function getPermissonIdOptions()
    {
        return Key::lists('name', 'id');
    }

}
