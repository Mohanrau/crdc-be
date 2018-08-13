<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserOtpsTableAddColumnsForAdditionalData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_otps', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->string('contact')->nullable()->after('user_id');
            $table->integer('tries')->default(0)->after('code_type_id');
            $table->integer('send_count')->default(0)->after('tries');
            $table->integer('verified')->default(0)->after('send_count');
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
            $table->dropColumn([
                'contact',
                'tries',
                'send_count',
                'verified'
            ]);
        });
    }
}
