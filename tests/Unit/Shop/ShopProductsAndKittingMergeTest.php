<?php
namespace Tests\Unit\Shop;

use Tests\Unit\TestCase;
use App\Models\{
    Products\Product,
    Shop\ProductAndKitting,
    Kitting\Kitting
};

class ShopProductsAndKittingMergeTest extends TestCase
{
    private $productModelReflection;
    private $kittingModelReflection;

    /*
     * Create reflections on setup to check if methods exists
     */
    protected function setUp()
    {
        parent::setUp();
        // create reflections to test if the required methods exists
        $this->productModelReflection = new \ReflectionClass(Product::class);
        $this->kittingModelReflection = new \ReflectionClass(Kitting::class);
    }

    /**
     * check if products and kitting model has images methods, if this methods is changed then probably
     * the kitting and product joins should change
     */
    function testMergableImages() {
        // test if images join methods exists
        $this->assertTrue(
            $this->productModelReflection->hasMethod('productImages'),
            sprintf('%s Method should should exist in %s', 'productImages',Product::class));

        $this->assertTrue(
            $this->kittingModelReflection->hasMethod('kittingImages'),
            sprintf('%s Method should should exist in %s', 'kittingImages',Kitting::class));
    }

    /**
     * check if products and kitting model has descriptions methods, if this methods is changed then probably
     * the kitting and product joins should change
     */
    function testMergableDescriptions() {
        // test if product descriptions exists
        $this->assertTrue(
            $this->productModelReflection->hasMethod('productDescriptions'),
            sprintf('%s Method should should exist in %s', 'productDescriptions',Product::class));

        $this->assertTrue(
            $this->kittingModelReflection->hasMethod('kittingDescriptions'),
            sprintf('%s Method should should exist in %s', 'kittingDescriptions',Kitting::class));
    }

    /**
     * check if products and kitting model has prices methods, if this methods is changed then probably
     * the kitting and product joins should change
     */
    function testMergablePrice() {
        // test if prices exists
        $this->assertTrue(
            $this->productModelReflection->hasMethod('productPrices'),
            sprintf('%s Method should should exist in %s', 'productPrice',Product::class));

        $this->assertTrue(
            $this->kittingModelReflection->hasMethod('kittingPrice'),
            sprintf('%s Method should should exist in %s', 'kittingPrice',Kitting::class));
    }

    /**
     * check if products and kitting model has locations methods, if this methods is changed then probably
     * the kitting and product joins should change
     */
    function testMergableLocations() {
        // test if locations exists
        $this->assertTrue(
            $this->productModelReflection->hasMethod('productLocations'),
            sprintf('%s Method should should exist in %s', 'productLocations',Product::class));

        $this->assertTrue(
            $this->kittingModelReflection->hasMethod('kittingLocations'),
            sprintf('%s Method should should exist in %s', 'kittingLocations',Kitting::class));
    }

