<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FilesController extends Controller {

  public function fetch(Request $request, $filename) {
        
    $_ERROR_404 = ['error' => ['code' => 404, 'description' => 'I have no clue which file you\'re trying to access. So you don\'t get any.']];

    $storedName = $filename . ".png";

    $pathToFile = rtrim(app()->basePath('public/documents'), '/') . '/' . $storedName;
    if (file_exists($pathToFile)) {
      return response()->download($pathToFile);
    } else {
    return response(
      $_ERROR_404['error']['description'] . $pathToFile,
      $_ERROR_404['error']['code']);
    }
  }
  /**
   *
   */
  public function upload(Request $request) {
    // $file = $request->file('file');//'file'];
    // return $file;

    if ($request->file('file')->isValid()) {

      $filename = $request->file('file')->getClientOriginalName();
      $extension = $request->file('file')->getClientOriginalExtension();

      // TODO: externalize this somewhere
      $characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTVWXYZ23456789';
      $string = '';
      $max = strlen($characters) - 1;
      for ($i = 0; $i < 8; $i++) {
        $string .= $characters[mt_rand(0, $max)];
      }

      $destinationPath = rtrim(app()->basePath('public/documents'), '/');

      // TODO: check for name clash
      $storageName = $string . '.' . $extension;

      // todo: save the file to the database

      $request->file('file')->move($destinationPath, $storageName);

      return response()->json(["location" => "http://.../files/$storageName"]);
    }
  }
}
