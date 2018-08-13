<?php

use App\Models\Languages\Language;
use App\Models\Locations\Country;
use Illuminate\Database\Seeder;

class CountryLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languageObj = new Language();

        $data = [
            'MY' => $languageObj->whereIn('key', ['EN', 'CH', 'MY'])->pluck('id')->toArray(),
            'TH' => $languageObj->whereIn('key', ['TH', 'EN'])->pluck('id')->toArray(),
            'BN' => $languageObj->whereIn('key', ['EN', 'CH', 'MY'])->pluck('id')->toArray(),
            'SG' => $languageObj->whereIn('key', ['EN', 'CH', 'MY'])->pluck('id')->toArray(),
            'TW' => $languageObj->whereIn('key', ['TW', 'EN'])->pluck('id')->toArray(),
            'HK' => $languageObj->whereIn('key', ['HK', 'CH', 'EN'])->pluck('id')->toArray(),
            'KH' => $languageObj->whereIn('key', ['KH', 'CH', 'EN'])->pluck('id')->toArray(),
            'PH' => $languageObj->whereIn('key', ['EN'])->pluck('id')->toArray(),
            'ID' => $languageObj->whereIn('key', ['ID', 'EN'])->pluck('id')->toArray()
        ];

        foreach ($data as $key => $item)
        {
            $country = Country::where('code_iso_2', '=', $key)->first();

            collect($item)->each(function($language, $key) use ($country){

                $country->countryLanguages()->detach([$language]);

                $country->countryLanguages()->attach(
                    $language,
                    [
                        'order' => $key + 1
                    ]);

            });
        }
    }
}
