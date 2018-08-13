<?php
namespace App\Http\Controllers\V1\Sales;

use App\{Interfaces\Masters\MasterInterface,
    Models\Sales\DeliveryOrder,
    Http\Controllers\Controller,
    Models\Sales\Sale,
    Models\Sales\SaleProduct,
    Rules\General\MasterDataTitleExists,
    Rules\Sales\CheckSalesProductBelongsToSales};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeliveryOrderController extends Controller
{
    private
        $deliveryOrderObj,
        $saleObj,
        $saleProductObj,
        $masterRepositoryObj
    ;

    /**
     * DeliveryOrderController constructor.
     *
     * @param DeliveryOrder $deliveryOrder
     * @param Sale $sale
     * @param SaleProduct $saleProduct
     * @param MasterInterface $masterInterface
     */
    public function __construct(
        DeliveryOrder $deliveryOrder,
        Sale $sale,
        SaleProduct $saleProduct,
        MasterInterface $masterInterface)
    {
        $this->deliveryOrderObj = $deliveryOrder;

        $this->saleObj = $sale;

        $this->saleProductObj = $saleProduct;

        $this->masterRepositoryObj = $masterInterface;
    }

    /**
     * create new delivery order
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $success = $errors = [];

        request()->validate([
            'data' => 'required|array'
        ]);

        foreach ($request->input('data') as $item)
        {
            $validate = Validator::make($item,
                [
                    'sale_number' => 'required|exists:sales,document_number',
                    'sales_product_id' => [
                        'required',
                        'integer',
                        'exists:sales_products,id',
                        new CheckSalesProductBelongsToSales(new SaleProduct(), new Sale(), $item['sale_number'])
                    ],
                    'provider' => [
                        new MasterDataTitleExists($this->masterRepositoryObj, 'delivery_order_services')
                    ],
                    'delivered_quantity' => 'required|integer',
                    'delivery_order_number' => 'required',
                    'consignment_order_number' => 'required',
                    'status_code' => 'required|integer|in:0,1,2,3,4,5',
                    'status' => 'required'
                ]);

            if($validate->fails())
            {
                $errors[] = [
                    'record' => $item,
                    'errors' => $validate->errors()->messages()
                ];

                continue;
            }

            $statusCodes = config('setting.delivery-order-status-codes');

            $statusCodeId = $this->masterRepositoryObj
                ->getMasterDataByKey(['delivery_order_status_code'])['delivery_order_status_code']
                ->where('title', $statusCodes[$item['status_code']] )
                ->pluck('id')[0];

            $sale = $this->saleObj->where('document_number', $item['sale_number'])->first();

            $status = $this->masterRepositoryObj
                ->getMasterDataByKey(['delivery_order_status'])['delivery_order_status']
                ->where('title', ucwords(strtolower($item['status'])))
                ->pluck('id');

            if ($status->count())
            {
                $statusId = $status[0];
            }
            else
            {
                $status = $this->masterRepositoryObj->getMasterByKey('delivery_order_status');

                $addedStatus = $status->masterData()->create([
                    'title' => ucwords(strtolower($item['status']))
                ]);

                $statusId = $addedStatus->id;
            }

            //get service id based on provider name
            $service = $this->masterRepositoryObj
                ->getMasterDataByKey(['delivery_order_services'])['delivery_order_services']
                ->where('title', ucwords(strtolower($item['provider'])))
                ->pluck('id');


            if($service->count())
            {
                $serviceId = $service[0];
            }

            $this->deliveryOrderObj->create(
                [
                    'sale_id' => $sale->id,
                    'sales_product_id' => $item['sales_product_id'],
                    'service_id' => $serviceId,
                    'delivered_quantity' => $item['delivered_quantity'],
                    'delivery_order_number' => $item['delivery_order_number'],
                    'consignment_order_number' => $item['consignment_order_number'],
                    'status_code_id' => $statusCodeId,
                    'status_id' => $statusId
                ]
            );

            $success[] = $item;

            $deliveredQuantity = $this->deliveryOrderObj->where('sale_id', $sale->id)->sum('delivered_quantity');

            $saleProductQuantity = $this->saleProductObj->where('sale_id', $sale->id)->sum('quantity');

            if($deliveredQuantity == $saleProductQuantity)
            {
                $this->saleObj->where('id', $sale->id)
                    ->update([
                        'order_status_id' => $this->masterRepositoryObj
                            ->getMasterDataByKey(['sale_order_status'])['sale_order_status']
                            ->where('title', ucwords(strtolower(config('mappings.sale_order_status.completed'))))
                            ->pluck('id')[0]
                    ]);
            }
        }

        return response([
            'success' => $success,
            'error_bag' => $errors
        ]);
    }
}
