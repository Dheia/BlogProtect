<?php namespace KurtJensen\BlogProtect\Updates;

use KurtJensen\Passage\Models\Key;
use October\Rain\Database\Updates\Seeder;

class SeedAllTables extends Seeder
{

    public function run()
    {
        if (!Key::where('name', '=', 'blog_public')->first()) {
            Key::create([
                'name' => 'blog_public',
                'description' => 'Public Blog Posts ( no user account required to view )',
            ]);
        }

        if (!Key::where('name', '=', 'blog_deny_all')->first()) {
            Key::create([
                'name' => 'blog_deny_all',
                'description' => 'Denied Blog Posts ( no one can see these posts )',
            ]);
        }

    }
}