    /**
     * check to see if exception is triggered when activated method is called too late
     */
    function testActiveTooLate() {
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get]');
        $productAndKittingMock->setCountry(1);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(trans("message.query-building.active-status-cannot-change"));
        $productAndKittingMock->setActiveOnly(true);
    }

    /**
     * Cannot run without setting the county
     */
    function testCountryNotSet()
    {
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class)->makePartial();
        $this->expectException(\App\Exceptions\Locations\CountryNotSetException::class);
        $this->expectExceptionMessage(trans("message.country.not-set"));
        $productAndKittingMock->shouldNotReceive('get');
        $productAndKittingMock->shouldReceive('unify');
        $productAndKittingMock->filterSalesTypes([]);

        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class)->makePartial();
        $this->expectException(\App\Exceptions\Locations\CountryNotSetException::class);
        $this->expectExceptionMessage(trans("message.country.not-set"));
        $productAndKittingMock->shouldNotReceive('get');
        $productAndKittingMock->shouldReceive('withPrices');
        $productAndKittingMock->withPrices([]);
    }

    /**
     * Country cannot be set twice
     */
    function testCountryAlreadySet() {
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get]');
        $productAndKittingMock->setCountry(1);
        $this->expectException(\App\Exceptions\Locations\CountryAlreadySetException::class);
        $this->expectExceptionMessage(trans("message.country.already-set"));
        $productAndKittingMock->setCountry(1);
    }

    /**
     * Check for all request functionality
     */
    function testAllParametersByLatestQuery() {
        $this->withoutMiddleware();
        // mock validator
        \Validator::shouldReceive('validate')->andReturn(\Validator::shouldReceive("passes"));
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')
            ->once()->andReturn([]);

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [
            "country_id" => 1,
            "location_id" => 1,
            "categories" => [ 1, 2, 3, 4, 5 ],
            "sales_types" => [ 1, 2, 3, 4, 5, 6, 7 ],
            "price_min" => 1,
            "price_max" => 10000,
            "cv_min" => 1,
            "cv_max" => 1200,
            "sortby" => 2
        ])->assertStatus(200);

        $resultsUnion = $productAndKittingMock->getResultUnion();

        // Check product select fields
        foreach (
            [
                ["Product", "products.id as product_id", "id", false, false],
                ["Product", \Illuminate\Database\Query\Expression::class, "id normalizer", false, true],
                ["Product", "name", "name", false, false],
                ["Product", "sku", "sku", false, false],
                ["Product", "products.created_at as created_at", "sku", false, false]
            ] as $index => $assertion) {
            list($name, $select, $field, $isUnion, $isType) = $assertion;
            $this->assertQuerySelect($resultsUnion, $name, $select, $field, $index, $isUnion, $isType);
        }

        // Check kitting select fields
        foreach (
            [
                ["Kitting", \Illuminate\Database\Query\Expression::class, "id normalizer", true, true],
                ["Kitting", "kitting.id as kitting_id", "id", true, false],
                ["Kitting", "kitting.name as name", "name", true, false],
                ["Kitting", "code as sku", "sku", true, false],
                ["Kitting", "kitting.created_at as created_at", "sku", true, false]
            ] as $index => $assertion) {
            list($name, $select, $field, $isUnion, $isType) = $assertion;
            $this->assertQuerySelect($resultsUnion, $name, $select, $field, $index, $isUnion, $isType);
        }

        // Product Joins
        foreach (
            [
                "product_active_countries.product_id", // Product Countries join
                "product_general_settings.product_id", // Product General Settings join
                "product_images.product_id", // Product Images join
                "product_prices.product_id", // Product Prices join
                "product_promo_locations.promo_id", // Product Promo join
                "product_descriptions.product_id" // Product Descriptions join
            ] as $index => $field
        ) {
            $this->assertQueryJoin($resultsUnion, $field, $index, "second");
        }

        // Kitting Joins
        foreach (
            [
                "kitting_general_settings.kitting_id", // Kitting General Settingsjoin
                "kitting_images.kitting_id", // Kitting Images join
                "kitting_prices.kitting_id", // Kitting Prices join
                "kitting_descriptions.kitting_id", // Kitting Descriptions join
            ] as $index => $value
        ) {
            $this->assertQueryJoin($resultsUnion, $value, 0, "second",  0, $index, true);
        }
    }

    /**
     * Checks the assertation of the join query for kittings and product union
     *
     * @param $builder the builder / model
     * @param $value the value to check
     * @param $index index of the union or join
     * @param $whereName name or key of the where clause normally "second" for joins (this is the JOIN ON {where} = {where})
     * @param int $whereIndex the index of the where element to check
     * @param int $joinIndex index at wich the join exists only applicable in kitting joins
     * @param bool $kitting if to check the kitting
     */
    private function assertQueryJoin($builder, $value, $index, $whereName, $whereIndex = 0, $joinIndex = 0, $kitting = false) {
        if ($kitting) {
            $check = $builder->unions[$index]['query']->joins[$joinIndex]->wheres[$whereIndex][$whereName];
        } else {
            $check = $builder->joins[$index]->wheres[$whereIndex][$whereName];
        }
        $this->assertEquals($value,  $check, "Join for {$value} not found in " . ($kitting ? "Kitting" : "Products"));
    }

    /**
     * Checks a select of a query
     *
     * @param $builder the builder / model
     * @param $name for error message purposes
     * @param $select the select to look for
     * @param $field for erro message purpose
     * @param $index index at with the colum exists
     * @param bool $isUnion if to check in the union
     * @param bool $isType should it be a type check
     */
    private function assertQuerySelect($builder, $name, $select, $field, $index, $isUnion = false, $isType = false) {
        $columns = ($isUnion) ? $builder->unions[0]['query']->columns : $builder->columns;
        $assert = ($isType) ? "InstanceOf" : "Equals";
        $this->{"assert" . $assert}($select, $columns[$index],
            sprintf("Index %s in %s should be %s", $index, $name, $field)
        );
    }
}