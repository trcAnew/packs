<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('game_id')->comment('游戏id');
            $table->unsignedInteger('channel_id')->comment('渠道ID');
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
        Schema::dropIfExists('game_channels');
    }
}
