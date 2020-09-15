<?php

use App\Models\Restaurant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Restaurant::with("dishes")->get();
});
