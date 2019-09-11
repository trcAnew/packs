<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform','20')->comment('平台');
            $table->string('name','200')->comment('渠道名称');
            $table->string('code','200')->comment('渠道代码');
            $table->string('max_verify','100')->nullable(true)->comment('最高支持的版本');
            $table->string('label','100')->nullable(true)->comment('角标');
            $table->string('sdk_verify','255')->nullable(true)->comment('渠道sdk版本|,隔开');
            $table->unsignedBigInteger('state')->nullable(true)->comment('状态');
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
        Schema::dropIfExists('channels');
    }
}
