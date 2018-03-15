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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// http://dev.lib.kth.se/webservices/mrbs/api/v1/xxxxxxx?yyyy=111&zzzz=2

$router->group(['prefix' => 'api/v1/'], function ($router) {
    //Sätt alltid statiska routes(entries/search) före dynamiska(entries/{id})
    $router->get('login/','UserController@authenticate');
    $router->get('entries','EntryController@index');
    $router->get('noauth/entries','EntryController@noauthindex');
    $router->get('entries/search','EntryController@search');
    $router->get('noauth/entries/{id}','EntryController@noauthgetEntry');
    $router->get('entries/{id}','EntryController@getEntry');
    $router->get('entries/confirm/{confirmation_code}','EntryController@confirm');

    $router->post('entries','EntryController@createEntry');
    $router->put('entries/{id}','EntryController@updateEntry');    
    $router->delete('entries/{id}','EntryController@deleteEntry ');

    $router->get('rooms','RoomController@index');
    $router->get('noauth/rooms','RoomController@noauthindex');
    $router->get('noauth/roomsavailability','RoomController@noauthgetRoomAvailability');
});
