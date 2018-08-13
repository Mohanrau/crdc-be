<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMobileVerificationsAddSendCountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_verifications', function (Blueprint $table) {
            $table->integer('send_count')->default(0)->after('tries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mobile_verifications', function (Blueprint $table) {
            $table->dropColumn('send_count');
        });
    }
}
