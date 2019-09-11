<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',121)->nullable(true)->comment('秘钥名称');
            $table->string('password',121)->nullable(true)->comment('秘钥密码');
            $table->string('alias',100)->nullable(true)->comment('秘钥别名');
            $table->string('file',100)->nullable(true)->comment('秘钥文件');
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
        Schema::dropIfExists('signatures');
    }
}
