<?php

use Illuminate\Database\Seeder;
use App\Models\Enrollments\EnrollmentTypes;
use function GuzzleHttp\json_encode;

class EnrollmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data =
        [
            [
                "title" => "BA",
                "info" => "
                    <ul>
                        <li>Eligible for Bonus payment</li>
                        <li>Entitled to sponsor and build network business</li>
                        <li>Purchase products at member price</li>
                        <li>iElken Business Manual -A file which explains the iElken Compensation Plan, detailing the business.
                        model and its Five Bonus Programme, glossary of terms and members\ Rules & Regulations.</li>
                        <li>iElken Product Catalogue - A file that contains a list of all the products available under iElken.
                        Each line of the product catalogue contains a description of each product, including its code,
                        name, category, price, picture and other attributes</li>
                        <li>iElken Corporate Brochure</li>
                        <li>iElken Corporate Video</li>
                        <li>iElken Business Suite (IBS for membership activation)</li>
                        <li>Comprehensive Back office management - membership module, sales reporting, genealogy monitoring,
                        e-commerce module, e-wallet management and campaign module</li>
                        <li>Interactive personalised website</li>
                    </ul>
                ",
                "sale_types" => [
                    'Formation',
                    'Registration'
                ],
                "active" => 1
            ],

            [
                "title" => "Premier Member",
                "info" => "
                    <ul>
                        <li>Eligible for Bonus payment</li>
                        <li>Entitled to sponsor and build network business</li>
                        <li>Purchase products at member price</li>
                        <li>iElken Business Manual -A file which explains the iElken Compensation Plan, detailing the business.
                        model and its Five Bonus Programme, glossary of terms and members\ Rules & Regulations.</li>
                        <li>iElken Product Catalogue - A file that contains a list of all the products available under iElken.
                        Each line of the product catalogue contains a description of each product, including its code,
                        name, category, price, picture and other attributes</li>
                        <li>iElken Corporate Brochure</li>
                        <li>iElken Corporate Video</li>
                        <li>iElken Business Suite (IBS for membership activation)</li>
                        <li>Comprehensive Back office management - membership module, sales reporting, genealogy monitoring,
                        e-commerce module, e-wallet management and campaign module</li>
                        <li>Interactive personalised website</li>
                    </ul>
                ",
                "sale_types" => [
                    'Formation',
                    'Registration'
                ],
                "active" => 1
            ],

            [
                "title" => "Member",
                "info" => "
                    <ul>
                        <li>Eligible for Bonus payment</li>
                        <li>Entitled to sponsor and build network business</li>
                        <li>Purchase products at member price</li>
                        <li>iElken Business Manual -A file which explains the iElken Compensation Plan, detailing the business.
                        model and its Five Bonus Programme, glossary of terms and members\ Rules & Regulations.</li>
                        <li>iElken Product Catalogue - A file that contains a list of all the products available under iElken.
                        Each line of the product catalogue contains a description of each product, including its code,
                        name, category, price, picture and other attributes</li>
                        <li>iElken Corporate Brochure</li>
                        <li>iElken Corporate Video</li>
                        <li>iElken Business Suite (IBS for membership activation)</li>
                        <li>Comprehensive Back office management - membership module, sales reporting, genealogy monitoring,
                        e-commerce module, e-wallet management and campaign module</li>
                        <li>Interactive personalised website</li>
                    </ul>
                ",
                "sale_types" => [],
                "active" => 1
            ]
        ];

        foreach ($data as $item){
            EnrollmentTypes::updateOrCreate(
                ['title' => $item['title']],
                [
                    'info' =>  $item['info'],
                    'sale_types' => json_encode($item['sale_types']),
                    'active' => $item['active']
                ]
            );
        }
    }
}