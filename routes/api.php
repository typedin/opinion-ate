<?php

use CloudCreativity\LaravelJsonApi\Facades\JsonApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

JsonApi::register('default')->routes(function ($api) {
    $api->resource('restaurants')->readOnly();
    $api->resource('dishes')
        ->relationships(function ($relations) {
            $relations->hasMany("comments");
        }) ->readOnly();
    $api->resource("comments")->readOnly();
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


JsonApi::register("default")->middleware("auth:sanctum")->routes(function ($api) {
    $api->resource('restaurants')->only('create', 'update', 'delete');
    $api->resource('dishes')->only('create', 'update', 'delete');
    $api->resource("comments")->only("create", "update", "delete");
    $api->resource("ratings");
});
