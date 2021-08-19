<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

\URL::forceScheme('https');
\URL::forceRootUrl(env('APP_URL', 'https://pelindo.primakom.co.id/api/shift/'));

$router->get('/', function () use ($router) {
    echo 'API Pelindo Report - Shift';
});

$router->group(['prefix' => 'superadmin', 'middleware' => ['jwt.auth', 'role.superadmin']], function() use ($router) {
    
});

$router->group(['prefix' => 'supervisor', 'middleware' => ['jwt.auth', 'role.supervisor']], function() use ($router) {
    $router->group(['prefix' => 'jadwal'], function() use ($router) {
        $router->get('/', 'PenjadwalanController@index');
        $router->get('/{id}', 'PenjadwalanController@show');
        $router->post('/', 'PenjadwalanController@store');
    });
});

$router->group(['prefix' => 'eos', 'middleware' => ['jwt.auth', 'role.eos']], function() use ($router) {
    
});

$router->group(['prefix' => 'utils'], function() use ($router) {
    $router->group(['prefix' => 'shift'], function() use ($router) {
        $router->get('/', 'ShiftController@index');
    });    
});