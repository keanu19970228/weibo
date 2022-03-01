<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');// 自增id
            $table->text('content');// 微博内容
            $table->integer('user_id')->index();// integer 类型的 user_id 字段,->index() 为该字段加上索引
            $table->index(['created_at']);// 为微博的创建时间添加索引
            $table->timestamps();// timestamps 方法会为微博数据表生成一个微博创建时间字段 created_at 和一个微博更新时间字段 updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
