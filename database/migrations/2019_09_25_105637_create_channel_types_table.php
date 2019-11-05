<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('cid')->comment('渠道ID');
            $table->unsignedBigInteger('tid')->comment('平台ID');
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
        Schema::dropIfExists('channel_types');
    }
}
