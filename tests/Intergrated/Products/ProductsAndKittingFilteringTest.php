<?php
namespace Tests\Functional\Products;

use Tests\Intergrated\TestCase;

class ProductsAndKittingFilteringTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // TODO: create seeds for test and put in ./database/seeding/file.txt
        // TODO: improve the test

        //        Artisan::call('migrate:refresh');
        //        Artisan::call('db:seed', [
        //            "--class" => "ProductCategorySeeder"
        //        ]);
        //        Artisan::call('db:seed', [
        //            "--class" => "ProductPriceSeeder"
        //        ]);
        //        Artisan::call('db:seed', [
        //            "--class" => "ProductSeeder"
        //        ]);
    }

    /*
     * Test all filters by latest first request is intergrated
     */
    function testAllFiltersByLatestFirst() {

        $this->withoutMiddleware();

        $response = $this->json('POST', 'api/v1/product-and-kitting', [
            "country_id" => 1,
            "location_id" => 1,
            "categories" => [ 1, 2, 3, 4, 5 ],
            "sales_types" => [ 1, 2, 3, 4, 5, 6, 7 ],
            "price_min" => 1,
            "price_max" => 10000,
            "cv_min" => 1,
            "cv_max" => 1200,
            "sortby" => 2
        ] )
            ->assertStatus(200);
        $content = $response->getContent();
        $objects = json_decode($content, true);
        $this->assertEquals(0, key($objects), "the first key has to be 0");
        $this->assertArrayHasKey("images", current($objects), "array should have image");
        $this->assertArrayHasKey("descriptions", current($objects), "array should have descriptions");
        $this->assertArrayHasKey("prices", current($objects), "array should have prices");
        foreach ($objects as $object){
            $this->assertEquals(0, key($object['prices']), "array should atleast have a prices");
        }
    }
}