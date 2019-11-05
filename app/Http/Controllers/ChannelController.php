<?php

namespace App\Http\Controllers;

use App\Model\Channel;
use App\Model\ChannelKey;
use App\Model\ChannelVersion;
use App\Model\Game;
use App\Model\GameHasChannel;
use App\Model\Param;
use App\Utils\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChannelController extends Controller
{
  protected $table_exec = ['id', 'json'];
  protected $table_arr = ['value'];
  /**
   * ChannelController constructor.
   */
  public function __construct()
  {
    $this->middleware('auth:api');
    // $this->middleware('refresh.token');
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  protected function list(Request $request)
  {
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $this->getList()
    ], 200);
  }

  /**
   * @return mixed
   */
  protected function getList()
  {
    $list = Channel::where('state', '=', 1)->get();
    return $list;
  }

  protected function upload(Request $request)
  {
    // $this->validateUpload($request);
    Log::debug($request);
    $file = $request->file;
    $json = Upload::upload_label($file);
    Log::debug($json);
    return response()->json([
      'path' => $json['path'],
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }

  protected function upload_save(Request $request)
  {
    $file = $request->file;
    $json = Upload::upload_label($file);
    $sql = Channel::find($request->id);
    $sql->label = $json['path'];
    $sql->save();
    return response()->json([
      'data' => $sql,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }


  /**
   * @param $request
   * @return: \Illuminate\Contracts\Validation\Validator
   */
  public function validateUpload($request)
  {
    return $request->validate([]);
  }

  /**
   * @description:
   * @param $request
   * @return: \Illuminate\Http\JsonResponse
   */
  public function add_channel(Request $request)
  {
    $model = $request->all();
    if (!isset($model['id'])) {
      $sql = new Channel;
      $sql->code = uniqid('C');
    } else {
      $sql = Channel::find($model['id']);
    }
    $sql->state = 1;
    $data = $this->created($model, 'channels', $sql);
    if ($data) {
      $request->cid = $data->id;
      // $this->add_types($request);
      return response()->json([
        'message' => 'success',
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60
      ], 200);
    }
    $this->responseFailReturn($request);
  }
  /**
   * @description: 添加渠道类型
   * @param $request
   * @return:
   */
  // protected function add_types($request)
  // {
  //   $arr = explode(',', $request->platform);
  //   $typeArr =  Type::whereIn('name', $arr)->get()->toArray();
  //   Log::debug($typeArr);
  //   $newArr = [];
  //   foreach ($typeArr as $item) {
  //     array_push($newArr, [
  //       'cid' => $request->cid,
  //       'tid' => $item['id'],
  //       'state' => 1
  //     ]);
  //   }
  //   $state = DB::table('Channel_types')->insert($newArr);
  //   Log::debug('type:' . $state);
  //   return true;
  // }
  /**
   * @description:
   * @param $request
   * @return:
   */
  public function view_params(Request $request)
  {
    $data = $this->get_data($request);
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }

  /**
   * @description:
   * @param {type}
   * @return:
   */
  public function get_data($request)
  {
    $data = ChannelKey::leftJoin('channels', 'channels.id', '=', 'channel_keys.cid')
      ->where('channel_keys.cid', '=', $request->cid)
      ->select('channel_keys.abstract', 'channel_keys.id', 'channel_keys.key', 'channel_keys.cid','channels.name as cname')
      ->get();
    return $data;
  }
  /**
   * @description:
   * @param $request
   * @return:
   */
  public function get_channel(Request $request)
  {
    $data = Channel::find($request->id);
    $gameHasChannel = GameHasChannel::where('game_id','=',$request->game_id)
    ->where('channel_id','=',$request->id)
    ->first();
    Log::debug([
      'info'=>$gameHasChannel
    ]);
    $data->signature_id = $gameHasChannel['signature_id'];
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
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
   * @return: responseJson
   */
  protected function responseFailReturn($request)
  {
    return response()->json([
      'message' => trans('failed'),
    ], 500);
  }
  /**
   * @description:
   * @param $request
   * @return: response json
   */
  protected function save_param(Request $request)
  {
    $model = $request->all();
    if ($this->created($model, 'channel_keys', new ChannelKey)) {
      return response()->json([
        'message' => 'success',
        'access_token' => $request->token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60
      ], 200);
    }
    $this->responseFailReturn($request);
  }
  /**
   * @description: 获取userId
   * @param {type}
   * @return:
   */
  protected function get_userId()
  {
    $user = JWTAuth::toUser(JWTAuth::parseToken()->getToken());
    return $user->id;
  }
  /**
   * @description: 批量更新数据
   * @param $request
   * @return: json
   */
  public function save_params(Request $request)
  {
    $model = $request->all();
    $game = Game::find($model['g_id']);
    $channel = Channel::find($request->channel_id);
    if($this->update_params($model)){
      $this->update_channel_version($model);
      $this->make_file($model,$channel,$game);
    }
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 检测更新版本号
   * @param {type} 
   * @return: 
   */
  protected function update_channel_version($model){
    $gameHasChannel = GameHasChannel::find($model['gc_id']);
    if(isset($model['form']['signature_id']) && $model['form']['signature_id'] !== $gameHasChannel['signature_id'])
    {
      $gameHasChannel->signature_id = $model['form']['signature_id'];
      $gameHasChannel->save();
    }
    if(isset($model['channel_version'])){
      $cVersion = ChannelVersion::where('val','=',$model['channel_version'])
      ->where('gc_id','=',$gameHasChannel->id)
      ->first();
      if(!$cVersion){
        $cVersion = new ChannelVersion;
        $cVersion->gc_id = $gameHasChannel->id;
        $cVersion->val = $model['channel_version'];
      }
      if(isset($model['compile'])){
        $cVersion->compile = $model['compile'];
      }
      if(isset($model['gameSettings'])){
        $cVersion->gameSettings = $model['gameSettings'];
      }
      $cVersion->save();
    }
    return true;
  }
  /**
   * @description: 更新参数数据
   * @param {type} 
   * @return: 
   */
  protected function update_params($model){
    foreach ($model['data'] as $data) {
      $dat = Param::where('ck_id', '=', $data['id'])
      ->where('userId','=',$this->get_userId())
      ->first();
      if (!$dat) {
        $dat = new Param;
        $dat->ck_id = $data['id'];
      }
      $dat->userId = $this->get_userId();
      $dat->gc_id = $model['gc_id'];
      if(isset($data['value'])){
        $dat->val = $data['value'];
        if (isset($data['compile'])) {
          $dat->compile = $data['compile'];
        }
        if (isset($data['gameSettings'])) {
          $dat->gameSettings = $data['gameSettings'];
        }
        $dat->save();
      } 
    }
    return true;
  }
  /**
   * @description: 验证参数生成文件
   * @param $model
   * @return: json
   */
  protected function make_file($model,$channel,$game)
  {
    $path = '../../MolePackageTool-Android/channels/' . $channel['alias'].'/configs/'.$game['game_id']. '/assets';
    Upload::touch_file($path . '/channel.txt', $model['channelId']);
    $compile = [];
    $gameSettings = [];
    foreach ($model['data'] as $key => $data) {
      if (isset($data['compile']) && $data['compile']) {
        $compile[$data['key']] = $data['value'];
      }
      if (isset($data['gameSettings'])  && $data['gameSettings']) {
        $gameSettings[$data['key']] = $data['value'];
      }
    }
    if(isset($model['compile']) && $model['compile']){
      $compile['channel_version'] = $model['channel_version'];
    }
    if(isset($model['gameSettings']) && $model['gameSettings']){
      $gameSettings['channel_version'] = $model['channel_version'];
    }
    if (count($gameSettings) > 0) {
      $path = '../../MolePackageTool-Android/channels/' . $channel['alias'] . '/configs/'.$game['game_id']. '/assets/gameSetting.json';
      Upload::touch_file($path, json_encode($gameSettings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
    if (count($compile) > 0) {
      $path = '../../MolePackageTool-Android/channels/' . $channel['alias'] . '/configs/'.$game['game_id'].'/package.json';
      Upload::touch_file($path, json_encode($compile, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }
  }
  /**
   * @description: 获取渠道配置参数
   * @param {type}
   * @return:
   */
  public function get_params_value(Request $request)
  {
    $channelParams = $this->get_data($request);;
    $channelParams = json_decode(json_encode($channelParams), true);
    foreach ($channelParams as $key => $item) {
      $data = Param::where('ck_id', '=', $item['id'])
      ->where('userId','=',$this->get_userId())
      ->first();
      if ($data) {
        $channelParams[$key]['value'] = $data->val;
        $channelParams[$key]['compile'] = false;
        $channelParams[$key]['gameSettings'] = false;
        if($data->compile == 1){
          $channelParams[$key]['compile'] = true;
        }
        if($data->gameSettings == 1){
          $channelParams[$key]['gameSettings'] = true;
        }
      }
    }
    return response()->json([
      'data' => $channelParams,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
}
