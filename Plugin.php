<?php namespace Asped\BlogProtect;

use Event;
use Asped\BlogProtect\Models\Settings;
use JosephCrowell\Passage\Models\Key;
use Lang;
use Winter\Blog\Controllers\Categories as CategoryController;
use Winter\Blog\Models\Category as CategoryModel;
use System\Classes\PluginBase;

/**
 * BlogProtect Plugin Information File
 */
class Plugin extends PluginBase {
	/**
	 * @var array Plugin dependencies
	 */
	public $require = ['Winter.User', 'Winter.Blog', 'JosephCrowell.Passage'];

	/**
	 * Returns information about this plugin.
	 *
	 * @return array
	 */
	public function pluginDetails() {
		return [
			'name' => 'asped.blogprotect::lang.plugin.name',
			'description' => 'asped.blogprotect::lang.plugin.description',
			'author' => 'Asped',
			'icon' => 'icon-lock',
		];
	}

	public function messageURL() {
		return 'http://firemankurt.com/notices/';
	}

	public function registerPermissions() {
		return [
			'asped.blogprotect.settings' => [
				'label' => 'asped.blogprotect::lang.plugin.permission_label',
				'tab' => 'winter.blog::lang.blog.tab',
			],
		];
	}

	public function registerSettings() {
		return [
			'settings' => [
				'label' => 'asped.blogprotect::lang.settings.label',
				'icon' => 'icon-pencil',
				'description' => 'asped.blogprotect::lang.settings.description',
				'class' => 'Asped\BlogProtect\Models\Settings',
				'order' => 199,
				'permissions' => ['asped.blogprotect.settings'],
			],
		];

	}

	public function registerComponents() {
		return [
			'Asped\BlogProtect\Components\ProtectedPost' => 'PblogPost',
			'Asped\BlogProtect\Components\ProtectedPosts' => 'PblogPosts',
			'Asped\BlogProtect\Components\ProtectedCategories' => 'PblogCategories',
			'Asped\BlogProtect\Components\ProtectedRssFeed' => 'PblogRssFeed',
		];
	}

	public function boot() {
		CategoryModel::extend(function ($model) {
			$model->belongsTo['permission'] = ['JosephCrowell\Passage\Models\Key',
				'table' => 'asped_passage_keys',
				'key' => 'permission_id',
			];

		});

		Event::listen('backend.list.extendColumns', function ($widget) {
			if (!$widget->getController() instanceof \Winter\Blog\Controllers\Categories) {
				return;
			}

			if (!$widget->model instanceof \Winter\Blog\Models\Category) {
				return;
			}

			$widget->addColumns([
				'permission_id' => [
					'label' => Lang::get('asped.blogprotect::lang.added_columns.permission_id_label'),
					'relation' => 'permission',
					'select' => 'concat(permission_id,\' \',josephcrowel_passage_keys.name)',
					'searchable' => true,
				],
				'id' => [
					'label' => Lang::get('asped.blogprotect::lang.added_columns.category_id_label'),
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
					'label' => Lang::get('asped.blogprotect::lang.added_fields.permission_id_label'),
					'comment' => Lang::get('asped.blogprotect::lang.added_fields.permission_id_comment'),
					'type' => 'dropdown',
					'options' => $this->getPermissonIdOptions(),
					'default' => Settings::get('default_perm', 'blog_deny_all'),
				],
			]);
		});
	}

	public function getPermissonIdOptions() {
		return Key::lists('name', 'id');
	}

}
