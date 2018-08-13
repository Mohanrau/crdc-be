<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('from');
            $table->string('to');
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('channel');
            $table->string('subject');
            $table->text('body');
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
        Schema::dropIfExists('notification_tracking');
    }
}
