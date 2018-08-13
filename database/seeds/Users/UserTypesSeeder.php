<?php

use Illuminate\Database\Seeder;
use App\Models\Users\UserType;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'root'],
            ['name' => 'BackOffice'],
            ['name' => 'Member'],
            ['name' => 'Stockist'],
            ['name' => 'Guest' ],
            ['name' => 'Stockist_staff'],
        ];

        foreach ($data as $val)
        {
            UserType::updateOrCreate($val);
        }
    }
}
