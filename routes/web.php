<?php

use App\Models\Restaurant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Restaurant::with("dishes")->get();
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia\Inertia::render('Dashboard');
})->name('dashboard');
