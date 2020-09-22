<?php

namespace App\JsonApi\Restaurants;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'restaurants';

    /**
     * @param \App\Restaurant $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Restaurant $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            "name" => $resource->name,
            "address" => $resource->address,
            'createdAt' => $resource->created_at->toAtomString(),
            'updatedAt' => $resource->updated_at->toAtomString(),
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'dishes' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
            'images' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
        ];
    }
}
