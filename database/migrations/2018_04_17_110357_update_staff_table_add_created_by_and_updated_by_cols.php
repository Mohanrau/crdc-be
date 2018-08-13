<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStaffTableAddCreatedByAndUpdatedByCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_city_id_foreign');

            $table->dropColumn(['city_id','mobile','active']);
            $table->string('position')->nullable()->change();

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();

            $table->foreign('created_by','staff_created_by_foreign')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by', 'staff_updated_by_foreign')
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
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_created_by_foreign');

            $table->dropForeign('staff_updated_by_foreign');

            $table->dropColumn(['created_by','updated_by']);

            $table->string('position')->change();

            $table->string('mobile', 50)->after('country_id');

            $table->boolean('active')->default(1)->after('country_id');

            $table->unsignedInteger('city_id')->nullable()->after('country_id');

            $table->foreign('city_id')
                ->references('id')
                ->on('cities')
                ->onDelete('cascade');
        });
    }
}
