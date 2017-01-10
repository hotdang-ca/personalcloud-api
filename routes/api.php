<?php

use Illuminate\Http\Response;

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

$app->group(['prefix' => 'api/v1'], function () use ($app) {

  $app->get('/about', function () use ($app) {
      // return response($app->version());
      return response()->json(["version" => $app->version()]);
  });

  $app->get('/files', function () use ($app) {

  });

  $app->post('/files', function () use $(app) {

  });

});
