<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('time')->comment('时间');
            // 存在活动记录的计分项不允许删除
            $table->foreignId('scoring_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('point')->comment('分数');
            $table->string('comment')->nullable()->comment('说明');
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
        Schema::dropIfExists('events');
    }
};
