<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_types', function (Blueprint $table){
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_type_id')->unsigned()->default(3);
            $table->string('name');
            $table->string('unique_login_id',50)->nullable();
            $table->string('mobile',20)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('active')->default(1);
            $table->integer('created_by')->unsigned()->default(0);
            $table->integer('update_by')->unsigned()->default(0);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('user_type_id')
                ->references('id')
                ->on('user_types')
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
        Schema::dropIfExists('users');

        Schema::dropIfExists('user_types');
    }
}
