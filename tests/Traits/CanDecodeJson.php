<?php

namespace Tests\Traits;

use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;

/**
 * Trait CanDecodeJson
 * @author typedin
 */
trait CanDecodeJson
{
    private function decodedJson(TestResponse $response, $path="data")
    {
        return Arr::get(json_decode($response->getContent(), true), $path);
    }
}
