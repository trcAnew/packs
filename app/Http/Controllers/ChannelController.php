<?php

namespace App\Http\Controllers;

use App\Model\Channel;
use App\Model\ChannelKey;
use App\Model\ChannelVersion;
use App\Model\Game;
use App\Model\GameHasChannel;
use App\Model\Param;
use App\Model\Signature;
use App\Utils\Common;
use App\Utils\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChannelController extends Controller
{
  protected $table_exec = ['id', 'json'];
  protected $table_arr = ['value'];
  protected $path = 'D://wwwroot/';
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
  public function get_data($alias)
  {
    // $data = ChannelKey::leftJoin('channels', 'channels.id', '=', 'channel_keys.cid')
    //   ->where('channel_keys.cid', '=', $request->cid)
    //   ->select('channel_keys.abstract', 'channel_keys.id', 'channel_keys.key', 'channel_keys.cid','channels.name as cname')
    //   ->get();
    $path = $this->path . 'MolePackageTool-Android/channels/' . $alias . '/templates.json';
    if (!is_file($path)) {
      return json_encode((object) null);
    }
    $data = Common::open_file($path);
    return $data;
  }
  /**
   * @description:获取绑定的签名ID
   * @param $request
   * @return:
   */
  public function get_channel(Request $request)
  {
    // $data = Channel::find($request->id);
    $gameHasChannel = GameHasChannel::where('game_id', '=', $request->game_id)
      ->where('channel_id', '=', $request->id)
      ->first();
    // $gameHasChannel->signature_id = $gameHasChannel['signature_id'];
    return response()->json([
      'data' => $gameHasChannel,
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
    $game = Game::find($model['game_id']);
    // $channel = Channel::find($request->channel_id);
    $list = $this->get_json();
    foreach ($list as $item) {
      if ($item->id == $model['channel_id']) {
        $channel = $item;
      }
    }
    if ($this->update_params($model)) {
      $this->update_channel_version($model);
      $this->make_file($model, $channel, $game);
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
  protected function update_channel_version($model)
  {
    $gameHasChannel = GameHasChannel::where('game_id', '=', $model['game_id'])->where('channel_id', '=', $model['channel_id'])->first();
    if ($model['signature_id']) {
      $gameHasChannel->signature_id = $model['signature_id'];
      $gameHasChannel->save();
    }
    return true;
  }
  /**
   * @description: 更新参数数据
   * @param {type} 
   * @return: 
   */
  protected function update_params($model)
  {
    foreach ($model['data'] as $item) {
      foreach ($item['params'] as $data) {
        $params = Param::where('game_id', '=', $model['game_id'])
          ->where('channel_id', '=', $model['channel_id'])
          ->where('keys', '=', $data['key'])
          ->first();
        if (!$params) {
          $params = new Param;
          $params->keys = $data['key'];
          $params->game_id = $model['game_id'];
          $params->channel_id = $model['channel_id'];
        }
        $params->type = $item['id'];
        if (isset($data['value'])) {
          $params->val = $data['value'];
          $params->save();
        }
      }
    }
    return true;
  }
  /**
   * @description: 验证参数生成文件
   * @param $model
   * @return: json
   */
  protected function make_file($model, $channel, $game)
  {
    $path = '../../MolePackageTool-Android/channels/' . $channel->name . '/configs/' . $game['game_id'] . '/assets';
    // Upload::touch_file($path . '/channel.txt', $model['channelId']);
    foreach ($model['data'] as $item) {
      $path = '../../MolePackageTool-Android/channels/' . $channel->name . '/configs/' . $game['game_id'] . '/' . $item['id'] . '.json';
      $json = [];
      if ($model['signature_id'] && $item['id'] == 2) {
        $sign = Signature::find($model['signature_id']);
        $sign_path = $this->get_dir() . $sign->file;
        $json = [];
        $json['keystore'] = $sign_path;
        $json['password'] = $sign->password;
        $json['alias'] = $sign->alias;
        $json['alias_password'] = $sign->alias_password;
      }
      foreach ($item['params'] as $i) {
        if (isset($i['value'])) {
          $json[$i['key']] = $i['value'];
          // array_push($json, [
          //   $i['key'] => $i['value']
          // ]);
        } else {
          $json[$i['key']] = '';
        }
      }
      Upload::touch_file($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    return true;
    // if (count($gameSettings) > 0) {
    //   $path = '../../MolePackageTool-Android/channels/' . $channel['alias'] . '/configs/' . $game['game_id'] . '/assets/gameSetting.json';

    // }
    // if (count($compile) > 0) {
    //   $path = '../../MolePackageTool-Android/channels/' . $channel['alias'] . '/configs/' . $game['game_id'] . '/package.json';
    //   Upload::touch_file($path, json_encode($compile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    // }
  }
  public function get_dir()
  {
    $path = dirname(dirname($_SERVER['DOCUMENT_ROOT']));
    $path_arr = explode('\\', $path);
    $path_dir = implode('/', $path_arr);
    return $path_dir;
  }
  /**
   * @description: 获取渠道配置参数
   * @param {type}
   * @return:
   */
  public function get_params_value(Request $request)
  {
    $model = $request->all();
    $channelParams = json_decode($this->get_data($model['channel']['alias']), true);
    $param = Param::where('game_id', '=', $request->gid)->get();
    foreach ($channelParams as $k => $item) {
      $data = $item['params'];
      foreach ($data as $key => $i) {
        if (isset($i['default'])) {
          $channelParams[$k]['params'][$key]['value'] = $i['default'];
        }
        foreach ($param as $p) {
          if ($p['keys'] == $i['key']) {
            $channelParams[$k]['params'][$key]['value'] = $p['val'];
          }
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
  /**
   * @description: 读取文件
   * @param {type} 
   * @return: 
   */
  public function get_json()
  {
    $path = 'D://wwwroot/MolePackageTool-Android/channels/channel.json';
    $list = json_decode(Common::open_file($path));
    return $list;
  }
}
