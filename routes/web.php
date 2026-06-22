<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')
        ->name('dashboard');

    Route::livewire('/categories', 'pages::category.index')
        ->middleware('role:admin,petugas')
        ->name('category.index');

    Route::livewire('/books', 'pages::book.index')
        ->middleware('role:admin,petugas')
        ->name('book.index');

    Route::livewire('/loans', 'pages::loan.index')
        ->name('loan.index');

    Route::livewire('/users', 'pages::user.index')
        ->middleware('role:admin')
        ->name('user.index');
});

require __DIR__.'/settings.php';