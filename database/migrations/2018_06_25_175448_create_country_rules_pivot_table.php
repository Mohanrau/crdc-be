<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryRulesPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_master_data', function (Blueprint $table) {
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('master_id');
            $table->unsignedInteger('master_data_id');
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('cascade');

            $table->foreign('master_id')
                ->references('id')
                ->on('masters')
                ->onDelete('cascade');

            $table->foreign('master_data_id')
                ->references('id')
                ->on('master_data')
                ->onDelete('cascade');

            $table->unique(['country_id', 'master_id', 'master_data_id'], 'country_id_master_id_master_data_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_master_data');
    }
}
