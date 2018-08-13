<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserOtpsTableAddCodeTypeIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_otps', function (Blueprint $table) {
            $table->unsignedInteger('code_type_id')->nullable()->after('code');

            $table->foreign('code_type_id')
                ->references('id')
                ->on('master_data')
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
        Schema::table('user_otps', function (Blueprint $table) {
            $table->dropForeign('user_otps_code_type_id_foreign');

            $table->dropColumn('code_type_id');
        });
    }
}
