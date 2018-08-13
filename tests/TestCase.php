<?php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function json($method, $uri, array $data = [], array $headers = [], $returnOnErrors = false) {
        $response = parent::json($method, $uri, $data, $headers);
        // log the error to console if the status code is not 200
        if ($response->getStatusCode() !== 200 && !$returnOnErrors) {
            $object = \GuzzleHttp\json_decode($response->getContent());
            dd($object);
        }
        return $response;
    }
}
