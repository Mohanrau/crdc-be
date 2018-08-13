<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRunningNumberTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('running_number_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->char('code', 20)->unique();
            $table->string('name', 150);
            $table->string('prefix', 100)->nullable();
            $table->string('suffix', 100)->nullable();
            $table->unsignedInteger('begin_number')->default(1);
            $table->unsignedInteger('running_width');
            $table->boolean('active')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('running_number_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('running_number_setting_id');
            $table->string('prefix', 100)->nullable();
            $table->string('suffix', 100)->nullable();
            $table->unsignedInteger('running_no');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('running_number_setting_id')
                ->references('id')
                ->on('running_number_settings')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('running_number_transactions');

        Schema::dropIfExists('running_number_settings');
    }
}
