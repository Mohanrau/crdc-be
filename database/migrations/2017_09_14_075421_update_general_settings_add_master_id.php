<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateGeneralSettingsAddMasterId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_general_settings', function (Blueprint $table) {
            $table->unsignedInteger('master_id')->after('product_id');

            $table->foreign('master_id')
                ->references('id')
                ->on('masters')
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
        Schema::table('product_general_settings', function (Blueprint $table) {
            $table->dropForeign('product_general_settings_master_id_foreign');

            $table->dropColumn('master_id');
        });
    }
}
