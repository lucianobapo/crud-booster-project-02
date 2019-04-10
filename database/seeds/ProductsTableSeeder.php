<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
//        factory(App\User::class, 50)->create()->each(function ($user) {
//            $user->posts()->save(factory(App\Post::class)->make());
//        });


        for ($i=1;$i<5;$i++)
            DB::table('products')->insert([
                'id' => $faker->uuid,
                'owner_id' => '1',
                'name' => $faker->text(),
                'description' => $faker->sentence,
            ]);
    }
}
