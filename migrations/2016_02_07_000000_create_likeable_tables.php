<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLikeableTables extends Migration {
    public function up() {
        Schema::create('likeable_likes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('likeable_id', 36);
            $table->string('likeable_type', 255);
            $table->string('user_id')->index();
            $table->boolean('session_like');
            $table->timestamps();
            $table->unique(['likeable_id', 'likeable_type', 'user_id', 'session_like'], 'likeable_likes_unique');
        });
    }

    public function down() {
        Schema::drop('likeable_likes');
    }
}
