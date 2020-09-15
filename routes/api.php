<?php

use CloudCreativity\LaravelJsonApi\Facades\JsonApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

JsonApi::register('default')->routes(function ($api) {
    $api->resource('restaurants')->relationships(function ($relations) {
        $relations->hasMany('dishes');
    });
    $api->resource('dishes')->relationships(function ($relations) {
        $relations->hasOne('restaurant');
    });
});
