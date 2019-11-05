<?php

namespace App\Http\Controllers;

use App\Jobs\ExecJob;
use App\Model\Channel;
use App\Model\ChannelVersion;
use App\Model\Game;
use App\Model\GameHasChannel;
use App\Model\GameVersion;
use App\Model\Param;
use App\Model\Signature;
use App\Model\Task;
use App\User;
use App\Utils\Common;
use App\Utils\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;

class GameController extends Controller
{
  //
  protected $table_exec = ['id'];
  public function __construct()
  {
    $this->middleware('auth:api')->except(['download_apk']);
  }
  /**
   * @description: 
   * @param $request
   * @return: 
   */
  public function get_list(Request $request)
  {
    $data = Game::where('state', '=', 1)
      ->where('userId', '=', $this->get_userId())
      ->get();
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60,
      'data' => $data
    ], 200);
  }
  /**
   * @description: 添加游戏
   * @param $request
   * @return: $json
   */
  public function add(Request $request)
  {
    // dd('a');
    $model = $request->all();
    if (!isset($model['id'])) {
      $model['game_id'] = uniqid('game');
      Upload::mkdir_dir('../../MolePackageTool-Android/games/' . $model['game_id']);
      $sql = new Game;
      $sql->state = 1;
    } else {
      $sql = Game::find($model['id']);
    }
    $sql->userId = $this->get_userId();
    $data = $this->created($model, 'games', $sql);
    if ($data) {
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
   * @description: 创建数据
   * @param $request
   * @return: json
   */
  public function created($model, $table_name, $sql)
  {
    $columns = Schema::getColumnListing($table_name);
    foreach ($model as $key => $item) {
      if (in_array($key, $columns) && !in_array($key, $this->table_exec)) {
        $sql->$key = $item;
      }
    }
    // if ($table_name == 'games') {
    //   $sql->game_key = Str::uuid();
    // }
    $sql->save();
    return $sql;
  }
  /**
   * @description: 获取游戏信息
   * @param $request
   * @return: 
   */
  public function info(Request $request)
  {
    $info = Game::find($request->id)->toArray();
    return response()->json([
      'data' => $info,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 获取签名列表
   * @param $request
   * @return: $json
   */
  public function get_sign(Request $request)
  {
    $userId = $this->get_userId();
    $model = User::with('signatures')->find($userId);
    return response()->json([
      'data' => $model['signatures']
    ]);
  }
  /**
   * @description: 关联用户-签名
   * @param $request
   * @return: $json
   */
  public function user_attach_sign(Request $request)
  {
    $this->created_sign($request);
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 添加签名
   * @param {type} 
   * @return: 
   */
  protected function created_sign($request)
  {
    $model = new Signature;
    $model->name = $request->name;
    $model->password = $request->password;
    $model->alias = $request->alias;
    $model->state = 1;
    $model->alias_password = $request->alias_password;
    $model->file = $request->file;
    $model->userId = $this->get_userId();
    $model->save();
    return $model->id;
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
   * @description:  上传文件
   * @param {type} 
   * @return: 
   */
  protected function sign_file(Request $request)
  {
    $json = Upload::upload_file($request->file, '../../MolePackageTool-Android/config/keystores', '/MolePackageTool-Android/config/keystores');
    $name = explode('.', $request->file->getClientOriginalName())[0];
    return response()->json([
      'path' => $json['path'],
      'name' => $name,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 解除签名绑定
   * @param $request
   * @return: $json
   */
  public function detach_sign(Request $request)
  {
    $sign = Signature::find($request->id);
    $sign->delete();
    unlink('../..' . $sign->file);
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 解绑渠道
   * @param {type} 
   * @return: 
   */
  public function detach_channel(Request $request)
  {
    $game = Game::find($request->gid);
    // DB::table('params')->where('gc_id','=',$)->delete();
    $game->channels()->detach($request->cid);
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 绑定渠道
   * @param $request
   * @return: json
   */
  public function game_has_channel(Request $request)
  {
    $game = Game::find($request->id);
    $channel = Channel::find($request->cid);
    if (!$this->validateGameChannel($request->id, $request->cid)) {
      $path = '../../MolePackageTool-Android/channels/' . $channel->alias . '/configs/' . $game['game_id'] . '/assets';
      Upload::mkdir_dir($path);
      Upload::touch_file($path . '/channel.txt', 0);
      $game->channels()->attach($request->cid);
    }
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 创建文件
   * @param {type} 
   * @return: 
   */
  public function mkdir_file($game, $alias, $cid)
  { }
  /**
   * @description: error返回
   * @param {type} 
   * @return: 
   */
  public function errorReturn($code, $request)
  {
    return response()->json([
      'code' => '402',
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 检测唯一
   * @param $request
   * @return: json
   */
  protected function validateGameChannel($gid, $cid)
  {
    $model = Channel::whereHas('games', function ($query) use ($gid) {
      return $query->where('games.id', '=', $gid);
    })->find($cid);
    return $model;
  }
  /**
   * @description: 检测别名是否唯一
   * @param $request
   * @return: json
   */
  protected function validateGameChannelAlias($alias)
  {
    $model = GameHasChannel::where('alias', '=', $alias)->first();
    return $model;
  }
  /**
   * @description: 获取游戏绑定的渠道
   * @param $request
   * @return: json
   */
  public function get_game_channel(Request $request)
  {
    $game_id = $request->id;
    $model = Channel::whereHas('games', function ($query) use ($game_id) {
      return $query->where('games.id', '=', $game_id);
    })->leftJoin('game_has_channels', 'game_has_channels.channel_id', '=', 'channels.id')
      ->where('game_has_channels.game_id', '=', $game_id)
      ->select('channels.*', 'game_has_channels.id as gc_id', 'game_has_channels.signature_id as signId')
      ->get();
    foreach ($model as $key => $item) {
      $data = ChannelVersion::where('gc_id', '=', $item['gc_id'])->orderBy('updated_at', 'desc')->first();
      $model[$key]['compile'] = false;
      $model[$key]['gameSettings'] = false;
      if ($data['compile'] == 1) {
        $model[$key]['compile'] = true;
      }
      $model[$key]['channel_version'] = $data['val'];
      if ($data['gameSettings'] == 1) {
        $model[$key]['gameSettings'] = true;
      }
    }
    return response()->json([
      'data' => $model
    ]);
  }
  /**
   * @description: 获取游戏版本列表
   * @param {type} 
   * @return: 
   */
  public function view_game_verify(Request $request)
  {
    $data = GameVersion::where('game_id', '=', $request->id)->get();
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 添加游戏版本
   * @param {type} 
   * @return: 
   */
  public function add_game_version(Request $request)
  {
    $model = $request->all();
    $model['compile'] = 1;
    $model['abstract'] = '游戏版本';
    // $game = Game::find($request->game_id);
    $sql = new GameVersion;
    $this->created($model, 'game_versions', $sql);
    $data = GameVersion::where('game_id', '=', $request->game_id)->get();
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 删除绑定版本
   * @param {type} 
   * @return: 
   */
  public function delete_game_version(Request $request)
  {
    $sql = GameVersion::find($request->id);
    $sql->delete();
    $game = Game::find($sql->game_id);
    $path = $this->get_dir() . '/MolePackageTool-Android/games/' . $game->game_id . '/' . $sql->version;
    Log::debug([
      'path' => $path
    ]);
    Common::deleteDir($path);
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 获取渠道下的ID
   * @param {type} 
   * @return: 
   */
  public function get_channel_version(Request $request)
  {
    $gameChannel = GameHasChannel::where('game_id', '=', $request->gid)
      ->where('channel_id', '=', $request->cid)
      ->first();
    $data = ChannelVersion::where('gc_id', '=', $gameChannel->id)->select('channel_versions.val as value')->get();
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  /**
   * @description: 更新配置文件
   * @param {type} 
   * @return: 
   */
  public function update_compile(Request $request)
  {
    $model = $request->data;
    $this->return_json($model, $this->get_dir());
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ]);
  }
  public function get_dir()
  {
    $path = dirname(dirname($_SERVER['DOCUMENT_ROOT']));
    $path_arr = explode('\\', $path);
    $path_dir = implode('/', $path_arr);
    return $path_dir;
  }
  public function return_json($model, $path_dir)
  {
    foreach ($model as $key => $data) {
      $dat = GameHasChannel::leftJoin('games', 'games.id', '=', 'game_has_channels.game_id')
        ->leftJoin('channels', 'channels.id', '=', 'game_has_channels.channel_id')
        ->select('channels.alias as channelName', 'games.game_id as game_id')
        ->find($data['gc_id']);
      $compile = Param::leftJoin('channel_keys', 'channel_keys.id', '=', 'params.ck_id')
        ->leftJoin('game_has_channels', 'game_has_channels.id', '=', 'channel_keys.cid')
        ->where('game_has_channels.id', '=', $data['gc_id'])
        ->where('compile', '=', '1')
        ->get();
      $sql = GameHasChannel::find($data['gc_id']);
      $sql->signature_id = $data['signId'];
      $sql->save();
      $sign = Signature::find($data['signId']);
      $sign_path = $path_dir . $sign->file;
      $json = [];
      $json['keystore'] = $sign_path;
      $json['password'] = $sign->password;
      $json['alias'] = $sign->alias;
      $json['alias_password'] = $sign->alias_password;
      $compile = json_decode(json_encode($compile), true);
      foreach ($compile as $c) {
        $json[$c['key']] = $c['val'];
      }
      $path = '../../MolePackageTool-Android/channels/' . $dat['channelName'] . '/configs/' . $dat['game_id'] . '/package.json';
      Upload::touch_file($path, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      return true;
    }
    return true;
  }
  /**
   * @description: 上传apk文件 
   * @param {type} 
   * @return: 
   */
  public function upload_apk(Request $request)
  {
    $game = Game::find($request->game_id);
    if ($game) {
      Upload::mkdir_dir('../../MolePackageTool-Android/games/' . $game->game_id . '/' . $request->version);
      $json = Upload::upload_apk($request->file, '../../MolePackageTool-Android/games/' . $game->game_id . '/' . $request->version, '../../MolePackageTool-Android/config/keystores');
    }
    return response()->json([
      'path' => $json['path'],
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
  public function exec_bat(Request $request)
  {
    $task_id = date('Ymd') . uniqid('t');
    $path = dirname(dirname($_SERVER['DOCUMENT_ROOT']));
    $game = Game::find($request->game_id);
    foreach ($request->gameInfoArr as $gItem) {
      foreach ($request->channelArr as $cItem) {
        $str = "game=" . $game->game_id . "+gameVersion=" . $gItem['version'] . "+channelName=" . $cItem['alias'] . "+channelVersion=" . $cItem['channel_version'];
        $data = [
          'str' => $str,
          'game_id' => $game->game_id,
          'game_name' => $game->name,
          'channel_name' => $cItem['alias'],
          'path' => $path,
          'version' => $gItem['version'] . $cItem['channel_version']
        ];
        $this->save_task($data, $game, $gItem, $cItem, $task_id);
        $job = (new ExecJob($data));
        $this->dispatch($job);
      }
    }
    return response()->json([
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 保存操作数据
   * @param {type} 
   * @return: 
   */
  public function save_task($data, $game, $gItem, $cItem, $task_id)
  {
    $task = new Task;
    $task->game_id = $game->id;
    $task->game_version_id = $gItem['id'];
    $task->channel_id = $cItem['id'];
    $task->channel_version_id = $cItem['id'];
    $task->system = 'Android';
    $task->task_id = $task_id;
    $task->signature_id = $cItem['signId'];
    $task->state = '0';
    $task->userId = $this->get_userId();
    $task->download_url = 'apk/' . $data['game_id'] . '/' . $data['channel_name'] . '/' . $data['version'] . '/' . 'output.apk';
    $task->save();
    return true;
  }
  /**
   * @description: 获取任务列表
   * @param {type} 
   * @return: 
   */
  public function get_tasks(Request $request)
  {
    $data = Task::leftJoin('games', 'games.id', '=', 'tasks.game_id')
      ->leftJoin('game_versions', 'game_versions.id', '=', 'tasks.game_version_id')
      ->leftJoin('channels', 'channels.id', '=', 'tasks.channel_id')
      ->leftJoin('channel_versions', 'channel_versions.id', '=', 'tasks.channel_version_id')
      ->leftJoin('signatures', 'signatures.id', '=', 'tasks.signature_id')
      ->where('tasks.userId','=',$this->get_userId())
      ->select('tasks.*', 'games.name as game_name', 'games.game_id as game_id', 'game_versions.version', 'channel_versions.val as channel_version', 'channels.alias', 'signatures.name as sign_name')
      ->get();
    return response()->json([
      'data' => $data,
      'access_token' => $request->token,
      'token_type' => 'bearer',
      'expires_in' => auth('api')->factory()->getTTL() * 60
    ], 200);
  }
  /**
   * @description: 下载游戏安装包
   * @param {type} 
   * @return: 
   */
  public function download_apk(Request $request)
  {
    $task = Task::find($request->id);
    // return response()->download($task['download_url'], $request->game_name . '.zip');

    header("Content-type:text/html;charset=utf-8");
    $file_name = $request->game_name.'.apk';
    $file_path = $task['download_url'];

    //首先要判断给定的文件存在与否
    if (!file_exists($file_path)) {
      echo "没有该文件文件";
      return;
    }
    $fp = fopen($file_path, "r");
    $file_size = filesize($file_path);

    //下载文件需要用到的头
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length:" . $file_size);
    Header("Content-Disposition: attachment; filename=" . $file_name);
    $buffer = 1024;
    $file_count = 0;
    //向浏览器返回数据
    while (!feof($fp) && $file_count < $file_size) {
      $file_con = fread($fp, $buffer);
      $file_count += $buffer;
      echo $file_con;
    }
    fclose($fp);
  }
}
