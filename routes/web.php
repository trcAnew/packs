<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\DB;

Route::get('/', function () {
  dd('a');
    return view('welcome');
});
Route::get('test_exec',function() {
  exec("d:\\exec.bat",$out);
  dd($out);
});

Auth::routes();
