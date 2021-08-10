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

$router->get('/', function () use ($router) {
    echo 'API Pelindo Report - Shift';
});

$router->post('/login', 'AuthController@authenticate');

$router->group(['prefix' => 'lupapassword'], function() use ($router) {
    $router->post('/kirimnohp', 'LupaPasswordController@kirimNoHp');
    $router->post('/kirimulangotp', 'LupaPasswordController@kirimOtp');
    $router->post('/otp', 'LupaPasswordController@cekOtp');
    $router->post('/setpassword', 'LupaPasswordController@setPassword');
});

$router->group(['prefix' => 'superadmin', 'middleware' => ['jwt.auth', 'role.superadmin']], function() use ($router) {
    // crud user
    $router->group(['prefix' => 'user'], function() use ($router) {
        $router->get('/', 'UserController@index');
        $router->get('/{id}', 'UserController@show');
        $router->post('/', 'UserController@store');
        $router->put('/{id}', 'UserController@update');
        $router->delete('/{id}', 'UserController@delete');
    });

    // crud role
    $router->group(['prefix' => 'role'], function() use ($router) {
        $router->get('/', 'RoleController@index');
        $router->get('/{id}', 'RoleController@show');
        $router->post('/', 'RoleController@store');
        $router->put('/{id}', 'RoleController@update');
        $router->delete('/{id}', 'RoleController@delete');
    });

});

$router->group(['prefix' => 'supervisor', 'middleware' => ['jwt.auth', 'role.supervisor']], function() use ($router) {
    
});


$router->group(['prefix' => 'eos', 'middleware' => ['jwt.auth', 'role.eos']], function() use ($router) {
    
});
