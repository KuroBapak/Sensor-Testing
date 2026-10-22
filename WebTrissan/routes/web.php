<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LicensesController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\WorksController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/license', [LicensesController::class, 'index'])->name('licenses');
Route::get('/works', [WorksController::class, 'index'])->name('works');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/portofolio', [PortfolioController::class, 'index'])->name('portfolio');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
