<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel_id');
            $table->string('video_id')->nullable();
            $table->tinyInteger('subscribes')->default(0);
            $table->unsignedInteger('subscribes_quantity')->nullable();
            $table->unsignedInteger('likes_quantity')->nullable();
            $table->unsignedInteger('unlikes_quantity')->nullable();
            $table->unsignedInteger('comments_quantity')->nullable();
            $table->unsignedInteger('old_subscribers_count')->nullable();
            $table->unsignedInteger('old_likes_count')->nullable();
            $table->unsignedInteger('old_unlikes_count')->nullable();
            $table->unsignedInteger('new_subscribers_count')->nullable();
            $table->unsignedInteger('new_likes_count')->nullable();
            $table->unsignedInteger('new_unlikes_count')->nullable();
            $table->unsignedInteger('comments_count')->nullable();
            $table->tinyInteger('likes')->default(0);
            $table->tinyInteger('unlikes')->default(0);
            $table->tinyInteger('comments')->default(0);
            $table->text('subscribe_errors')->nullable();
            $table->text('like_errors')->nullable();
            $table->text('unlike_errors')->nullable();
            $table->text('comment_errors')->nullable();
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
        Schema::dropIfExists('histories');
    }
}
