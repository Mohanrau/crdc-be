<?php
namespace Tests\Functional\Shop;

use Tests\Functional\TestCase;
use App\Models\Shop\Favorites;
use Tests\Shared\Traits\MockBlamable;
use Tests\Shared\Traits\DisableValidator;

class ShopFavoritesTest extends TestCase
{

    use MockBlamable;
    use DisableValidator;

    /**
     * test create shop favorite for products
     */
    function testCreateShopFavoriteForProducts() {
        $this->withoutMiddleware();

        // Pass Though Validator
        $this->disableValidator();

        // mock favorites model
        $input = [ "product_id" => 1, "user_id" => 1 ];
        $mock = $this->mockBlamableSave(Favorites::class, $input, ["getFavoriteProductAndKitting"]);
        $mock->shouldReceive('getFavoriteProductAndKitting')->andReturn(null);
        $this->app->instance(Favorites::class, $mock);

        $content = $this->json('POST', 'api/v1/shop/favorite', $input)->assertStatus(200)->getContent();
        $content = json_decode($content, true);

        $this->assertEquals($input, $content);
    }

    /**
     * test create shop favorite for products
     */
    function testCreateShopFavoriteForKitting() {
        $this->withoutMiddleware();

        // Pass Though Validator
        $this->disableValidator();

        // mock favorites model
        $input = [ "kitting_id" => 1, "user_id" => 1 ];
        $mock = $this->mockBlamableSave(Favorites::class, $input, ["getFavoriteProductAndKitting"]);
        $mock->shouldReceive('getFavoriteProductAndKitting')->andReturn(null);
        $this->app->instance(Favorites::class, $mock);

        $content = $this->json('POST', 'api/v1/shop/favorite', $input)->assertStatus(200)->getContent();
        $content = json_decode($content, true);

        $this->assertEquals($input, $content);
    }

    /**
     * test create shop favorite for products
     */
    function testReadFavorites() {
        $this->withoutMiddleware();

        // Pass Though Validator
        $this->disableValidator();

        $this->mockUser();

        // mock favorites model
        $input = [ "country_id" => 1, "location_id" => 1 ];

        $products = [
            ["id" => 1],
            ["id" => 2],
            ["id" => 3],
        ];
        // methods that should be touched
        $favoritesMock = \Mockery::mock(Favorites::class . '[getFavoritesForUser]');
        $favoritesMock->shouldReceive('getFavoritesForUser')->once()->andReturn($products);

        $this->app->instance(Favorites::class, $favoritesMock);

        $content = $this->json('GET', 'api/v1/shop/favorite', $input)->assertStatus(200)->getContent();
        $content = json_decode($content, true);

        $this->assertEquals($products, $content);
    }
}