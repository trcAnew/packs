<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gc_id')->comment('游戏渠道绑定的ID');
            $table->unsignedBigInteger('ck_id')->comment('对应的keyid');
            $table->string('val',255);
            $table->unsignedBigInteger('compile')->default(0)->comment('是否保存在pack');
            $table->unsignedBigInteger('gameSettings')->default(0)->comment('是否保存在gameSettings');
            $table->unsignedBigInteger('userId')->comment('用户id');
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
        Schema::dropIfExists('params');
    }
}
