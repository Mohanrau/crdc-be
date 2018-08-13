<?php
namespace Tests\Functional\Shop;

use Tests\Functional\TestCase;
use App\Models\{
    Products\ProductAndKitting, Shop\Cart
};
use Tests\Shared\Traits\MockBlamable;
use Tests\Shared\Traits\DisableValidator;

class ShopCartTest extends TestCase
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
        $input = [ "product_id" => 1, "user_id" => 1, "country_id" => 1, "location_id" => 1 ];
        $mock = $this->mockBlamableSave(Cart::class, $input, ["getSingleCartItem"]);
        $mock->shouldReceive('getSingleCartItem')->andReturn(null);
        $this->app->instance(Cart::class, $mock);

        $content = $this->json('POST', 'api/v1/shop/cart', $input)->assertStatus(200)->getContent();
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
        $input = [ "kitting_id" => 1, "user_id" => 1, "country_id" => 1, "location_id" => 1 ];
        $mock = $this->mockBlamableSave(Cart::class, $input, ["getSingleCartItem"]);
        $mock->shouldReceive('getSingleCartItem')->andReturn(null);
        $this->app->instance(Cart::class, $mock);

        $content = $this->json('POST', 'api/v1/shop/cart', $input)->assertStatus(200)->getContent();
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
            ["id" => 1, "", "general_settings" => ""],
            ["id" => 2, "", "general_settings" => ""],
            ["id" => 3, "", "general_settings" => ""],
        ];
        // methods that should be touched
        $productAndKittingMock = \Mockery::mock(ProductAndKitting::class . '[get]');
        $productAndKittingMock->shouldReceive('get')->once()->andReturn($products);

        $this->app->instance(ProductAndKitting::class, $productAndKittingMock);

        $content = $this->json('POST', 'api/v1/shop/cart-details', $input)->assertStatus(200)->getContent();
        $content = json_decode($content, true);

        $this->assertEquals([
            "data" => $products,
            'total' => 1,
            'total_price' => 0,
            'total_cv' => 0,
            'total_ba_cv' => 0,
            'total_amp' => 0,
        ], $content);
    }
}