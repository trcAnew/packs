<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api){
    $api->group([], function ($api) {
        $api->post('register','App\Http\Controllers\Auth\RegisterController@register');
        $api->post('login','App\Http\Controllers\Auth\LoginController@login');
        $api->get('download_apk/{data?}','App\Http\Controllers\GameController@download_apk');
        $api->post('receive/post','App\Http\Controllers\ServiceController@receive_post');  
        $api->post('logout','App\Http\Controllers\UserController@logout');     
        // $api->post('/user/info','App\Http\Controllers\LoginController@user_info'); 
    });
    $api->group(['middleware' => 'refresh.token'],function ($api) {
        $api->post('channel_list','App\Http\Controllers\ChannelController@list');
        $api->post('get/powers','App\Http\Controllers\Auth\LoginController@powers');
        $api->post('channel/upload','App\Http\Controllers\ChannelController@upload');
        $api->post('add_channel','App\Http\Controllers\ChannelController@add_channel');
        $api->post('save_param','App\Http\Controllers\ChannelController@save_param');
        $api->post('save_params','App\Http\Controllers\ChannelController@save_params');
        $api->post('get_channel','App\Http\Controllers\ChannelController@get_channel');
        $api->post('view_params','App\Http\Controllers\ChannelController@view_params');
        $api->post('params/list','App\Http\Controllers\ParamsController@list');
        $api->post('game/list','App\Http\Controllers\GameController@get_list');
        $api->post('game/add','App\Http\Controllers\GameController@add');
        $api->post('game/info','App\Http\Controllers\GameController@info');
        $api->post('user/list','App\Http\Controllers\UserController@user_list');
        $api->post('user/roles','App\Http\Controllers\UserController@roles');
        $api->post('user/adds','App\Http\Controllers\UserController@adds');
        $api->post('user/info','App\Http\Controllers\UserController@user_info');
        $api->post('permission/parent','App\Http\Controllers\PermissionController@parent');
        $api->post('permission/add','App\Http\Controllers\PermissionController@add');
        $api->post('permission/list','App\Http\Controllers\PermissionController@list');
        $api->post('permission/info','App\Http\Controllers\PermissionController@info');    
        $api->post('permission/delete','App\Http\Controllers\PermissionController@delete');    
        $api->post('role/add','App\Http\Controllers\RolesController@add');  
        $api->post('role/list','App\Http\Controllers\RolesController@list');  
        $api->post('role/info','App\Http\Controllers\RolesController@info');  
        $api->post('role/view_permission','App\Http\Controllers\RolesController@view_permission');  
        $api->post('get/sign','App\Http\Controllers\GameController@get_sign');  
        $api->post('add/sign','App\Http\Controllers\GameController@user_attach_sign');  
        $api->post('upload/sign_file','App\Http\Controllers\GameController@sign_file');
        $api->post('detach_channel','App\Http\Controllers\GameController@detach_channel');  
        $api->post('detach_sign','App\Http\Controllers\GameController@detach_sign');  
        $api->post('game_has_channel','App\Http\Controllers\GameController@game_has_channel');  
        $api->post('get_game_channel','App\Http\Controllers\GameController@get_game_channel');  
        $api->post('get_channel_params','App\Http\Controllers\ChannelController@get_channel_params');  
        $api->post('channel/upload_save','App\Http\Controllers\ChannelController@upload_save');  
        $api->post('get/params_value','App\Http\Controllers\ChannelController@get_params_value');  
        $api->post('add_game_version','App\Http\Controllers\GameController@add_game_version');  
        $api->post('view_game_verify','App\Http\Controllers\GameController@view_game_verify');  
        $api->post('delete_game_version','App\Http\Controllers\GameController@delete_game_version');  
        $api->post('get_channel_version','App\Http\Controllers\GameController@get_channel_version');  
        $api->post('update_compile','App\Http\Controllers\GameController@update_compile');  
        $api->post('upload_apk','App\Http\Controllers\GameController@upload_apk');
        $api->post('exec_bat','App\Http\Controllers\GameController@exec_bat');
        $api->post('get_tasks','App\Http\Controllers\GameController@get_tasks');
      });
});

