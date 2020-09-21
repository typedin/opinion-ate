<?php

namespace App\JsonApi\Traits;

use Illuminate\Auth\AuthenticationException;

trait CanOnlyBeModifiedByCreatorTrait
{
    /**
     * Authorize a resource update request.
     *
     * @param object $record
     *      the domain record.
     * @param Request $request
     *      the inbound request.
     * @return void
     * @throws AuthenticationException|AuthorizationException
     *      if the request is not authorized.
     */
    public function update($record, $request)
    {
        if ($request->json("data.attributes.user_id") !== (int) $record->user_id) {
            throw new AuthenticationException();
        }
    }
    /**
     * Authorize a resource delete request.
     *
     * @param object $record the domain record.
     *
     * @param Request $request the inbound request.
     *
     * @return void
     * @throws AuthenticationException|AuthorizationException
     *      if the request is not authorized.
     */
    public function delete($record, $request)
    {
        if ($request->json("data.attributes.user_id") !== (int) $record->user_id) {
            throw new AuthenticationException();
        }
    }
}
