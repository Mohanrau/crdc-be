<?php
namespace Tests\Functional\Shop;

use Tests\Functional\TestCase;
use App\Models\{
    Shop\ProductAndKitting
};
use Tests\Shared\Traits\DisableValidator;

class ShopProductsAndKittingMergeTest extends TestCase
{

    use DisableValidator;
    /**
     * Test if request creates prices
     */
    function testWithPriceQuery() {
        // without tokens
        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();

        // methods that should be touched
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, withPrices, withImages, withDescriptions, filterSalesTypes]');
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('withPrices')->once()->passthru();
        $productAndKittingMock->shouldReceive('withImages')->once()->passthru();
        $productAndKittingMock->shouldReceive('withDescriptions')->once()->passthru();
        $productAndKittingMock->shouldReceive('filterSalesTypes')->once()->passthru();

        // inject mock
        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price filter category query is functional
     */
    function testPriceFilterCatagoryQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, filterCatagories, filterSalesTypes]');
        // define behavior
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('filterCatagories')->once()->passthru();
        $productAndKittingMock->shouldReceive('filterSalesTypes')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "categories" => [ 1, 2 ] ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price filter sales type query is functional
     */
    function testPriceFilterSalesTypeQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, filterSalesTypes]');
        // behavior
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('filterSalesTypes')->with([ 1, 2 ])->once()->passthru();
        // iject
        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sales_types" => [ 1, 2 ] ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price order by best selling query is functional
     */
    function testPriceOrderByBestSellingQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByBestSelling]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByBestSelling')->once()->passthru();

        // Inject
        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 1 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /**
     * test if price order by latest query is functonal
     */
    function testPriceOrderByLatestQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByCreatedAt]');
        // behaviors
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByCreatedAt')->with('ASC')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 2 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price order by older query is functional
     */
    function testPriceOrderByOlderQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByCreatedAt]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByCreatedAt')->with('DESC')->once()->passthru();
        // Inject
        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 3 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /**
     * Test if price order by price query is functional
     */
    function testPriceOrderByPriceQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByPrice]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByPrice')->with('ASC')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 4 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /**
     * Test if price order by price low from high query is functional
     */
    function testPriceOrderByPriceLowQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByPrice]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByPrice')->with('DESC')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 5 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price order by cv query is functional
     */
    function testPriceOrderByCvQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByCv]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByCv')->with('ASC')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 6 ] )->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price order by CV decending query is functional
     */
    function testPriceOrderByCvDescQuery() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, sortByCv]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('sortByCv')->with('DESC')->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "sortby" => 7 ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price grater than query is functional
     */
    function testPriceGraterThan() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        $price = 100;
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, priceGraterThan]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('priceGraterThan')->with($price)->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "price_min" => $price ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if price less than query is functional
     */
    function testPriceLessThan() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        $price = 100;
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, priceLessThan]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('priceLessThan')->with($price)->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "price_max" => $price ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /**
     * test if cv grater than is functional
     */
    function testCvGraterThan() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        $cv = 100;
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, cvGraterThan]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('cvGraterThan')->with($cv)->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "cv_min" => $cv ] )
            ->assertStatus(200);

        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }

    /*
     * Test if CV less than is functional
     */
    function testCvLessThan() {

        $this->withoutMiddleware();
        // Mock validator to return true
        $this->disableValidator();
        $cv = 100;
        // mock only get method
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get, cvLessThan]');
        // all ways return array from get because we are testing the query
        $productAndKittingMock->shouldReceive('get')->once()->andReturn([]);
        $productAndKittingMock->shouldReceive('cvLessThan')->with($cv)->once()->passthru();

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $this->json('POST', 'api/v1/shop/product-and-kitting', [ "country_id" => 1, "location_id" => 1, "cv_max" => $cv ] )
            ->assertStatus(200);
        // sql should be generatable
        $sql = $productAndKittingMock->unify()->toSql();
        $this->assertNotEmpty($sql);
    }
}
