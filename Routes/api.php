<?php

use Modules\Messages\Classes\RouteWebHook;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/messages/webhook/{messager}/{secret_route}', [RouteWebHook::class, 'route']);

