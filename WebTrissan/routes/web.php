<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/license', function () {
    return view('license');
});

Route::get('/works', function () {
    return view('work');
});

Route::get('/about', function () {
    return view('abtus');
});

Route::get('/portofolio', function () {
    return view('portofolio');
});