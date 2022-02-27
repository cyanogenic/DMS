<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scorings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('计分项');

            $table->unsignedSmallInteger('point')->comment('分值');
            $table->string('comment')->nullable()->comment('说明');
            
            $table->timestamps();
        });

        $timestamp = date("Y-m-d H:i:s");
        DB::table('scorings')->insert([
            ['name' => '转点香主', 'point' => 3, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '四海BOSS', 'point' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '枭野决战', 'point' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '修罗城', 'point' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '世界BOSS', 'point' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '帮派联赛', 'point' => 20, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '天龙号', 'point' => 5, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => '打架', 'point' => 10, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scorings');
    }
}
