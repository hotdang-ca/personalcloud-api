<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use DB;

class FilesController extends Controller {

  public function fetch(Request $request, $filename) {

    $_ERROR_404 = ['error' => ['code' => 404, 'description' => 'I have no clue which file you\'re trying to access. So you don\'t get any.']];

    $storedName = $filename . ".png";

    $pathToFile = rtrim(app()->basePath('storage/app'), '/') . '/' . $storedName;
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

      $fileName = $request->file('file')->getClientOriginalName();
      $extension = $request->file('file')->getClientOriginalExtension();
      $ipAddress = $request->ip();

      // TODO: externalize this somewhere. It gives me a random string made up of these chars
      $characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTVWXYZ23456789';
      $string = '';
      $max = strlen($characters) - 1;
      for ($i = 0; $i < 8; $i++) {
        $string .= $characters[mt_rand(0, $max)];
      }

      // TODO: check for name clash
      $storageName = $string . '.' . $extension;

      // Move file
      $destinationPath = rtrim(app()->basePath('storage/app'), '/');
      $request->file('file')->move($destinationPath, $storageName);

      // Save to Database
      // TODO: injection protection
      DB::table('files')->insert([
        'original_name' => $fileName,
        'storage_name'  => $storageName,
        'extension'     => $extension,
        'uploader_ip'   => $ipAddress
      ]);

      // TODO: public URL
      $url = url('/') . "/files/" . $storageName;

      return response()->json(["location" => $url ]);
    }
  }
}
