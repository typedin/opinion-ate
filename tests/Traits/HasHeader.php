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

    private function userToken($method, $id): string
    {
        $appleSauce = User::where("id", $id)->first();
        
        if ($appleSauce) {
            return $appleSauce
            ->createToken(
                "{$method}-{$this->getModel()}",
                ["{$this->getModel()}:{$method}"]
            )->plainTextToken;
        }

        return User::factory()
            ->create(["id" => $id])
            ->createToken(
                "{$method}-{$this->getModel()}",
                ["{$this->getModel()}:{$method}"]
            )->plainTextToken;
    }

    private function headersWithCredentials($method, $id=1)
    {
        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            "Authorization" => "Bearer {$this->userToken($method, $id)}"
        ];
    }

    private function headersWithNoCredentials()
    {
        return [
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ];
    }

    private function emptyHeader()
    {
        return [];
    }
}
