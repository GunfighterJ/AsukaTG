<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/{bot}',
    ['as' => 'bans', 'uses' => 'BotController@index']);

$app->post('/{bot}',
    ['as' => 'bans', 'uses' => 'BotController@index']);

$app->get('/{bot}/webhook/{action}',
    ['as' => 'bans', 'uses' => 'BotController@updateWebhook']);