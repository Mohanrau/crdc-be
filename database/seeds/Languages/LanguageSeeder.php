<?php

use Illuminate\Database\Seeder;
use App\Models\Languages\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languagesData = [
            [
                "key" => "EN",
                "name" => "English",
                "active" => 1
            ],
            [
                "key" => "MY",
                "name" => "Malay",
                "active" => 1
            ],
            [
                "key" => "CH",
                "name" => "Simplified Chinese",
                "active" => 1
            ],
            [
                "key" => "HK",
                "name" => "HK Chinese",
                "active" => 1
            ],
            [
                "key" => "TW",
                "name" => "Traditional Chinese",
                "active" => 1
            ],
            [
                "key" => "TH",
                "name" => "Thai",
                "active" => 1
            ],
            [
                "key" => "ID",
                "name" => "Bahasa Indonesia",
                "active" => 1
            ],
            [
                "key" => "KH",
                "name" => "Khmer",
                "active" => 1
            ],
        ];

        foreach ($languagesData as $data)
        {
            Language::updateOrCreate([
                'key' => $data['key']
            ], $data);
        }
    }
}
