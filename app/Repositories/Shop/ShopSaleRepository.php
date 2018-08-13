<?php
namespace App\Repositories\Shop;

use App\Helpers\Classes\UserIdentifier;
use App\Services\EShop\ShoppingCartService;
use App\Interfaces\{
    Shop\ShopSaleInterface,
    Shop\ShopCartInterface,
    Sales\SaleInterface,
    Masters\MasterInterface,
    Locations\LocationInterface,
    Users\UserInterface
};
use Illuminate\Support\Facades\{
    Auth,
    Config
};
use App\Exceptions\Shop\{
    EmptyCartException,
    CannotCheckoutException
};

class ShopSaleRepository implements ShopSaleInterface
{
    private
        $saleRepository,
        $shopCartRepository,
        $masterRepository,
        $locationRepository,
        $shoppingCartService,
        $userInterface
    ;

    function __construct (
        SaleInterface $saleRepository,
        ShopCartInterface $shopCartRepository,
        MasterInterface $masterRepository,
        LocationInterface $locationRepository,
        ShoppingCartService $shoppingCartService,
        UserInterface $userInterface
    ) {
        $this->saleRepository = $saleRepository;

        $this->shopCartRepository = $shopCartRepository;

        $this->masterRepository = $masterRepository;

        $this->locationRepository = $locationRepository;

        $this->shoppingCartService = $shoppingCartService;

        $this->userInterface = $userInterface;
    }

    /**
     * Create sales from cart
     *
     * @param UserIdentifier $userIdentifier
     * @param int $locationId
     * @param int $countryId
     * @param array $saleData
     * @return mixed
     * @throws \Exception
     */
    function createCartSale(
        UserIdentifier $userIdentifier,
        int $locationId,
        int $countryId,
        array $saleData
    )
    {
        $user = $this->userInterface->find($saleData['downline_member_id']);

        $userCartItems = $this->shopCartRepository->userCartItems(
            Auth::user()->identifier(),
            $countryId,
            $locationId,
            $user->id
        );

        // Process only if the cart is not empty
        if ($userCartItems['total_quantity'] === 0) {
            throw new EmptyCartException();
        } else {
            $checkout = $this->shoppingCartService
                ->processCheckout(
                    $this->shopCartRepository,
                    $user
                );

            if ($checkout->getStatus() === false) {
                throw new CannotCheckoutException();
            }

            // Get product and kitting from the cart
            $productsAndKitting = collect($userCartItems['data'])
                ->map(function($productOrKitting) {
                    return [
                        "kitting_id" => $productOrKitting["kitting_id"],
                        "product_id" => $productOrKitting["product_id"],
                        "quantity" => $productOrKitting["quantity"],
                        "transaction_type" => $productOrKitting["eligible_sales_type"]["id"]
                    ];
                });

            // Get Product Ids from cart
            $productIds = $productsAndKitting
                ->reduce(function ($products, $product) {
                    if (is_int($product['product_id'])) {
                        $products[] = $product;
                    }
                    return $products;
                }, []);

            // Get Kitting Ids from cart
            $kittingIds = $productsAndKitting
                ->reduce(function ($kittings, $kitting) {
                    if (is_int($kitting['kitting_id'])) {
                        $kittings[] = $kitting;
                    }
                    return $kittings;
                }, []);

            // Get fees from cart
            $fees = [
                "total_nmp" => $userCartItems["total_npm"],
                "tax_amount" => $userCartItems["total_tax"],
                "total_gmp" => $userCartItems["total_price"],
                "rounding_adjustment" => $userCartItems["total_price_rounding_adjustment"]
            ];

            // Get cvs from cart
            $cvs = collect($userCartItems["total_cv"])
                ->filter(function ($value, $key) {
                    return $key === "eligible";
                })
                ->map(function($cv) {
                    return [
                        "total_cv" => $cv,
                        "total_qualified_cv" => $cv

                    ];
                })
                ->first();

            $salesData = array_merge([
                "country_id" => $countryId,
                "location_id" => $locationId,
                "products" => $productIds,
                "kittings" => $kittingIds,
                "remarks" => "",
                "cw_id" => isset($saleData['downline_member_id'])
                    ? $this->saleRepository->currentCwUpgradeCvForUser(Auth::id())['currentCwId']
                    : null,
                "order_fees" => $fees,
                "cvs" => $cvs,
                "status" => "pending-online"
            ], $saleData);

            $this->preProcessSales($salesData);

            return $this->saleRepository->createSale(
                [
                    "sales_data" => $salesData
                ]
            );
        }
    }

    /**
     * Create sales in general
     *
     * @param array $data
     * @param bool $orderCompleteStatus
     * @return mixed
     */
    function createSale (
        array $data,
        bool $orderCompleteStatus = false
    ) {
        $this->preProcessSales($data['sales_data']);

        $sale = $this->saleRepository->createSale($data, $orderCompleteStatus);

        return $sale;
    }

    /**
     * Pre process sales information for shop sales
     *
     * @param array $saleData
     */
    private function preProcessSales (array &$saleData) {
        $saleData['status'] = 'pending-online';

        $this->processLocations($saleData);
    }

    /**
     * Process locations
     *
     * Set the stock_location_id to stockist location id if the delivery method id self pickup
     *
     * @param array $salesData
     */
    private function processLocations (array &$salesData) {
        if (
            isset($salesData['selected']) &&
            isset($salesData['selected']['shipping']) &&
            isset($salesData['selected']['shipping']['sale_delivery_method'])
        ) {
            $deliveryMethodId = $salesData['selected']['shipping']['sale_delivery_method'];

            $shippingMethodMasterData =  $this->masterRepository
                ->getMasterDataByKey(['sale_delivery_method'])
                ->pop()->pluck('id','title')->flip()->get($deliveryMethodId);

            if (strtolower($shippingMethodMasterData) === strtolower(config::get('mappings.sale_delivery_method.pickup'))) {
                // Set stock location based on the self pickup location
                if (isset($salesData['selected']['shipping']['self_collection_point_id'])){
                    if ($stockLocation = $this->locationRepository
                        ->getStockLocationsByLocation($salesData['selected']['shipping']['self_collection_point_id'])
                        ->stockLocations()
                        ->first()) {
                        $salesData['stock_location_id'] = $stockLocation->id;
                    }
                }

                // set the transaction location based on the self pickup location
                $salesData['transaction_location_id'] = $salesData['selected']['shipping']['self_collection_point_id'];
            } else if (strtolower($shippingMethodMasterData) === strtolower(config::get('mappings.sale_delivery_method.delivery'))) {
                // TODO: find stock location based on delivery address
                // $salesData['stock_location_id'] = '';
            }
        }
    }
}