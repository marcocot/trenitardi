<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'train-monitor')->name('home');
Route::livewire('/status/{trainNumber}', 'train-monitor')->name('train.status');
