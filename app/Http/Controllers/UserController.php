<?php

namespace App\Http\Controllers;

use App\Model\Role as AppRole;
use App\Model\RoleHasPermission;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Models\Permission;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
  //
  protected $table_exec = ['id'];
  public function __construct()
  {
    $this->middleware('auth:api');
  }
  //获取列表
  public function user_list(Request $request)
  {
    $data = User::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
      ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
      ->select('users.*', 'roles.name as role_name')
      ->get();
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $data
    ], 200);
  }
  /**
   * @description: 获取角色列表
   * @param $request
   * @return: 
   */
  public function roles(Request $request)
  {
    $data = Role::all();
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $data
    ], 200);
  }
  /**
   * @description: 添加用户
   * @param $request
   * @return: json
   */
  public function adds(Request $request)
  {
    $model = $request->all();
    if (!isset($model['id'])) {
      $sql = new User();
      $model['password'] = bcrypt($request->password);
    } else {
      $sql = User::find($model['id']);
    }
    $data = $this->created($model, 'users', $sql);
    if (!$sql->hasAnyRole($request->role)) {
      $sql->syncRoles([$request->role]);
    }
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $data
    ], 200);
  }
  /**
   * @description: 
   * @param $request
   * @return: 
   */
  public function created($model, $table_name, $sql)
  {
    $columns = Schema::getColumnListing($table_name);
    Log::debug($columns);
    // $sql = new Channel;
    foreach ($model as $key => $item) {
      if (in_array($key, $columns) && !in_array($key, $this->table_exec)) {
        $sql->$key = $item;
      }
    }
    $sql->save();
    return $sql;
  }
  /**
   * @description: 
   * @param {type} 
   * @return: 
   */
  public function user_info(Request $request)
  {
    $user = JWTAuth::toUser(JWTAuth::parseToken()->getToken());
    if ($user->id == 1) {
      $role = Permission::all()->toArray();
    } else {
      $user = User::leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select('users.*', 'roles.id as role_id')
        ->find($user->id);
      $role = RoleHasPermission::where('role_id', '=', $user->role_id)->get()->toArray();
    }
    $user['roles'] = $role;
    $user['avatar'] = '@/assets/img/head.png';
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $user
    ], 200);
  }
  /**
   * @description: 退出清除token
   * @param {type} 
   * @return: 
   */
  public function logout(Request $request)
  {
    Auth::guard('api')->logout();
    return response()->json(['message' => '登出成功', 'status_code' => 200, 'data' => null]);
  }
}
