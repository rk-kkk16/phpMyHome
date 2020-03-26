<?php

use Illuminate\Database\Seeder;

class ScCategoryInsertPrimary extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sc_categories')->insert([
            'category_name' => '(カテゴリなし)',
            'user_id' => 0,
            'is_primary' => true,
            'depth' => 1,
        ]);
    }
}
