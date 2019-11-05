<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\Permission;
use App\User;
use Illuminate\Support\Facades\Auth;
use Dingo\Api\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
  /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

  use AuthenticatesUsers;


  /**
   * Where to redirect users after login.
   *
   * @var string
   */
  protected $redirectTo = '/home';

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth:api')->except(['login']);
  }

  /**
   * @param Request $request
   * @return bool
   */
  protected function attemptLogin(Request $request)
  {
    $userInfo = $this->getData($request);
    if ($this->guard()->attempt($userInfo, true))
      return true;
    return false;
  }
  /**
   * Send the response after the user was authenticated.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  protected function sendLoginResponse(Request $request)
  {
    //        $request->session()->regenerate();

    $this->clearLoginAttempts($request);

    return $this->authenticated($request, $this->guard()->user())
      ?: redirect()->intended($this->redirectPath());
  }

  protected function authenticated(Request $request)
  {
    return response()->json([
      'access_token' => (string) auth('api')->getToken(),
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }

  /**
   * @param $request
   * @return mixed
   */
  protected function getData($request)
  {
    return $request->only($this->username(), 'password');
  }

  /**
   * @return mixed
   */
  protected function username()
  {
    return 'username';
  }

  /**
   * @return mixed
   */
  protected function guard()
  {
    return Auth::guard('api');
  }
  /**
   * @description: 获取用户所有权限
   * @param $request
   * @return: json
   */
  public function powers(Request $request)
  {
    $user = JWTAuth::toUser(JWTAuth::parseToken()->getToken());
    if ($user->id == 1) {
      $power = Permission::where('type', '=', 1)->get();
    } else {
      $power = $user->getPermissionsViaRoles();
    }
    $data = $this->recu_list($power);
    return response()->json([
      'data' => $data,
      'access_token' => (string) auth('api')->getToken(),
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 递归 
   * @param $data
   * @return: 
   */
  public function recu_list($data, $fid = 0)
  {
    $arr = [];
    foreach ($data as $item) {
      if ($item['fid'] == $fid) {
        $item['children'] = $this->recu_list($data, $item['id']);
        if ($item['children'] == null) {
          unset($item['children']);
        }
        $arr[] = $item;
      }
    }
    return $arr;
  }
  /**
   * @description: 获取userInfo
   * @param {type} 
   * @return: 
   */
  public function user_info(Request $request)
  {
    $user = JWTAuth::toUser(JWTAuth::parseToken()->getToken());
    Log::debug([
      'user'=>JWTAuth::parseToken()->getToken()
    ]);
    return response()->json([
      'data' => $user,
      'access_token' => (string) auth('api')->getToken(),
    ]);
  }
}
