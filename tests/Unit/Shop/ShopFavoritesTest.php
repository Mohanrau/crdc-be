<?php
namespace Tests\Unit\Shop;

use Tests\Unit\TestCase;
use App\Models\Shop\Favorites;
use Tests\Shared\Traits\MockAuth;

class ShopFavoritesTest extends TestCase
{

    use MockAuth;
    /**
     * Check for all request functionality
     */
    function testInvalidRequestError() {

        $favoritesClass = \Mockery::mock(Favorites::class . '[get]');
        $this->app->instance(Favorites::class, $favoritesClass);

        $this->mockUser();
        $content = $this->withoutMiddleware()
            ->json('POST', 'api/v1/shop/favorite', [], [], true)
            ->assertStatus(422)
            ->getContent();
        $content = json_decode($content, true);
        $this->assertArrayHasKey("product_id",$content);
        $this->assertArrayHasKey("kitting_id",$content);
    }

    /**
     * Check for all request functionality
     */
    function testInvalidListRequestError() {

        $this->mockUser();
        $favoritesClass = \Mockery::mock(Favorites::class);
        $favoritesClass->shouldReceive('getFavoritesForUser')->once();
        $this->app->instance(Favorites::class, $favoritesClass);

        $this->withoutMiddleware()
            ->json('GET', 'api/v1/shop/favorite', [], [])
            ->assertStatus(200)
            ->getContent();
    }
}