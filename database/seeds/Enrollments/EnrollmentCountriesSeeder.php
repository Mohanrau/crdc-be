<?php

use Illuminate\Database\Seeder;

class EnrollmentCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = \App\Models\Locations\Country::active()->get();

        $enrollmentsTypes = \App\Models\Enrollments\EnrollmentTypes::active()->pluck('id')->toArray();

        foreach ($countries as $country)
        {
            if ($country->code == 'TWN') {
                $enrollmentTypesTw = $enrollmentsTypes;

                unset($enrollmentTypesTw[1]);

                $country->enrollmentTypes()->sync($enrollmentTypesTw);
            }else{
                $country->enrollmentTypes()->sync($enrollmentsTypes);
            }
        }
    }
}
