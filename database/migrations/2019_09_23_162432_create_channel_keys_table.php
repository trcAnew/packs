<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key',255)->comment('键值');
            $table->string('abstract',255)->comment('说明');
            $table->unsignedBigInteger('cid')->comment('渠道id');
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
        Schema::dropIfExists('channel_keys');
    }
}
