<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        \App\User::truncate();
        \App\Category::truncate();
        \App\Product::truncate();
        \App\Transaction::truncate();
        \Illuminate\Support\Facades\DB::table('category_product')->truncate();
        factory(\App\User::class,200)->create();
        factory(\App\Product::class,100)->create();
        factory(\App\Transaction::class,100)->create();
        factory(\App\Category::class,30)->create();
    }
}
