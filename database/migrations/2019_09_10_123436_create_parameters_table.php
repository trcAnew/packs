<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('cid')->comment('渠道id');
            $table->string('keys',100)->comment('渠道id');
            $table->string('abstract',200)->nullable(true)->comment('参数说明');
            $table->string('val',200)->comment('参数值');
            $table->unsignedBigInteger('typeId')->comment('参数类型');
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
        Schema::dropIfExists('parameters');
    }
}
