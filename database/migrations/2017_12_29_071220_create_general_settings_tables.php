<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralSettingsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_keys', function (Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('key');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('setting_key_id');
            $table->longText('value');
            $table->unsignedInteger('mapping_id')->nullable();
            $table->string('mapping_model')->nullable();

            $table->boolean('active')->default(1);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('setting_key_id')
                ->references('id')
                ->on('setting_keys')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');

        Schema::dropIfExists('setting_keys');
    }
}
