<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;

class Upload
    {
        public static function upload_label($tmp)
        {
            $path = '/Upload/label/'; 
            if ($tmp->isValid()) { 
                $FileType = $tmp->getClientOriginalExtension(); 

                $FilePath = $tmp->getRealPath(); 

                $FileName = date('Y-m-d') . uniqid() . '.' . $FileType; 

                Storage::disk('label')->put($FileName, file_get_contents($FilePath)); //存储文件
                // storage_path('label');
                return $data = [
                    'status' => 0,
                    // 'path' => Storage::disk('label')->get($FileName),
                    'path'=>$path.$FileName
                ];
            }
        }
        public static function upload_file($tmp,$path = '', $returnPath){
            // $paths = '/Upload/label/'; 
            if ($tmp->isValid()) { 
                $FileType = $tmp->getClientOriginalExtension(); 
                $FilePath = $tmp->getRealPath(); 
                $FileName = date('Y-m-d') . uniqid() . '.' . $FileType; 
                // move_uploaded_file($FilePath,'D:\\wwwroot\\file\\'.$FileName);
                move_uploaded_file($FilePath,$path.'/'.$FileName);
                return [
                    'status' => 0,
                    'path' => $returnPath.'/'.$FileName
                ];
            }
        } 
        public static function upload_apk($tmp, $path = '', $returnPath){
          if ($tmp->isValid()) { 
            $FileType = $tmp->getClientOriginalExtension(); 
            $FilePath = $tmp->getRealPath(); 
            $FileName = 'game.' . $FileType; 
            // move_uploaded_file($FilePath,'D:\\wwwroot\\file\\'.$FileName);
            move_uploaded_file($FilePath,$path.'/'.$FileName);
            return [
                'status' => 0,
                'path' => $returnPath.'/'.$FileName
            ];
        }
        }
        //创建文件夹
        public static function mkdir_dir($path = ''){
          $dir = iconv("UTF-8", "GBK", $path);
          if (!file_exists($dir)){
            mkdir ($dir,0777,true);
            return [
              'code' => 1,
              'path' => $path
            ];
          } else {
            return [
              'code' => 0
            ];
          }
        }
        //新建文件
        public static function touch_file($path = '',$StrConents = ''){
          header("content-type:text/html;charset=utf-8");
          if(!file_exists($path)){
              if($fp = fopen($path,'w')){
                fwrite($fp,$StrConents);
                fclose($fp);
                return [
                  'code' => 1,
                  'path' => $path
                ];
              }else{
                return [
                  'code' => 0,
                  'path' => $path
                ];
              }
          }else{
            $fp = fopen($path,'w');
            fwrite($fp,$StrConents);
            fclose($fp);
            return [
              'code' => 0,
              'path' => $path
            ];
          }
        }
    }
