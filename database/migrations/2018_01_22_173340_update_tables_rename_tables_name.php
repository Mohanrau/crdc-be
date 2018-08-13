<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTablesRenameTablesName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('sale_payment_mode_providers', 'sales_payments_modes_providers');

        Schema::rename('sale_payment_mode_settings', 'sales_payments_modes_settings');

        Schema::rename('sale_payments', 'sales_payments');

        Schema::rename('sale_product_sizes', 'sales_products_sizes');

        Schema::rename('setting_keys', 'settings_keys');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('sales_payments_modes_providers', 'sale_payment_mode_providers');

        Schema::rename('sales_payments_modes_settings', 'sale_payment_mode_settings');

        Schema::rename('sales_payments', 'sale_payments');

        Schema::rename('sales_products_sizes', 'sale_product_sizes');

        Schema::rename('settings_keys', 'setting_keys');
    }
}
