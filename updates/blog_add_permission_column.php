<?php namespace Asped\BlogProtect\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Winter\Blog\Models\Category;

class BlogAddPermissionColumn extends Migration
{
    public function up()
    {
        Schema::table('winter_blog_categories', function($table)
        {
            $table->integer('permission_id')->unsigned();
        });

        /*
         * Set permission for existing categories
         */
        $categories = Category::all();
        foreach ($categories as $category) {
            $category->permission_id = 0;
            $category->save();
        }
    }

    public function down()
    {
        Schema::table('winter_blog_categories', function($table)
        {
            $table->dropColumn('permission_id');
        });
    }

}
