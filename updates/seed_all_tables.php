<?php namespace KurtJensen\BlogProtect\Updates;

use ShahiemSeymor\Roles\Models\UserPermission;
use October\Rain\Database\Updates\Seeder;

class SeedAllTables extends Seeder
{

    public function run()
    {
       if (!UserPermission::where('name','=','blog_public')->first())
              UserPermission::create([
                     'name' => 'blog_public',
              ]);
       if (!UserPermission::where('name','=','blog_deny_all')->first())
              UserPermission::create([
                     'name' => 'blog_deny_all',
              ]);
    }
}
