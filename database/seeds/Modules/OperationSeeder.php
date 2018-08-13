<?php

use Illuminate\Database\Seeder;
use App\Models\Modules\Operation;

class OperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'create'],
            ['name' => 'view'],
            ['name' => 'update'],
            ['name' => 'delete'],
            ['name' => 'list'],
            ['name' => 'search'],
            ['name' => 'download']
        ];

        foreach ($data as $val)
        {
            Operation::updateOrCreate($val);
        }
    }
}
