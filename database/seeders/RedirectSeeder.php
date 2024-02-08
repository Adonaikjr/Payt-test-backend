<?php

namespace Database\Seeders;

use App\Models\table_redirects;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RedirectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        table_redirects::factory()->count(10)->create();
    }
}
