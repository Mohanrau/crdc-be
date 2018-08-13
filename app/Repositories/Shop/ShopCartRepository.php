<?php
namespace App\Repositories\Shop;

use App\Repositories\BaseRepository;
use App\Models\{
    Shop\Cart,
    Products\Product,
    Products\ProductPrice,
    Kitting\Kitting,
    Kitting\KittingPrice,
    Masters\MasterData
};
use App\Helpers\{
    Traits\ResourceRepository,
    Classes\UserIdentifier,
    ValueObjects\SaleProductKitting
};
use App\Interfaces\{
    Shop\ShopCartInterface,
    Sales\SaleInterface,
    Sales\SaleProductKittingInterface
};

class ShopCartRepository extends BaseRepository implements ShopCartInterface, SaleProductKittingInterface
{
    use ResourceRepository {
        create as baseCreate;
    }

    private
        $productAndKitting,
        $transactionTypeConfigCodes,
        $saleRepository,
        $saleTypeCvSettings,
        $numberFormatter,
        $items
    ;

    /**
     * ShopCartRepository constructor.
     * @param Cart $model
     * @param ShopProductAndKittingFilteringRepository $productAndKitting
     * @param SaleInterface $salesRepository
     */
    public function __construct(
        Cart $model,
        ShopProductAndKittingFilteringRepository $productAndKitting,
        SaleInterface $salesRepository
    )
    {
        parent::__construct($model);

        $this->productAndKitting = $productAndKitting;

        $this->saleRepository = $salesRepository;

        $this->transactionTypeConfigCodes = config('mappings.sale_types');

        $this->saleTypeCvSettings = config('setting.sale-type-cvs');

        $this->numberFormatter = new \NumberFormatter( 'en-US', \NumberFormatter::DECIMAL);

        $this->numberFormatter->setAttribute($this->numberFormatter::FRACTION_DIGITS, 2);
    }

