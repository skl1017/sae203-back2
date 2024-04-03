<?php

use App\Http\Controllers\ContactsController;
use App\Http\Controllers\TagsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/contacts',[ContactsController::class, 'getContacts']);
Route::get('/contacts/{id}',[ContactsController::class, 'getSingleContact']);
Route::post('/contacts',[ContactsController::class, 'addContact']);
Route::put('/contacts/{id}',[ContactsController::class, 'updateContactWithTags']);
Route::delete('/contacts/{id}',[ContactsController::class, 'deleteContact']);
Route::get('/tags', [TagsController::class, 'getTags']);
Route::post('/tags',[TagsController::class, 'addTag']);
Route::delete('/tags/{id}', [TagsController::class,'deleteTag']);
