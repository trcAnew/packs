<?php

namespace App\Http\Controllers;

use App\Model\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission as SpatiePermission;

class PermissionController extends Controller
{
    //
    protected $table_exec = ['id'];
    public function __construct()
    {
      $this->middleware('auth:api');
    }
    /**
     * @description: 
     * @param {type} 
     * @return: 
     */
    public function parent(Request $request){
      $data = Permission::where('fid','=',0)->get();
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data'=> $data
      ],200);
    }
    /**
     * @description: 添加权限
     * @param $request
     * @return: 
     */
    public function add(Request $request){
      if(isset($request->id)){
        $sql = Permission::find($request->id);
        $this->save($request->all(),'permissions',$sql);
      }else{
        $this->created($request);
      }
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
      ],200);
    }
    /**
     * @description: 创建权限
     * @param {type} 
     * @return: 
     */
    protected function created($request){
      SpatiePermission::create([
        'name'=>$request['name'],
        'path'=>$request['path'],
        'type'=>$request['type'],
        'hidden'=>$request['hidden'],
        'fid'=>$request['fid'],
        'icon'=>$request['icon'],
      ]);
      return true;
    }
    /**
     * @description: 保存修改
     * @param $request
     * @return: 
     */
    public function save($model,$table_name,$sql)
    {
        $columns = Schema::getColumnListing($table_name);
        Log::debug($columns);
        // $sql = new Channel;
        foreach ($model as $key=>$item)
        {
            if(in_array($key,$columns) && !in_array($key,$this->table_exec))
            {
                $sql->$key = $item;
            }
        }
        $sql->save();
        return $sql;
    }
    /**
     * @description: 获取列表
     * @param $request 
     * @return: 
     */
    public function list(Request $request){
      $data = Permission::all();
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data' => $this->recu_list($data)
      ],200);
    }
    /**
     * @description: 递归 
     * @param $data
     * @return: 
     */
    public function recu_list($data,$fid = 0){
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
    /**
     * @description: 
     * @param $request
     * @return: $json
     */
    public function info(Request $request){
      $info = Permission::find($request->id);
      return response()->json([
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'data' => $info
      ],200);
    } 
    public function delete(Request $request){
      if($this->validate_delete($request)){
        Permission::destroy($request->id);
        return response()->json([
          'access_token' => $request->token,
          'token_type' => 'bearer',
          'expires_in' => auth('api')->factory()->getTTL() * 60,
        ],200);
      }
    }
    /**
     * @description: 
     * @param $request
     * @return: 
     */
    protected function validate_delete($request){
      $arr = Permission::where('fid','=',$request->id)->get();
      if(count($arr) > 0){
        return false;
      }
      return true;
    }
}
