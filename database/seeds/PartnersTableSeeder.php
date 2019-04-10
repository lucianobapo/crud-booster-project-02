<?php

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Faker\Generator as Faker;

class PartnersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param Faker $faker
     * @return void
     * @throws Exception
     */
    public function run(Faker $faker)
    {
        for ($i=1;$i<5;$i++)
            DB::table('partners')->insert([
                'id' => $faker->uuid,
                'owner_id' => '1',
                'name' => $faker->name,
    //            'description' => $faker->sentence,
            ]);
    }
}
