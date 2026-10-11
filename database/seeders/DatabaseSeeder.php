<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a demo pharmacy. Model events stay on so that
     * sales deduct stock from batches exactly like the real app.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@medistock.test',
            'password' => 'password',
        ]);

        $this->call([
            CatalogSeeder::class,
            SalesSeeder::class,
        ]);
    }
}
