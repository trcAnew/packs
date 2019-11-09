<?php

namespace App\Jobs;

use App\Events\ExampleEvent;
use App\Events\News;
use App\Utils\Common;
use App\Utils\Upload;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

class ExecJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  public $timeout = 300;
  public $data;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($data)
  {
    //
    $this->data = $data;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    if($this->attempts() > 3){
      Log::debug([
        'info'=>'队列任务异常'
      ]);
    }else{
      chdir($this->data['path']."\\MolePackageTool-Android");
      exec($this->data['path'].'\\MolePackageTool-Android\\package.bat "'.$this->data['str'].'"',$opt);
      Log::debug([
        'info'=>$opt
      ]);
      Upload::mkdir_dir($this->data['path'].'\\packs\\public\\apk\\'.$this->data['game_id'].'\\'.$this->data['channel_name'].'\\'.$this->data['version']);
      $state = Common::copy_file($this->data['path'].'\\MolePackageTool-Android\\workspace\\'.$this->data['game_id'].'\\'.$this->data['channel_name'].'\\output.apk',$this->data['path'].'\\packs\\public\\apk\\'.$this->data['game_id'].'\\'.$this->data['channel_name'].'\\'.$this->data['version'].'\\output.apk');
      event(new News($this->data['uid']));
      Log::debug([
        'info'=>$state
      ]);
    }
    // if($state){
      // $ref = Common::en_zip($this->data['path'].'\\packs\\public\\apk\\'.$this->data['game_name'].'\\'.$this->data['channel_name'].'\\'.$this->data['version'].'\\'.$this->data['game_id'].'.zip',$this->data['path'].'\\packs\\public\\apk\\'.$this->data['game_id'].'\\'.$this->data['channel_name'].'\\'.$this->data['version'].'\\output.apk');
      // Log::debug([
      //   'info'=>$ref
      // ]);
    // }
  }
}
