<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use DB;

class FilesController extends Controller {

  public function __construct()
  {
    $this->ERROR_404 = ['error' => ['code' => 404, 'description' => 'I have no clue which file you\'re trying to access. So you don\'t get any.']];
    $this->ERROR_400 = ['error' => ['code' => 400, 'description' => 'I have no idea what to do with what you just sent me. Best to just try again, I guess.']];
    $this->ERROR_410 = ['error' => ['code' => 410, 'description' => 'That file is gone. I have no idea where it went. It was here one moment, and then gone the next. Don\'t bother retrying.']];
    $this->ERROR_415 = ['error' => ['code' => 415, 'description' => 'Your file type isn\'t welcome around these parts. Try a different file.']];
    $this->ERROR_413 = ['error' => ['code' => 413, 'description' => 'What are you trying to do?! That file is way too big.']];

    // LONG LINE ---->
    $this->disallowedExtensions = array("exe","zip","tar","tgz","gz","rar","iso","bin","pif","dmg","msi","msp","com","scr","hta","htm","html","css","js","jsx","app","cpl","msc","jar","java","bat","cmd","vb","vbs","vbe","ws","wsc","wsf","wsh","ps1","ps1xml","ps2","ps2xml","psc1","psc2","scf","lnk","inf","reg");
  }

  /**
   * Provides information about a file
   *
   * @param Request   $request  Provided by lumen, describes the request
   * @param string    $filename The filename provided in the URI path for which to search for
   *
   * @return Response The Lumen Response Object with the data, or a JSON-formatted error object
   *
   */
  public function info(Request $request, $filename) {

    $fileInfo = DB::table('files')->where('storage_name', $filename)->first();
    if (! $fileInfo) {
      return response()->json($this->ERROR_404, 404);
    }

    $diskName = $fileInfo->storage_name;
    $url = url('/') . "/file/" . $diskName;

    return response()->json([
      "original_name" => $fileInfo->original_name,
      "storage_name" => $fileInfo->storage_name,
      "extension" => $fileInfo->extension,
      "ip_address" => $fileInfo->uploader_ip,
      "created_at" => $fileInfo->created_at,
      "download_path" => $url
    ], 200);
  }

  /**
   * Provides the raw file requested
   *
   * @param Request   $request  Provided by lumen, describes the request
   * @param string    $filename The filename provided in the URI path for which to search for
   *
   * @return Response The original file as binary, or a JSON-formatted error object
   *
   */
  public function fetch(Request $request, $filename) {
    $fileInfo = DB::table('files')->where('storage_name', $filename)->first();
    if (! $fileInfo) {
      return response()->json($this->ERROR_404, 404);
    }

    // TODO:
    $realFile = $fileInfo->original_name;
    $diskName = $fileInfo->storage_name . '.' . $fileInfo->extension;

    $pathToFile = rtrim(app()->basePath('storage/app'), '/') . '/' . $diskName;

    if (file_exists($pathToFile)) {
      return response()->download($pathToFile, $fileInfo->original_name);
    } else {
      return response()->json($this->ERROR_404, 404);
    }
  }

  /**
   * Uploads a file
   *
   * @param Request   $request  Provided by lumen, describes the request. Should come from a multipart/form-data post request
   *
   * @return Response The Lumen Response Object with info on where the file can be accessed
   *
   */

  public function upload(Request $request) {
    if ($request->file('file')->isValid()) {

      $fileName = $request->file('file')->getClientOriginalName();
      $extension = strtolower($request->file('file')->getClientOriginalExtension());
      $ipAddress = $request->ip();

      if (in_array($extension, $this->disallowedExtensions)) {
        return response()->json($this->ERROR_415, 415);
      }

      // TODO: externalize this somewhere. It gives me a random string made up of these chars
      $characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTVWXYZ23456789';
      $string = '';
      $max = strlen($characters) - 1;
      for ($i = 0; $i < 8; $i++) {
        $string .= $characters[mt_rand(0, $max)];
      }

      // TODO: check for name clash
      $storageName = $string;

      // Move file
      $destinationPath = rtrim(app()->basePath('storage/app'), '/');
      $request->file('file')->move($destinationPath, $storageName . '.' . $extension);

      // Save to Database
      // TODO: injection protection
      DB::table('files')->insert([
        'original_name' => $fileName,
        'storage_name'  => $storageName,
        'extension'     => $extension,
        'uploader_ip'   => $ipAddress
      ]);

      // TODO: public URL
      $url = url('/') . "/file/" . $storageName;

      return response()->json(["location" => $url ], 200);
    } else {
      return response()->json($this->ERROR_400, 400);
    }
  }
}
