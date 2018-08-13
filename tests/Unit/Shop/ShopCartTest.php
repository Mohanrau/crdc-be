<?php
namespace Tests\Unit\Shop;

use Tests\Unit\TestCase;
use App\Models\Shop\Favorites;

class ShopCartTest extends TestCase
{

    /**
     * Check for all request functionality
     */
    function testInvalidRequestError() {

        $favoritesClass = \Mockery::mock(Favorites::class . '[get]');
        $this->app->instance(Favorites::class, $favoritesClass);

        $content = $this->withoutMiddleware()
            ->json('POST', 'api/v1/shop/cart', [], [], true)
            ->assertStatus(422)
            ->getContent();
        $content = json_decode($content, true);
        $this->assertArrayHasKey("product_id",$content);
        $this->assertArrayHasKey("kitting_id",$content);
        $this->assertArrayHasKey("location_id",$content);
        $this->assertArrayHasKey("country_id",$content);
    }

    /**
     * Check for all request functionality
     */
    function testInvalidListRequestError() {

        $favoritesClass = \Mockery::mock(Favorites::class . '[get]');
        $this->app->instance(Favorites::class, $favoritesClass);

        $content = $this->withoutMiddleware()
            ->json('POST', 'api/v1/shop/cart-details', [], [], true)
            ->assertStatus(422)
            ->getContent();
        $content = json_decode($content, true);
        $this->assertArrayHasKey("location_id",$content);
        $this->assertArrayHasKey("country_id",$content);
    }
}