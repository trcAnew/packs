<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('game_id')->comment('游戏id');
            $table->unsignedBigInteger('game_verify_id')->comment('项目版本id');
            $table->unsignedBigInteger('channel_id')->comment('渠道name');
            $table->unsignedBigInteger('channel_version_id')->comment('渠道版本');
            $table->unsignedBigInteger('signature_id')->comment('签名ID');
            $table->string('system',200)->comment('系统版本');
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
        Schema::dropIfExists('tasks');
    }
}
