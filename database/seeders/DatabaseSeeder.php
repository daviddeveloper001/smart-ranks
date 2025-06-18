<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);


        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');


        User::factory(95)->create()->each(function ($user) {
            $user->assignRole('user');
        });


        $categories = Category::factory(10)->create();


        Product::factory(80)->create([
            'category_id' => $categories->random()->id,
        ]);

    }
}
