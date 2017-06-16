<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFavoritesTables extends Migration {
    public function up() {
        Schema::create('favorites_likes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('likeable_id');
            $table->string('likeable_type', 255);
            $table->string('user_id')->index();
            $table->boolean('session_like');
            $table->timestamps();
            $table->unique(['likeable_id', 'likeable_type', 'user_id', 'session_like'], 'favorites_likes_unique');
        });
    }

    public function down() {
        Schema::drop('favorites_likes');
    }
}
