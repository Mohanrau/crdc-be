<?php

use App\Models\EWallets\EWallet;
use App\Models\Members\Member;
use Illuminate\Database\Seeder;

class CreateEWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = Member::get();

        foreach ($members as $member)
        {
            EWallet::updateOrCreate([
                'user_id' => $member->user_id
            ],[
                'default_currency_id' => $member->country->default_currency_id
            ]);
        }
    }
}
