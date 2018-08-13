<?php

use Faker\Generator as Faker;
use App\Models\{
    Sales\Sale,
    Sales\SaleProduct,
    Locations\Country,
    Locations\Entity,
    Locations\Location,
    Members\Member,
    General\CWSchedule,
    Masters\Master,
    Members\MemberTree,
    Products\Product,
    Products\ProductPrice,
    Invoices\Invoice
};

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Sale::class, function (Faker $faker) {

    $salesChannel = collect([1,2,3])->random();

    $letter = collect(['P','E','CN','A','R','E','S',''])->random();

    $country = Country::where('active',1)
        ->get()
        ->random(1);

    $country = $country[0]->id == 10 || $country[0]->id == 9? Country::where('active',1)
        ->get()
        ->random(1) : $country;

    $entity = Entity::where('country_id',$country[0]->id)
        ->get()
        ->random()
        ->id;

    $transactionLocationId = Location::where('entity_id',$entity)
        ->get()
        ->random()
        ->id;

    $random_number = str_pad(rand(0,100000), 10, '0', STR_PAD_LEFT);

    $transactionNumber = $country[0]->code_iso_2.$letter.$salesChannel.$random_number;

    $user = Member::where('country_id',$country[0]->id)
        ->get()
        ->random(1);

    return [
        'transaction_date' => $faker->unique()->dateTimeBetween($startDate = "now", $endDate = "30 days")->format('Y-m-d'),
        'country_id' => $country[0]->id,
        'transaction_location_id' => $transactionLocationId,
        'transaction_number' => $transactionNumber,
        'user_id' => $user[0]->id,
        'cw_id' => CWSchedule::all()
            ->random()
            ->id,
        'total_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'total_cv' => $faker->numberBetween($min = 10, $max = 1000),
        'delivery_method_id' => Master::where('key','sale_delivery_method')
            ->first()
            ->masterData()
            ->get()
            ->random()
            ->id,
        'delivery_status_id' => Master::where('key','sale_delivery_status')
            ->first()
            ->masterData()
            ->get()
            ->random()
            ->id,
        'sponsor_id' => MemberTree::where('sponsor_parent_user_id',$user[0]->id)->first(),
        'channel_id' => Master::where('key','sale_channel')
            ->first()
            ->masterData()
            ->get()
            ->random()
            ->id,
        'order_status_id' => Master::where('key', 'sale_order_status')
            ->first()
            ->masterData()
            ->get()
            ->random()
            ->id
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(SaleProduct::class, function (Faker $faker) {

    $sale = Sale::all()
        ->random(1);

    $product = Product::where('ibs_active',1)
        ->get()
        ->random(1);

    return [
        'sale_id' => $sale[0]->id,
        'product_id' => $product[0]->id,
        'product_price_id' => ProductPrice::where('product_id',$product[0]->id)
            ->get()
            ->random()
            ->id,
        //type_id
        //mapping_id
        'transaction_type_id' => $faker->numberBetween(1,10),
        'quantity' => $faker->numberBetween(1,10),
        'unit_cv' => $faker->numberBetween(1,10000),
        'total_cv' => $faker->numberBetween(1,10000),
        'gmp_price_gst' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'rp_price' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'rp_price_gst' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'nmp_price' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'effective_date' => $faker->unique()->dateTimeBetween($startDate = "-30 days", $endDate = "now")->format('Y-m-d'),
        'expiry_date' => $faker->unique()->dateTimeBetween($startDate = "now", $endDate = "30 days")->format('Y-m-d'),
        'base_cv' => $faker->numberBetween(1,10000),
        'wp_cv' => $faker->numberBetween(1,10000),
        'cv1' => $faker->numberBetween(1,10000),
        'cv2' => $faker->numberBetween(1,10000),
        'welcome_bonus_l1' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'welcome_bonus_l2' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'welcome_bonus_l3' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'welcome_bonus_l4' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'welcome_bonus_l5' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
    ];
});

$factory->define(Invoice::class, function (Faker $faker) {

    $sale = Sale::all()
        ->random(1);

    $invoice = strlen($sale[0]->transaction_number) < 14 ? substr_replace($sale[0]->transaction_number,'INV',0,2) : 'NA';

    $selfCollectCode = substr($sale[0]->transaction_number,2,1) == 'S' ? $sale[0]->transaction_number : 'NA';

    return [
        'sale_id' => $sale[0]->id,
        'invoice_number' => $invoice,
        'document_number' => $sale[0]->transaction_number,
        'invoice_date' => $faker->unique()->dateTimeBetween($startDate = "now", $endDate = "30 days")->format('Y-m-d'),
        'reference_number' => $sale[0]->transaction_number,
        'self_collection_code' => $selfCollectCode,
    ];
});