    /**
     * @param int $id
     * @return mixed|void
     */
    public function find(int $id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Add a product to the cart
     *
     * If the given product quantity is not defined, it will set 1 as quantity
     * if the quantity is set to zero, the cart has no point of holding the product
     *
     * @param array $data
     * @return mixed
     */
    function create (array $data) {
        $product = $this->modelObj->getSingleCartItem(
            $data['user_identifier'],
            $data['country_id'],
            $data['location_id'],
            $data['product_id'] ?? null,
            $data['kitting_id'] ?? null,
            $data['order_for_user_id'] ?? null
        );

        if ($product) {
            return $this->modelObj->addItemQuantity($product, $data['quantity'] ?? 1);
        } else {
            if (isset($data['quantity']) && $data['quantity'] === 0 ) { // don't update if the quentity is set to 0
                return [];
            } else {
                $data['user_identifier_model'] = $data['user_identifier']->modelTable;

                $data['user_identifier'] = $data['user_identifier']->identifier;

                return $this->baseCreate(array_merge(["quantity" => 1], $data));
            }
        }
    }

    /**
     * Clear user cart
     *
     * @param UserIdentifier $userIdentifier
     * @return mixed
     */
    function userCartClear(UserIdentifier $userIdentifier) {
        if ($items = $this->modelObj->getUserCartItems($userIdentifier)) {
            return Cart::destroy($items->pluck('id')->toArray());
        } else {
            return 0;
        }
    }

    /**
     * Get the cart items and calculates total price, cv and amp cv
     *
     * @param UserIdentifier $userIdentifier
     * @param int $countryId
     * @param int $locationId
     * @param int|null $orderForUserId
     * @return mixed
     * @throws \Exception
     */
    function userCartItems(
        UserIdentifier $userIdentifier,
        int $countryId,
        int $locationId,
        ?int $orderForUserId = null
    )
    {
        $items = $this->modelObj->getUserCartItems($userIdentifier, $countryId, $locationId, $orderForUserId);

        $productIds = $items->pluck('product_id');

        $kittingIds = $items->pluck('kitting_id');

        $productsAndKitting = $this->productAndKitting
            ->getProductsAndKittingsByIds($countryId, $locationId, $productIds->toArray(), $kittingIds->toArray());

        $this->items = clone $productsAndKitting;

        $this->items = $this->items->toArray();

        $productsAndKitting = $productsAndKitting
            ->calculated()
            ->refine()
            ->toArray();

        $unPurchasableProducts = [];

        // Set item quantity
        $productsAndKitting['data'] = collect($productsAndKitting['data']->map(function ($object, $key) use ($items, &$unPurchasableProducts) {
            if ($object['product_id'] !== "") {
                $item = $items->where('product_id', $object['product_id'])->first();
            } else {
                $item = $items->where('kitting_id', $object['kitting_id'])->first();
            }

            $this->items['data'][$key]['cart_item'] = $item;

            // check if the sales type used when the item was added to the cart is still associated with the item
            $eligibleType = array_filter($object["sales_types"],  function ($type) use ($item) {
                return $type['id'] == $item->sales_type_id;
            });

            if (!empty($eligibleType)) {
                $object['eligible_sales_type'] = reset($eligibleType);

                $object['quantity'] = $item->quantity;

                $object['total_price'] = $object['price'] * $item->quantity;

                $this->formatHasPrefix($object, 'total_price');

                // filter breakdowns based on eligible sales type cv types
                $cvsToCalculate = [
                    'break_down' => collect($object['unit_cv']['break_down'])
                        ->filter(function ($value, $key) use ($object) {
                            return in_array($key, $this->saleTypeCvSettings[$object['eligible_sales_type']['key']]);
                        })->all()
                ];

                if (isset($object['unit_cv']['sales_types'][$object['eligible_sales_type']['key']])) {
                    $cvsToCalculate['sales_types'][$object['eligible_sales_type']['key']] = $object['unit_cv']['sales_types'][$object['eligible_sales_type']['key']];
                }

                $this->calculateTotals($object, $cvsToCalculate, $item->quantity);

                $this->calculateTotals($object, $object['prices'], $item->quantity, 'total_prices');

                if (isset($object['total_cv']) && isset($object['total_cv']['sales_types'][$object['eligible_sales_type']['key']])) {
                    $object['total_cv'] = array_merge(
                        $object['total_cv'],
                        $object['total_cv']['sales_types'][$object['eligible_sales_type']['key']]
                    );
                }
            } else {
                $unPurchasableProducts[] = $key;
            }
            return $object;
        }));

        // remove the items from the collection where the sales type used when adding to the cart is no longer attached
        // to the item
        $productsAndKitting['data'] = $productsAndKitting['data']->forget($unPurchasableProducts);

        $this->items['data'] = collect($this->items['data'])->forget($unPurchasableProducts);

        $productsAndKitting['total_cv'] = $productsAndKitting['data']->reduce(function ($carry, $item) {
            if (isset($item['total_cv'])) {
                $this->calculateTotals($carry, $item['total_cv'], 1);
            }

            return $carry;
        }, []);

        $productsAndKitting['total_cv'] = (object) (
            isset($productsAndKitting['total_cv']['total_cv'])
            ? $productsAndKitting['total_cv']['total_cv']
            : []
        );

        $productsAndKitting['total_prices'] = $productsAndKitting['data']->reduce(function ($carry, $item) {
            if (isset($item['total_prices'])) {
                $this->calculateTotals($carry, $item['total_prices'], 1, 'total_prices');
            }

            return $carry;
        }, []);

        if (isset($productsAndKitting['total_prices']['total_prices'])) {
            $productsAndKitting['total_prices'] = $productsAndKitting['total_prices']['total_prices'];
        }

        $productsAndKitting['total_price'] =  $productsAndKitting['data']->sum( function($item)
            {
                if(isset($item['total_price'])) {
                    return $item['total_price'];
                }
            });

        $productsAndKitting['total_admin_fee'] = 0; // TODO: To Be Implemented

        $productsAndKitting['total_delivery_fee'] = 0; // TODO: To Be Implemented

        $productsAndKitting['total_tax'] = (
            isset($productsAndKitting['total_prices']['nmp_price']) &&
            isset($productsAndKitting['total_prices']['gmp_price_tax'])
        )
            ? (float) number_format($productsAndKitting['total_prices']['gmp_price_tax'] - $productsAndKitting['total_prices']['nmp_price'], 2, ".", "")
            : 0
        ;

        $productsAndKitting['total_npm'] = $productsAndKitting['total_prices']['nmp_price']
                                           ?? $productsAndKitting['total_prices']['gmp_price_tax']
                                              ?? 0;

        // Calculate rounding value
        $roundingAdjustment =  (float) $this->saleRepository
            ->roundingAdjustment(
                $countryId,
                $productsAndKitting['total_price'] ?? 0
            );

        $productsAndKitting['total_price_rounding_adjustment'] = (float) number_format($roundingAdjustment - $productsAndKitting['total_price'], 2, ".", "");

        $productsAndKitting['total_price'] = $roundingAdjustment;

        $this->formatHasPrefix($productsAndKitting, 'total_');

        $productsAndKitting['total_quantity'] = $productsAndKitting['data']->sum('quantity');

        $productsAndKitting['total_prices'] = (object) $productsAndKitting['total_prices'];

        return $productsAndKitting;
    }

    /**
     * Formats a given array that matches the $prefix of the key
     *
     * @param array $object Array to loop through
     * @param $prefix Prefix in key to look for to format
     */
    private function formatHasPrefix (array &$object, $prefix) {
        foreach ($object as $key => $value) {
            if (substr($key, 0, strlen($prefix)) === $prefix && is_numeric($value)) {
                $object[rtrim($key, '_') . '_formatted'] = $this->numberFormatter->format($value);
            }
        }
    }

    /**
     * Calculates total cv and updates the referred object
     *
     * @param array $object
     * @param array $totals
     * @param int $quantity
     * @param $key
     */
    private function calculateTotals (array &$object, array $totals, int $quantity, $key = 'total_cv') {
        foreach($totals as $name => $value) {
            if (is_array($value)){  // calculate total cv for sales types
                if (!isset($object[$key][$name])) {
                    $object[$key][$name] = [];
                }

                $this->calculateTotals($object[$key], $value, $quantity, $name);
            } else { // calculate for basic, amp and registration
                if (!isset($object[$key][$name])) {
                    $object[$key][$name] = 0;
                }

                $object[$key][$name] += ($value * $quantity);
            }
        }
    }

    /**
     * Get Products
     *
     * @return \App\Helpers\ValueObjects\SaleProductKitting[]
     */
    public function getProducts ()
    {
        return collect($this->items['data'])
            ->filter(function ($item) {
                return !empty($item['product_id']);
            })
            ->map(function ($item) {
                $product = new Product;

                $product->fill($item);

                // hydrate general settings
                $product->productGeneralSetting = $product->productGeneralSetting()
                                                          ->hydrate($item['general_settings']);

                // hydrate prices
                $product->productPrices = $product->productPrices()
                                                  ->hydrate($item['prices']);

                $productPrice = new ProductPrice;

                $productPrice->fill($item['prices'][0]);

                $saleType = new MasterData;

                $saleType->id = $item['cart_item']['sales_type_id'];

                return new SaleProductKitting($product, $productPrice, $saleType, $item['cart_item']['quantity']);
            })
            ->all();
    }

    /**
     * Get Kitting
     *
     * @return \App\Helpers\ValueObjects\SaleProductKitting[]
     */
    public function getKitting ()
    {
        return collect($this->items['data'])
            ->filter(function ($item) {
                return !empty($item['kitting_id']);
            })
            ->map(function ($item) {
                $kitting = new Kitting;

                $kitting->fill($item);

                // hydrate general settings
                $kitting->kittingGeneralSetting = $kitting->kittingGeneralSetting()
                                                          ->hydrate($item['general_settings']);

                // hydrate prices
                $kitting->kittingPrice = $kitting->kittingPrice()
                                                 ->hydrate($item['prices'])
                                                 ->first();

                $kittingPrice = new KittingPrice;

                $kittingPrice->fill($item['prices'][0]);

                $masterData = new MasterData;

                $masterData->id = $item['cart_item']['sales_type_id'];

                return new SaleProductKitting($kitting, $kittingPrice, $masterData, $item['cart_item']['quantity']);
            })
            ->all();
    }
}