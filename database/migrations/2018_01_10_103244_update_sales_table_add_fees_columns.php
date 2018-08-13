<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSalesTableAddFeesColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total_gmp')->default('0.00')->after('total_amount');
            $table->decimal('rounding_adjustment')->default('0.00')->after('total_gmp');
            $table->decimal('tax_amount')->default('0.00')->after('rounding_adjustment');
            $table->integer('total_qualified_cv')->default(0)->after('total_cv');
            $table->text('remarks')->nullable()->after('other_fees');

            $table->dropColumn('transaction_number');

            $table->unsignedInteger('delivery_method_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('total_gmp');
            $table->dropColumn('rounding_adjustment');
            $table->dropColumn('tax_amount');
            $table->dropColumn('total_qualified_cv');
            $table->dropColumn('remarks');

            $table->string('transaction_number');
        });
    }
}
