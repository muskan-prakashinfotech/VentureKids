<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
        ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
        ['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupee'],
        ['code'=>'SGD','symbol'=>'S$','name'=>'Singapore Dollar'],
    ]);
    }
}
