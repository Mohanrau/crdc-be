<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTableAddOldMemberId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('old_ibs_user_id')->default(0)->after('id');

            //remove user type id from user table
            $table->dropForeign('users_user_type_id_foreign');

            $table->dropColumn('user_type_id');
        });

        Schema::create('user_type', function (Blueprint $table){
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_type_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('user_type_id')
                ->references('id')
                ->on('user_types')
                ->onDelete('cascade');

            $table->primary(['user_id','user_type_id'],'user_type_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('old_ibs_user_id');

            $table->integer('user_type_id')->unsigned()->default(3)->after('id');

            $table->foreign('user_type_id')
                ->references('id')
                ->on('user_types')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('user_type');
    }
}