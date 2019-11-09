<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Common
{
  public static function deleteDir($path)
  {
    if (is_dir($path)) {
      //扫描一个目录内的所有目录和文件并返回数组
      $dirs = scandir($path);

      foreach ($dirs as $dir) {
        //排除目录中的当前目录(.)和上一级目录(..)
        if ($dir != '.' && $dir != '..') {
          //如果是目录则递归子目录，继续操作
          $sonDir = $path . '/' . $dir;
          if (is_dir($sonDir)) {
            //递归删除
            self::deleteDir($sonDir);

            //目录内的子目录和文件删除后删除空目录
            @rmdir($sonDir);
          } else {

            //如果是文件直接删除
            @unlink($sonDir);
          }
        }
      }
      @rmdir($path);
    }
  }
  /**
   * @description: 将文件打包成zip
   * @param {type} 
   * @return: 
   */
  public static function en_zip($name, $path)
  {
    $zip = new ZipArchive;
    // dd($zip->open($name, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE));
    if ($zip->open($name, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE) === TRUE) {
      Log::debug([
        'path' => $path
      ]);
      $zip->addFile($path);
      $zip->close();
      return true;
    }
    return false;
  }
  /**
   * @description: copy 文件
   * @param {type} 
   * @return: 
   */
  public static function copy_file($path, $toPath)
  {
    $state = copy($path, $toPath);
    return $state;
  }
  /**
   * @description: 读取文件内容
   * @param {type} 
   * @return: json
   */
  public static function open_file($path)
  { 
    $myfile = fopen($path,'r');
    $json = fread($myfile,filesize($path));
    fclose($myfile);
    return $json;
  }
}
