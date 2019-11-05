<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gc_id')->comment('渠道游戏绑定ID');
            $table->string('val',255)->comment('版本号');
            $table->unsignedBigInteger('compile')->comment('是否保存在pack');
            $table->unsignedBigInteger('gameSettings')->comment('是否保存在gameSettings');
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
        Schema::dropIfExists('channel_versions');
    }
}
