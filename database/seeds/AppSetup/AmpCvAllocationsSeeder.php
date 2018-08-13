<?php

use Illuminate\Database\Seeder;

class AmpCvAllocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/' . "amp_cv_allocations.txt"));

        foreach ($data as $item)
        {
            $user = App\Models\Users\User::where('old_member_id', $item->old_member_id)->first();

            $cw = \App\Models\General\CWSchedule::where('cw_name', $item->cw_name)->first();

            if(isset($user) && isset($cw))
            {
                $typeId = (new App\Models\Masters\MasterData)->getIdByTitle('Amp', 'amp_cv_allocation_types');

                \App\Models\Bonus\AmpCvAllocation::updateOrCreate([
                    'type_id' => $typeId,
                    'sale_id' => null,
                    'user_id' => $user->id,
                    'cw_id' => $cw->id,
                    'cv' => $item->cv,
                    'active' => $item->active
                ]);
            }
        }
    }
}
