<?php

namespace App\Http\Controllers;

use App\Model\Permission;
use App\Model\Role as AppRole;
use App\Model\RoleHasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Models\Role as SpatieRole;

class RolesController extends Controller
{
    //
    public function __construct()
    {
      $this->middleware('auth:api');
    }
    public function list(Request $request)
    { 
      $data = AppRole::all();
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data'=>$data
      ],200);
    }
    /**
     * @description: 
     * @param $request
     * @return: 
     */
    public function add(Request $request){
      $role = $this->created($request);
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
      ],200);
    }
    /**
     * @description: 
     * @param $request
     * @return: 
     */
    protected function created($request){
      $arr = explode(',',$request->pid);
      if(!isset($request->id)){
        $role = SpatieRole::create([
          'name' => $request->name
        ]);
        // $role = 1;
        if(count($arr) > 0){
          $this->add_rule_permission($role,$arr);
        }
      }else{
        $role = SpatieRole::findById($request->id);
        if(count($arr) > 0){
          $this->remove_add_permission($arr,$role);
        }
      }
      return $role;
    }
    /**
     * @description: 
     * @param $role,$request
     * @return: 
     */
    protected function add_rule_permission($role,$arr)
    {
      $data = Permission::find($arr)->toArray();
      $permission = array_column($data,'name');
      $role->syncPermissions($permission);
      return true;
    }
    /**
     * @description: 
     * @param {type} 
     * @return: 
     */
    protected function remove_add_permission($arr,$role)
    {
      $array = RoleHasPermission::where('role_id','=',$role->id)->get()->toArray();
      if(count($array) > 0){
        $id = array_column($array,'permission_id');
        $perArr = Permission::find($id)->toArray();
        $name = array_column($perArr,'name');
        $role->revokePermissionTo($name);
      }
      
      $data = Permission::find($arr)->toArray();
      $permission = array_column($data,'name');
      $role->syncPermissions($permission);
      return true;
    }
    /**
     * @description: 
     * @param $request 
     * @return: 
     */
    protected function info(Request $request)
    {
      $model['data'] = SpatieRole::findById($request->id);
      $arr = RoleHasPermission::where('role_id','=',$request->id)->get()->toArray();
      $model['key'] = array_column($arr,'permission_id');
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data' => $model
      ],200);
    }
    /**
     * @description: 
     * @param {type} 
     * @return: 
     */
    public function view_permission(Request $request)
    {
      $arr = RoleHasPermission::where('role_id','=',$request->id)->get()->toArray();
      $id = array_column($arr,'permission_id');
      $data = Permission::find($id);
      $data = $this->recu_list($data);
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data' => $data
      ],200);
    }
    /**
     * @description: 递归 
     * @param $data
     * @return: 
     */
    public function recu_list($data,$fid = 0)
    {
      $arr = [];
      foreach($data as $item){
        if($item['fid'] == $fid){
          $item['children'] = $this->recu_list($data,$item['id']);
          if($item['children'] == null){
            unset($item['children']);           
          }
          $arr[] = $item;
        }
      }
      return $arr;
    }
}
