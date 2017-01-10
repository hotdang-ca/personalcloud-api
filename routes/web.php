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

$app->get('/upload', function () use ($app) {
  $page = <<<HTML
  <p>Upload a file</p>
  <form action="/api/v1/file" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br/>
    <input type="submit" value="Send'r on Up!" />
  </form>
HTML;

  return response($page, 200);
});

$app->get('/file/{filename}', 'FilesController@fetch');

$app->group(['prefix' => 'api/v1'], function () use ($app) {
  $app->get('/about', function () use ($app) {
      return response()->json(["version" => $app->version()]);
  });

  $app->get('/file/{filename}', 'FilesController@info');
  $app->post('/file', 'FilesController@upload');
});
