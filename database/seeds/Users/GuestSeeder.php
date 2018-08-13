<?php

use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Helpers\Classes\RandomPassword;

class GuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'guest@elken.com')->first();

        if (is_null($user)){
            $user = User::create([
                'mobile' => '0000000000',
                'name' => 'Guest',
                'email' => 'guest@elken.com',
                'password' => bcrypt(RandomPassword::generate(30)),
                'active' => 1,
                'login_count' => 0
            ]);
        }

        //attach user to guest user type
        $user->userType()->sync([
            'user_type_id' => 5 // set user to be have guest type for rnp check
        ]);

    }
}
