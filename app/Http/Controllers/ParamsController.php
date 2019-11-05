<?php

namespace App\Http\Controllers;

use App\Model\Channel;
use App\Model\Param;
use Illuminate\Http\Request;

class ParamsController extends Controller
{
    //
    public function __construct()
    {
      $this->middleware('auth:api');
    }
    /**
     * @description: 
     * @param $request
     * @return: 
     */
    public function list(Request $request)
    {
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data'=> $this->get_list($request)
      ],200);
    }
    protected function get_list($request){
      $data = Channel::leftJoin('channel_params','channels.id','=','channel_params.cid')
              ->where('channel_params.value','!=',null)
              ->where('channels.state','=',1)
              ->select('channels.*')
              ->get();
      return $data;
    }
}
