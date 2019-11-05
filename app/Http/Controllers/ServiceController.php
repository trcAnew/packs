<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
  //
  public function __construct()
  {
    $this->middleware('auth:api')->except(['receive_post']);
  }
  public function receive_post(Request $request)
  {
    Log::debug([
      'json' => $request->all()
    ]);
    return response()->json([
      'message' => 'success',
      'code' => 200,
    ], 200);
  }
}
