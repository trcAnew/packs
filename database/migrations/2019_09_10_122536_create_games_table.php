<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',100)->comment('游戏名称');
            $table->string('alias',50)->comment('游戏简称');
            $table->string('game_id',50)->comment('游戏id');
            $table->string('game_key',200)->comment('游戏KEY');
            $table->string('product_name',100)->nullable(true)->comment('产品名称');
            $table->string('sdk_server',100)->comment('sdk服务器');
            $table->string('sdk_ios',50)->nullable(true)->comment('ios sdk 版本');
            $table->string('unity_version',50)->nullable(true)->comment('Unity3D版本');
            $table->string('sdk_android',50)->comment('安卓版本');
            $table->unsignedBigInteger('android_sign')->comment('Android默认签名');
            $table->unsignedBigInteger('unity_enc')->comment('unity 加密');
            $table->string('icon',50)->comment('游戏图标');
            $table->unsignedBigInteger('state')->comment('状态');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
