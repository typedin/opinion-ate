<?php

namespace Tests\Traits;

use App\Models\User;

/**
 * Trait HasHeader
 * @author typedin
 */
trait HasHeader
{
    private function getModel(): string
    {
        return self::MODEL;
    }

    private function userToken($method): string
    {
        return User::factory()
            ->create()
            ->createToken(
                "{$method}-{$this->getModel()}",
                ["{$this->getModel()}:{$method}"]
            )->plainTextToken;
    }

    private function headers($method)
    {
        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$this->userToken($method)}"
        ];
    }
}
