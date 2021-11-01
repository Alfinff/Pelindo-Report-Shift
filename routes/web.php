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
\URL::forceRootUrl(env('APP_URL', 'https://centro.pelindo.co.id/api/shift/'));

$router->get('/', function () use ($router) {
    echo 'API Pelindo Report - Shift';
});

$router->get('/tesdb', function () use ($router) {
    // Test database connection
    try {
        // DB::connection()->getPdo();
        if(DB::connection()->getDatabaseName())
        {
            echo "conncted sucessfully to database ".DB::connection()->getDatabaseName();
        } else {
            echo 'no';
        }
    } catch (\Exception $e) {
        die("Could not connect to the database.  Please check your configuration. error:" . $e );
    }
});

$router->group(['prefix' => 'superadmin', 'middleware' => ['jwt.auth', 'role.super']], function() use ($router) {
    $router->group(['prefix' => 'jadwal'], function() use ($router) {
        $router->group(['prefix' => 'temp'], function() use ($router) {
            $router->get('/', 'PenjadwalanController@getListTemp');
        });
    });
});

$router->group(['prefix' => 'supervisor', 'middleware' => ['jwt.auth', 'role.super']], function() use ($router) {
    $router->group(['prefix' => 'jadwal'], function() use ($router) {
        $router->get('/', 'PenjadwalanController@index');
        $router->get('/export', 'ShiftController@export');
        $router->get('/history', 'PenjadwalanController@history');
        $router->post('/tes', 'PenjadwalanController@tes');
        $router->post('/', 'PenjadwalanController@store');

        $router->group(['prefix' => 'temp'], function() use ($router) {
            $router->get('/', 'PenjadwalanController@temp');
            $router->group(['prefix' => 'data'], function() use ($router) {
                $router->get('/tahun', 'PenjadwalanController@dataTempTahun');
                $router->get('/bulan/{tahun}', 'PenjadwalanController@dataTempBulan');
            });
            $router->post('/approve', 'PenjadwalanController@approveTemp');
        });

        $router->get('/{id}', 'PenjadwalanController@show');
        $router->put('/{id}', 'PenjadwalanController@update');
        $router->delete('/{id}', 'PenjadwalanController@delete');
    });
});

$router->group(['prefix' => 'utils', 'middleware' => ['jwt.auth', 'role.super']], function() use ($router) {
    $router->group(['prefix' => 'shift'], function() use ($router) {
        $router->get('/', 'ShiftController@index');
    });    
});