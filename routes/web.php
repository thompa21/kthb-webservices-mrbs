<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// http://dev.lib.kth.se/webservices/mrbs/api/v1/xxxxxxx?yyyy=111&zzzz=2

$router->group(['prefix' => 'api/v1/'], function ($router) {
    //Sätt alltid statiska routes(entries/search) före dynamiska(entries/{id})

    $router->get('checkjwt','JWTController@index');
    $router->get('getuserfromtoken','JWTController@getUserFromToken');

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
    $router->get('rooms/{id}','RoomController@getRoom');
    $router->get('noauth/rooms','RoomController@noauthindex');
    $router->get('noauth/roomsavailability','RoomController@noauthgetRoomAvailability');

    $router->get('eventstest','EventController@indextest');
});
