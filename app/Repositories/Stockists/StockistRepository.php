<?php
namespace App\Repositories\Stockists;

use App\Models\{
    Stockists\Stockist,
    Stockists\StockistBank,
    Stockists\StockistBusinessAddress,
    Stockists\StockistDepositSetting,
    Stockists\StockistDepositSettingLogs,
    Stockists\StockistGstVat,
    Stockists\StockistLog,
    Stockists\ConsignmentDepositRefund,
    Stockists\ConsignmentOrderReturn,
    Stockists\ConsignmentOrderReturnProduct,
    Stockists\ConsignmentOrderReturnProductClone,
    Stockists\ConsignmentTransaction,
    Stockists\StockistConsignmentProduct,
    Stockists\StockistSalePayment,
    Stockists\StockistSalePaymentTransaction,
    Locations\Entity,
    Locations\Location,
    Locations\LocationTypes,
    Locations\Country,
    Payments\Payment,
    Payments\PaymentModeProvider,
    Payments\PaymentModeSetting,
    Invoices\Invoice,
    Sales\CreditNote,
    Products\Product,
    Sales\Sale,
    Sales\SaleCancellation,
    Sales\SaleExchange,
    Users\User,
    Users\UserType
};
use App\{
    Helpers\Traits\AccessControl,
    Interfaces\Locations\LocationInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Members\MemberInterface,
    Interfaces\Products\ProductInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Stockists\StockistInterface,
    Interfaces\Workflows\WorkflowInterface,
    Repositories\BaseRepository,
    Helpers\Classes\MemberAddress,
    Helpers\Classes\Uploader,
    Helpers\Classes\PdfCreator,
    Helpers\Classes\RandomPassword
};
use Illuminate\{
    Support\Facades\Auth,
    Support\Facades\Storage,
    Support\Facades\Config
};
use \PhpOffice\PhpSpreadsheet\{
    Spreadsheet,
    Writer\Xlsx
};
use Carbon\Carbon;

class StockistRepository extends BaseRepository implements StockistInterface
{
    use AccessControl;

    private
        $locationRepositoryObj,
        $masterRepositoryObj,
        $memberRepositoryObj,
        $productRepositoryObj,
        $settingRepositoryObj,
        $workflowRepositoryObj,
        $stockistBankObj,
        $stockistBusinessAddressObj,
        $stockistDepositSettingObj,
        $stockistDepositSettingLogsObj,
        $stockistGstVatObj,
        $stockistLogObj,
        $consignmentDepositRefundObj,
        $consignmentOrderReturnObj,
        $consignmentOrderReturnProductObj,
        $consignmentOrderReturnProductCloneObj,
        $consignmentTransactionObj,
        $stockistConsignmentProductObj,
        $stockistSalePaymentObj,
        $stockistSalePaymentTransactionObj,
        $entityObj,
        $locationObj,
        $locationTypesObj,
        $countryObj,
        $paymentObj,
        $paymentModeProviderObj,
        $paymentModeSettingObj,
        $invoiceObj,
        $creditNoteObj,
        $productObj,
        $saleObj,
        $saleCancellationObj,
        $saleExchangeObj,
        $userObj,
        $userTypeObj,
        $consignmentDepositRefundTypeConfigCodes,
        $consignmentDepositRefundStatusConfigCodes,
        $consignmentRefundVerificationStatusConfigCodes,
        $consignmentOrderReturnTypeConfigCodes,
        $consignmentOrderStatusConfigCodes,
        $consignmentReturnStatusConfigCodes,
        $locationTypeCodeConfigCodes,
        $memberAddressHelper,
        $uploader;

    /**
     * StockistRepository constructor.
     *
     * @param Stockist $model
     * @param LocationInterface $locationInterface
     * @param MasterInterface $masterInterface
     * @param MemberInterface $memberInterface
     * @param ProductInterface $productInterface
     * @param SettingsInterface $settingsInterface
     * @param WorkflowInterface $workflowInterface
     * @param StockistBank $stockistBank
     * @param StockistBusinessAddress $stockistBusinessAddress
     * @param StockistDepositSetting $stockistDepositSetting
     * @param StockistDepositSettingLogs $stockistDepositSettingLogs
     * @param StockistGstVat $stockistGstVat
     * @param StockistLog $stockistLog
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param ConsignmentOrderReturn $consignmentOrderReturn
     * @param ConsignmentOrderReturnProduct $consignmentOrderReturnProduct
     * @param ConsignmentOrderReturnProductClone $consignmentOrderReturnProductClone
     * @param ConsignmentTransaction $consignmentTransaction
     * @param StockistConsignmentProduct $stockistConsignmentProduct
     * @param StockistSalePayment $stockistSalePayment
     * @param StockistSalePaymentTransaction $stockistSalePaymentTransaction
     * @param Entity $entity
     * @param Location $location
     * @param LocationTypes $locationTypes
     * @param Country $country
     * @param Payment $payment
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param Invoice $invoice
     * @param CreditNote $creditNote
     * @param Product $product
     * @param Sale $sale
     * @param MemberAddress $memberAddress
     * @param Uploader $uploader
     * @param SaleCancellation $saleCancellation
     * @param SaleExchange $saleExchange
     * @param User $user
     * @param UserType $userType
     */
    public function __construct(
        Stockist $model,
        LocationInterface $locationInterface,
        MasterInterface $masterInterface,
        MemberInterface $memberInterface,
        ProductInterface $productInterface,
        SettingsInterface $settingsInterface,
        WorkflowInterface $workflowInterface,
        StockistBank $stockistBank,
        StockistBusinessAddress $stockistBusinessAddress,
        StockistDepositSetting $stockistDepositSetting,
        StockistDepositSettingLogs $stockistDepositSettingLogs,
        StockistGstVat $stockistGstVat,
        StockistLog $stockistLog,
        ConsignmentDepositRefund $consignmentDepositRefund,
        ConsignmentOrderReturn $consignmentOrderReturn,
        ConsignmentOrderReturnProduct $consignmentOrderReturnProduct,
        ConsignmentOrderReturnProductClone $consignmentOrderReturnProductClone,
        ConsignmentTransaction $consignmentTransaction,
        StockistConsignmentProduct $stockistConsignmentProduct,
        StockistSalePayment $stockistSalePayment,
        StockistSalePaymentTransaction $stockistSalePaymentTransaction,
        Entity $entity,
        Location $location,
        LocationTypes $locationTypes,
        Country $country,
        Payment $payment,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentModeSetting,
        Invoice $invoice,
        CreditNote $creditNote,
        Product $product,
        Sale $sale,
        MemberAddress $memberAddress,
        Uploader $uploader,
        SaleCancellation $saleCancellation,
        SaleExchange $saleExchange,
        User $user,
        UserType $userType
    )
    {
        parent::__construct($model);

        $this->stockistBankObj = $stockistBank;

        $this->locationRepositoryObj = $locationInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->memberRepositoryObj = $memberInterface;

        $this->productRepositoryObj = $productInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->workflowRepositoryObj = $workflowInterface;

        $this->stockistBusinessAddressObj = $stockistBusinessAddress;

        $this->stockistDepositSettingObj = $stockistDepositSetting;

        $this->stockistDepositSettingLogsObj = $stockistDepositSettingLogs;

        $this->stockistGstVatObj = $stockistGstVat;

        $this->stockistLogObj = $stockistLog;

        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->consignmentOrderReturnObj = $consignmentOrderReturn;

        $this->consignmentOrderReturnProductObj = $consignmentOrderReturnProduct;

        $this->consignmentOrderReturnProductCloneObj = $consignmentOrderReturnProductClone;

        $this->consignmentTransactionObj = $consignmentTransaction;

        $this->stockistConsignmentProductObj = $stockistConsignmentProduct;

        $this->stockistSalePaymentObj = $stockistSalePayment;

        $this->stockistSalePaymentTransactionObj = $stockistSalePaymentTransaction;

        $this->entityObj = $entity;

        $this->locationObj = $location;

        $this->locationTypesObj = $locationTypes;

        $this->countryObj = $country;

        $this->paymentObj = $payment;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->invoiceObj = $invoice;

        $this->creditNoteObj = $creditNote;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->productObj = $product;

        $this->saleObj = $sale;

        $this->saleCancellationObj = $saleCancellation;

        $this->saleExchangeObj = $saleExchange;

        $this->userObj = $user;

        $this->userTypeObj = $userType;

        $this->memberAddressHelper = $memberAddress;

        $this->uploader = $uploader;

        $this->consignmentDepositRefundTypeConfigCodes =
            config('mappings.consignment_deposit_and_refund_type');

        $this->consignmentDepositRefundStatusConfigCodes =
            config('mappings.consignment_deposit_and_refund_status');

        $this->consignmentRefundVerificationStatusConfigCodes =
            config('mappings.consignment_refund_verification_status');

        $this->consignmentOrderReturnTypeConfigCodes =
            config('mappings.consignment_order_and_return_type');

        $this->consignmentOrderStatusConfigCodes =
            config('mappings.consignment_order_status');

        $this->consignmentReturnStatusConfigCodes =
            config('mappings.consignment_return_status');

        $this->locationTypeCodeConfigCodes =
            config('mappings.location_type_code');
    }

    /**
     * get stockist details for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get stockist details filtered by below parameter
     *
     * @param int $countryId
     * @param string $text
     * @param int $stockistTypeId
     * @param int $stockistStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getStockistsByFilters(
        int $countryId = 0,
        string $text = '',
        int $stockistTypeId = 0,
        int $stockistStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['country', 'member.country',
                'createdBy', 'status', 'stockistType',
                'stockistUser'
            ])
            ->where('stockists.country_id', $countryId);

        //rnp check if user is stockist
        if ($this->isUser('stockist') ) {
            $data = $data->where('stockist_user_id', Auth::id());
        } elseif($this->isUser('stockist_staff')){
            $data = $data->where('stockist_user_id', $this->getStockistParentUser());
        }

        //rnp check if auth is back_office user
        if ($this->isUser('back_office')) {
            $data = $data->whereIn('stockists.stockist_number',
                $this->getUserLocations($countryId, 'code')
            );
        }

        //text search
        if ($text != '') {
            $data = $data
                ->join('users', function ($join){
                    $join->on('users.id', '=', 'stockists.member_user_id');
                })
                ->where(function ($textQuery) use($text) {
                    $textQuery->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('stockists.stockist_number', 'like','%' . $text . '%')
                        ->orWhere('stockists.name', 'like','%' . $text . '%');
                });
        }

        //check stockist type if given
        if($stockistTypeId > 0){
            $data = $data->where('stockists.stockist_type_id', $stockistTypeId);
        }

        //check stockist status if given
        if($stockistStatusId > 0){
            $data = $data->where('stockists.status_id', $stockistStatusId);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('stockists.*');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * create or update stockist
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdateStockist(array $data)
    {
        $stockistDetail = $data['stockist_data'];

        $creation = (isset($stockistDetail['details']['id'])) ? false : true;

        //save stockist record
        $stockistData = $stockistDetail['details'];

        $stockistData['email'] = $stockistDetail['business_address']['email_1'];

        if($creation){

            $randomPasswordClass = new RandomPassword();

            $password = $randomPasswordClass->generate(16);

            //Insert Stockist User
            $stockistUser = $this->userObj->create([
                'name' => $stockistData['name'],
                'email' => $stockistDetail['business_address']['email_1'],
                'password' => bcrypt($password),
                'first_time_login' => 0
            ]);

            //Insert User Type
            $stockistUserType = $this->userTypeObj
                ->where('name', config('mappings.user_types.stockist'))
                ->first();

            $stockistUser->userType()->sync([$stockistUserType->id]);

            //Insert Stockist Record
            $stockistData['stockist_user_id'] = $stockistUser->id;

            $stockistData['registered_date'] = Carbon::now()->format('Y-m-d');

            $stockist = Auth::user()->createdBy($this->modelObj)->create($stockistData);

            //Form Stockist Log Data
            $stockistLogData = [
                'stockist_id' => $stockist->id,
                'stockist_type_id' => $stockistData['stockist_type_id'],
                'status_id' => $stockistData['status_id'],
                'stockist_ratio' => $stockistData['stockist_ratio'],
                'ibs_online' => $stockistData['ibs_online'],
                'effective_date' => $stockistData['effective_date'],
                'remark' => trans('message.stockist-log-message.initial')
            ];

        } else {

            $stockistData['updated_by'] = Auth::id();

            $stockist = $this->find($stockistData['id']);

            //Form Stockist Log Data
            $stockistLogData = [
                'stockist_type_id' => ($stockist->stockist_type_id != $stockistData['stockist_type_id']) ?
                    $stockistData['stockist_type_id'] : NULL,

                'status_id' => ($stockist->status_id != $stockistData['status_id']) ?
                    $stockistData['status_id'] : NULL,

                'stockist_ratio' => ($stockist->stockist_ratio != $stockistData['stockist_ratio']) ?
                    $stockistData['stockist_ratio'] : NULL,

                'ibs_online' => ($stockist->ibs_online != $stockistData['ibs_online']) ?
                    $stockistData['ibs_online'] : NULL,

                'effective_date' => ($stockist->effective_date != $stockistData['effective_date']) ?
                    $stockistData['effective_date'] : NULL
            ];

            $stockistDataChanges = collect($stockistLogData)
                ->filter(function ($value, $key) {
                    return $value != NULL;
                })
                ->count();

            if($stockistDataChanges > 0){

                $stockistLogData['stockist_id'] = $stockist->id;

                $stockistLogData['remark'] = $stockistData['remark'];

            } else {
                $stockistLogData = [];
            }

            //Update Stockist
            $stockist->update($stockistData);
        }

        //save business address record
        $businessAddressData = $stockistDetail['business_address'];

        $businessAddressField = $businessAddressData['addresses'];

        $businessAddressData['addresses'] = json_encode($businessAddressData['addresses']);

        if($creation){

            $businessAddressData['stockist_id'] = $stockist->id;

            $stockistBusinessAddress = $this->stockistBusinessAddressObj->create($businessAddressData);

        } else {

            $businessAddressData['updated_by'] = Auth::id();

            $stockistBusinessAddress = $this->stockistBusinessAddressObj
                ->where('stockist_id', $stockist->id)
                ->first();

            $stockistBusinessAddress->update($businessAddressData);
        }

        //Save deposit record
        $depositData = $stockistDetail['deposits'];

        if($creation){

            $depositData['deposit_balance'] = 0;

            $depositData['deposit_limit'] = 0;

            $depositData['stockist_id'] = $stockist->id;

            $stockistDepositSetting = Auth::user()->createdBy($this->stockistDepositSettingObj)->create($depositData);

            //Form Stockist Deposit Log Data
            $stockistDepositLogData = [
                'stockist_deposit_setting_id' => $stockistDepositSetting->id,
                'minimum_initial_deposit' => $depositData['minimum_initial_deposit'],
                'maximum_initial_deposit' => $depositData['maximum_initial_deposit'],
                'minimum_top_up_deposit' => $depositData['minimum_top_up_deposit'],
                'maximum_top_up_deposit' => $depositData['maximum_top_up_deposit'],
                'minimum_capping' => $depositData['minimum_capping'],
                'credit_limit_1' => $depositData['credit_limit_1'],
                'credit_limit_2' => $depositData['credit_limit_2'],
                'credit_limit_3' => $depositData['credit_limit_3']
            ];

        } else {

            unset($depositData['deposit_balance']);

            unset($depositData['deposit_limit']);

            $depositData['updated_by'] = Auth::id();

            $stockistDepositSetting = $this->stockistDepositSettingObj
                ->where('stockist_id', $stockist->id)
                ->first();

            //Form Stockist Deposit Log Data
            $stockistDepositLogData = [
                'minimum_initial_deposit' =>
                    ($stockistDepositSetting->minimum_initial_deposit != $depositData['minimum_initial_deposit']) ?
                        $depositData['minimum_initial_deposit'] : NULL,

                'maximum_initial_deposit' =>
                    ($stockistDepositSetting->maximum_initial_deposit != $depositData['maximum_initial_deposit']) ?
                        $depositData['maximum_initial_deposit'] : NULL,

                'minimum_top_up_deposit' =>
                    ($stockistDepositSetting->minimum_top_up_deposit != $depositData['minimum_top_up_deposit']) ?
                        $depositData['minimum_top_up_deposit'] : NULL,

                'maximum_top_up_deposit' =>
                    ($stockistDepositSetting->maximum_top_up_deposit != $depositData['maximum_top_up_deposit']) ?
                        $depositData['maximum_top_up_deposit'] : NULL,

                'minimum_capping' =>
                    ($stockistDepositSetting->minimum_capping != $depositData['minimum_capping']) ?
                        $depositData['minimum_capping'] : NULL,

                'credit_limit_1' =>
                    ($stockistDepositSetting->credit_limit_1 != $depositData['credit_limit_1']) ?
                        $depositData['credit_limit_1'] : NULL,

                'credit_limit_2' =>
                    ($stockistDepositSetting->credit_limit_2 != $depositData['credit_limit_2']) ?
                        $depositData['credit_limit_2'] : NULL,

                'credit_limit_3' =>
                    ($stockistDepositSetting->credit_limit_3 != $depositData['credit_limit_3']) ?
                        $depositData['credit_limit_3'] : NULL
            ];

            $stockistDepositChanges = collect($stockistDepositLogData)
                ->filter(function ($value, $key) {
                    return $value != NULL;
                })
                ->count();

            if($stockistDepositChanges > 0){
                $stockistDepositLogData['stockist_deposit_setting_id'] = $stockistDepositSetting->id;
            } else {
                $stockistDepositLogData = [];
            }

            $stockistDepositSetting->update($depositData);
        }

        //save bank record
        $bankData = $stockistDetail['banks'];

        $bankData['bank_detail'] = json_encode($bankData['bank_detail']);

        if($creation){

            $bankData['stockist_id'] = $stockist->id;

            $stockistBank = $this->stockistBankObj->create($bankData);

        } else {

            $bankData['updated_by'] = Auth::id();

            $stockistBank = $this->stockistBankObj
                ->where('stockist_id', $stockist->id)
                ->first();

            $stockistBank->update($bankData);
        }

        //save gstVat record
        $gstVatData = $stockistDetail['gst_vat'];

        $gstVatData['gst_vat_detail'] = json_encode($gstVatData['gst_vat_detail']);

        if($creation){

            $gstVatData['stockist_id'] = $stockist->id;

            $stockistGstVat = $this->stockistGstVatObj->create($gstVatData);

        } else {

            $gstVatData['updated_by'] = Auth::id();

            $stockistGstVat = $this->stockistGstVatObj
                ->where('stockist_id', $stockist->id)
                ->first();

            $stockistGstVat->update($gstVatData);
        }

        if(!empty($stockistLogData)){
            $stockistLog = Auth::user()->createdBy($this->stockistLogObj)->create($stockistLogData);
        }

        if(!empty($stockistDepositLogData)){
            $stockistDepositLog = Auth::user()->createdBy($this->stockistDepositSettingLogsObj)
                ->create($stockistDepositLogData);
        }

        //Prepare Stockist Location Data
        $addressData[] = array_merge(
            [
                "title" => "Business Address"
            ],
            $businessAddressField[0]
        );

        $countryDetail = collect($businessAddressField[0]['fields'])->where('key', 'countries')->first();

        $stateDetail = collect($businessAddressField[0]['fields'])->where('key', 'states')->first();

        $cityDetail = collect($businessAddressField[0]['fields'])->where('key', 'cities')->first();

        $locationAddress = [
            "telephone_code_id" => $businessAddressData['telephone_office_country_code_id'],
            "telephone_num" => $businessAddressData['telephone_office_num'],
            "mobile_phone_code_id" => $businessAddressData['mobile_1_country_code_id'],
            "mobile_phone_num" => $businessAddressData['mobile_1_num'],
            "country_id" => (isset($countryDetail['value'])) ? $countryDetail['value'] : null,
            "state_id" => (isset($stateDetail['value'])) ? $stateDetail['value'] : null,
            "city_id" => (isset($cityDetail['value'])) ? $cityDetail['value'] : null,
            "address_data" => $addressData,
        ];

        //Insert Stockist Location
        if($creation){

            //Get Entity Record
            $entity = $this->entityObj
                ->where('country_id', $stockistData['country_id'])
                ->first();

            //Get Location Type ID
            $locationTypes = $this->locationTypesObj
                ->where('code', 'stockist')
                ->first();

            //Insert Stockist Location
            $this->locationRepositoryObj->create(
                [
                    "entity_id" => $entity->id,
                    "name" => $stockistData['name'],
                    "code" => $stockistData['stockist_number'],
                    "location_types_id" => $locationTypes->id,
                    "active" => 1,
                    "address" => $locationAddress
                ]
            );

        } else {

            //Get Stockist Location ID
            $stockistLocation = $stockist->stockistLocation()->first();

            //Get Stockist Location Address ID
            $stockistLocationAddress = $stockistLocation->locationAddress()->first();

            if($stockistLocationAddress){

                $locationAddress['id'] = $stockistLocationAddress->id;

                //Update Stockist Location
                $this->locationRepositoryObj->update(
                    [
                        "entity_id" => $stockistLocation->entity_id,
                        "name" => $stockistLocation->name,
                        "code" => $stockistLocation->code,
                        "location_types_id" => $stockistLocation->location_types_id,
                        "active" => $stockistLocation->active,
                        "address" => $locationAddress
                    ],
                    $stockistLocation->id
                );
            } else {

                //Get Entity Record
                $entity = $this->entityObj
                    ->where('country_id', $stockistData['country_id'])
                    ->first();

                //Get Location Type ID
                $locationTypes = $this->locationTypesObj
                    ->where('code', 'stockist')
                    ->first();

                //Insert Stockist Location
                $this->locationRepositoryObj->create(
                    [
                        "entity_id" => $entity->id,
                        "name" => $stockistData['name'],
                        "code" => $stockistData['stockist_number'],
                        "location_types_id" => $locationTypes->id,
                        "active" => 1,
                        "address" => $locationAddress
                    ]
                );
            }
        }

        //Update Stockist Stock Location
        $stockistStockLocations = $stockistDetail['stockist_stock_location'];

        $stockistStockLocationData = [
            $stockistDetail['stockist_stock_location']['id']
        ];

        $stockist->stockistLocation->stockLocations()->sync($stockistStockLocationData);

        return $this->stockistDetails($stockist->stockist_user_id);
    }

    /**
     * get stockist detail by given stockistUserId
     *
     * @param int $stockistUserId
     * @return mixed
     */
    public function stockistDetails(int $stockistUserId)
    {
        $stockist = $this->modelObj->where('stockist_user_id', $stockistUserId)->first();

        $stockistMember = $this->memberRepositoryObj->memberDetails($stockist->member_user_id);

        $stockistDeposit = $stockist->depositSetting()->first();

        return [
            'stockist_data' => array_merge(
                [
                    'details' => array_merge(
                        $stockist->toArray(),
                        [
                            'country' => $stockist->country()->first(),
                            'stockistType' => $stockist->stockistType()->first(),
                            'status' => $stockist->status()->first(),
                            'remark' => ''
                        ]
                    ),
                    'business_address' => $stockist->businessAddress()->first(),
                    'deposits' => array_merge(
                        $stockistDeposit->toArray(),
                        [
                            'last_minimum_initial_deposit' => $stockistDeposit->getLastModifiedDepositSettings('minimum_initial_deposit'),
                            'last_maximum_initial_deposit' => $stockistDeposit->getLastModifiedDepositSettings('maximum_initial_deposit'),
                            'last_minimum_top_up_deposit' => $stockistDeposit->getLastModifiedDepositSettings('minimum_top_up_deposit'),
                            'last_maximum_top_up_deposit' => $stockistDeposit->getLastModifiedDepositSettings('maximum_top_up_deposit'),
                            'last_minimum_capping' => $stockistDeposit->getLastModifiedDepositSettings('minimum_capping'),
                            'last_credit_limit_1' => $stockistDeposit->getLastModifiedDepositSettings('credit_limit_1'),
                            'last_credit_limit_2' => $stockistDeposit->getLastModifiedDepositSettings('credit_limit_2'),
                            'last_credit_limit_3' => $stockistDeposit->getLastModifiedDepositSettings('credit_limit_3'),
                        ]
                    ),
                    'banks' => $stockist->bank()->first(),
                    'gst_vat' => $stockist->gstVat()->first(),
                    'logs' => $stockist->stockistLog()->with(['stockistType', 'status'])->get(),
                    'stockist_location' => array_merge(
                        $stockist->stockistLocation()->with('stockLocations')->first()->toArray(),
                        [
                            'stock_locations' => $stockist->stockistLocation->stockLocations()->first()
                        ]
                    ),
                    'stockist_stock_location' => $stockist->stockistLocation->stockLocations()->first()
                ],
                $stockistMember
            )
        ];
    }

    /**
     * get consignment deposit refund filtered by below parameter
     *
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $typeId
     * @param int $statusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getConsignmentDepositRefundByFilters(
        int $countryId = 0,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $typeId = 0,
        int $statusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->consignmentDepositRefundObj
            ->with(['stockist.country', 'consignmentDepositRefundType',
                'status', 'actionBy', 'verificationStatus',
                'verifiedBy', 'createdBy'
            ]);

        $data = $data->join('stockists', function ($join)
        use ($countryId){
            $join->on('consignments_deposits_refunds.stockist_id', '=', 'stockists.id')
                ->where(function ($consignmentQuery) use ($countryId) {
                    $consignmentQuery->where('stockists.country_id', $countryId);

                    //rnp check if auth is back_office user
                    if ($this->isUser('back_office')) {
                        $consignmentQuery->whereIn('stockists.stockist_number', $this->getUserLocations($countryId, 'code'));
                    }
                });

            //rnp check if user stockist
            if ($this->isUser('stockist')) {
                $join->where('stockists.stockist_user_id', Auth::id());
            }elseif( $this->isUser('stockist_staff')){
                $join->where('stockists.stockist_user_id', $this->getStockistParentUser());
            }
        });


        if ($text != '') {
            $data = $data
                ->join('users', function ($join){
                    $join->on('users.id', '=', 'stockists.member_user_id');
                })
                ->join('members', function ($join){
                    $join->on('users.id', '=', 'members.user_id');
                })
                ->where(function ($textQuery) use($text) {
                    $textQuery->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('members.name', 'like','%' . $text . '%')
                        ->orWhere('members.ic_passport_number', 'like','%' . $text . '%')
                        ->orWhere('stockists.stockist_number', 'like','%' . $text . '%')
                        ->orWhere('stockists.name', 'like','%' . $text . '%')
                        ->orWhere('consignments_deposits_refunds.document_number', 'like','%' . $text . '%');
                });
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('consignments_deposits_refunds.transaction_date','>=', $dateFrom)
                ->where('consignments_deposits_refunds.transaction_date','<=', $dateTo);
        }

        //check consignment deposit refund type if given
        if($typeId > 0){
            $data = $data->where('stockists.stockist_type_id', $typeId);
        }

        //check consignment deposit refund status if given
        if($statusId > 0){
            $data = $data->where('consignments_deposits_refunds.status_id', $statusId);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('consignments_deposits_refunds.*');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * validate stockist consignment operation for a given stockistUserId, deposit refund type
     *
     * @param int $stockistUserId
     * @param string $type
     * @return mixed
     */
    public function validatesConsignmentDepositsRefunds(int $stockistUserId, string $type)
    {
        //Get consignment deposit status
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_status',
                'consignment_order_status',
                'consignment_return_status'
            )
        );

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']->pluck('id','title')->toArray()
        );

        $orderStatus = array_change_key_case(
            $masterSettingsDatas['consignment_order_status']->pluck('id','title')->toArray()
        );

        $returnStatus = array_change_key_case(
            $masterSettingsDatas['consignment_return_status']->pluck('id','title')->toArray()
        );

        //Get Stockist Details
        $stockistData = $this->stockistDetails($stockistUserId);

        $stockistDataDetail = $stockistData['stockist_data']['details'];

        $stockistDepositSetting = $stockistData['stockist_data']['deposits'];

        if($type == 'deposit'){

            $depositApprovedCode = $this->consignmentDepositRefundStatusConfigCodes['approved'];

            //Calculate Consignment Deposits Refund Record
            $depositRefundCount = $this->consignmentDepositRefundObj
                ->where('stockist_id', $stockistDataDetail['id'])
                ->where('status_id', $depositRefundStatus[$depositApprovedCode])
                ->count();

            $minimum_capping = $minimum_credit_limit_capping = NULL;

            $transactionBlock = false;

            if($depositRefundCount > 0){

                $minimum_amount = $stockistDepositSetting['minimum_top_up_deposit'];

                $maximum_amount = $stockistDepositSetting['maximum_top_up_deposit'];

            } else {

                $minimum_amount = $stockistDepositSetting['minimum_initial_deposit'];

                $maximum_amount = $stockistDepositSetting['maximum_initial_deposit'];
            }

        } else {

            $minimum_amount = $maximum_amount = NULL;

            $minimum_capping = $stockistDepositSetting['minimum_capping'];

            $minimum_credit_limit_capping = floatval($stockistDepositSetting['minimum_capping']) *
                floatval($stockistDataDetail['stockist_ratio']);

            //Block to refund if has active consignment transaction
            $initialDepositRefundStatusId =
                $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['initial']];

            $pendingDepositRefundStatusId =
                $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['pending']];

            $pendingOrderStatusId = $orderStatus[$this->consignmentOrderStatusConfigCodes['pending']];

            $pendingReturnStatusId = $returnStatus[$this->consignmentReturnStatusConfigCodes['pending']];

            $activeDepositRefundCount = $this->consignmentDepositRefundObj
                ->where('stockist_id', $stockistDataDetail['id'])
                ->whereIn('status_id', [$pendingDepositRefundStatusId, $initialDepositRefundStatusId])
                ->count();

            $activeOrderReturnCount = $this->consignmentOrderReturnObj
                ->where('stockist_id', $stockistDataDetail['id'])
                ->whereIn('status_id', [$pendingOrderStatusId, $pendingReturnStatusId])
                ->count();

            $transactionBlock = (($activeDepositRefundCount + $activeOrderReturnCount) > 0) ?
                false : true;
        }

        return [
            'consignment_deposit_refund'  => [
                'stockist_id' => $stockistDataDetail['id'],
                'stockist_user_id' => $stockistDataDetail['stockist_user_id'],
                'transaction_date' => Carbon::now()->format('Y-m-d'),
                'amount' => 0,
                'credit_limit' => 0,
                'ratio' => $stockistDataDetail['stockist_ratio'],
                'minimum_amount' => $minimum_amount,
                'maximum_amount' => $maximum_amount,
                'minimum_capping' => $minimum_capping,
                'minimum_credit_limit_capping' => $minimum_credit_limit_capping,
                'deposit_balance' => $stockistDepositSetting['deposit_balance'],
                'deposit_limit' => $stockistDepositSetting['deposit_limit'],
                'remark' => "",
                'payment' => []
            ],
            'stockist_data' => $stockistData['stockist_data'],
            'workflow' => [],
            'is_allow_consignment_refund' => $transactionBlock
        ];
    }

    /**
     * create consignment deposits
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentDeposit(array $data)
    {
        return $this->insertConsignmentDepositRefund('deposit', $data);
    }

    /**
     * get consignment deposits refunds detail by given ID
     *
     * @param int $consignmentDepositRefundId
     * @return mixed
     */
    public function consignmentDepositsRefundsDetails(int $consignmentDepositRefundId)
    {
        $consignmentDepositRefund = $this->consignmentDepositRefundObj
            ->with([
                'consignmentDepositRefundType', 'status', 'actionBy',
                'verificationStatus', 'verifiedBy', 'createdBy'
            ])
            ->find($consignmentDepositRefundId);

        $stockistData = $this->stockistDetails($consignmentDepositRefund->stockist->stockist_user_id);

        $workflow = [];

        (!empty($consignmentDepositRefund->workflow_tracking_id)) ?
            $workflow = $this->workflowRepositoryObj
                ->getTrackingWorkflowDetails($consignmentDepositRefund->workflow_tracking_id) : [];

        return [
            'consignment_deposit_refund' => array_merge(
                $consignmentDepositRefund->toArray(),
                [
                    'stockist_user_id' => $consignmentDepositRefund->stockist->stockist_user_id,
                    'selected' => [
                        'payments' => [
                            'paid' => $consignmentDepositRefund->payments()->get(),
                            'unpaid' => []
                        ],
                    ],
                    'deposit_balance' => $stockistData['stockist_data']['deposits']['deposit_balance'],
                    'deposit_limit' => $stockistData['stockist_data']['deposits']['deposit_limit']
                ]
            ),
            'stockist_data' => $stockistData['stockist_data'],
            'workflow' => $workflow
        ];
    }

    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $consignmentOrderReturnId
     * @return \Illuminate\Support\Collection
     * @throws \Mpdf\MpdfException
     */
    public function downloadConsignmentNote(int $consignmentOrderReturnId)
    {
        //TODO clean up the bellow code
        $consignment = $this->consignmentOrderReturnObj->find($consignmentOrderReturnId);

        $products = $consignment->consignmentOrderReturnProduct;

        $address = $tel = '';

        $stockistBusinessAddress = $consignment->stockist->businessAddress;

        if (isset($stockistBusinessAddress)) {
            if ($stockistBusinessAddress->mobile_1_country_code_id != null) {
                $country = $this->countryObj->find($stockistBusinessAddress->mobile_1_country_code_id);
                if ($country != null) {
                    if ($country->code_iso_2 != null) {
                        $tel = $tel . $country->code_iso_2;
                    }
                    if ($country->call_code != null) {
                        $tel = $tel . '+' . $country->call_code;
                    }
                }
            }

            if ($stockistBusinessAddress->mobile_1_num != null) {
                $tel = $tel . $stockistBusinessAddress->mobile_1_num;
            }

            if ($stockistBusinessAddress->addresses != null) {
                $address = $this->memberAddressHelper->getAddress($stockistBusinessAddress->addresses, "");
            }
        }

        $basic = [
            'no' => $consignment->document_number,
            'date' => $consignment->transaction_date,
            'stockistID' => $consignment->stockist->stockist_number,
            'name' => $consignment->stockist->name,
            'type' => $consignment->consignmentOrderReturnType->title,
            'refNo' =>'',
            'address' => $address,
            'tel' => $tel,
            'location' => isset($consignment->stockLocation->code) ? $consignment->stockLocation->code : null,
            'currency' => $consignment->stockist->country->currency->code,
            'country' => $consignment->stockist->country->code_iso_2,
            'tax' => $consignment->stockist->country->code_iso_2 == 'TH'? true:false,
            'issuer' => $consignment->createdBy->name
        ];

        $sales = array();
        //sales products lines
        $salesProducts = array();

        //populate summary product lines
        $productsSummary = array();

        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;

        foreach($products as $product){

            //if this product quantity is 0, continue to the next one because we are not showing those with 0
            if(!$product->quantity){
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            $productsSummary[$product->consignmentOrderReturnProductClone->sku] = isset($productsSummary[$product->consignmentOrderReturnProductClone->sku])
                ? $product->quantity + $productsSummary[$product->consignmentOrderReturnProductClone->sku]
                : $product->quantity;

            $total = 0;
            $excludingTaxGmp = $product->unit_nmp_price * $product->quantity;

            $total = $product->unit_gmp_price_gst * $product->quantity;

            $salesProducts[] = array(
                'no' => $lineCount,
                'code' => $product->consignmentOrderReturnProductClone->sku,
                'description' => $product->consignmentOrderReturnProductClone->name,
                'qty' => $product->quantity,
                'unitPrice' => $product->unit_gmp_price_gst,
                'excTax' => $excludingTaxGmp,
                'tax' => $total - $excludingTaxGmp,
                'total' => $total
            );

            $totalProductQty += $product->quantity;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;

            $lineCount++;
        }

        $summary = array(
            'items' => $productsSummary
        );

        $sales = array(
            'products' => $salesProducts,
            'subTotal' => ['qty' => $totalProductQty, 'excTax' => $totalExcTax, 'tax' => $totalTax, 'total' =>$totalIncTax, 'exempt' => 0.00, 'zeroRated' => 0.00],
            'delivery' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'admin' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'other' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'total' => ['excTax' => $consignment->total_amount, 'tax' => $consignment->total_tax, 'total' => $consignment->total_gmp, 'exempt' => 0.00, 'zeroRated' => 0.00]
        );

        $view = 'invoices.consignment_note';

        $html = \View::make($view)
            ->with('basic', $basic)
            ->with('summary', $summary)
            ->with('remarks', $consignment->remarks)
            ->with('sales', $sales)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 0, 'margin_bottom' =>0];

        $mpdf = new PdfCreator($config);

        $mpdf->WriteHTML($html, 2);

        $total = $mpdf->getTotalPage();

        $config['margin_bottom'] = 20;

        $mpdf = new PdfCreator($config);

        $html = str_replace('{nb}', $total, $html);

        $mpdf->WriteHTML($html);

        $absoluteUrlPath = Config::get('filesystems.subpath.consignment_note.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('consignment_note' . $consignmentOrderReturnId) . '.pdf';

        $consignmentNoteDownloadLink = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        $view = 'invoices.consignment_note_return';

        $html = \View::make($view)
            ->with('basic', $basic)
            ->with('summary', $summary)
            ->with('remarks', $consignment->remarks)
            ->with('sales', $sales)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 0, 'margin_bottom' => 0];

        $mpdf = new PdfCreator($config);

        $mpdf->WriteHTML($html, 2);

        $total = $mpdf->getTotalPage();

        $config['margin_bottom'] = 20;

        $mpdf = new PdfCreator($config);

        $html = str_replace('{nb}', $total, $html);

        $mpdf->WriteHTML($html);

        $fileName = $this->uploader->getRandomFileName('consignment_note_return' . $consignmentOrderReturnId) . '.pdf';

        $consignmentNoteReturnDownloadLink = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(
            array(
                'consignement_note_download_link' => $consignmentNoteDownloadLink,
                'consignement_note_return_download_link' => $consignmentNoteReturnDownloadLink
            )
        );
    }

    /**
     * create consignment refund
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentRefund(array $data)
    {
        return $this->insertConsignmentDepositRefund('refund', $data);
    }

    /**
     * update consignment deposit return
     *
     * @param array $data
     * @param int $consignmentDepositReturnId
     * @return array|mixed
     */
    public function updateConsignmentDepositReturn(array $data, int $consignmentDepositReturnId)
    {
        $consignmentData = $data['consignment_deposit_refund'];

        //Get consignment deposit status
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey([
            'consignment_deposit_and_refund_status'
        ]);

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']
                ->pluck('id','title')
                ->toArray()
        );

        //Get consignment deposit return data
        $consignmentDepositReturnDetail = $this->consignmentDepositRefundObj->find($consignmentDepositReturnId);

        if($consignmentData['update_type'] == 'cancel_deposit'){

            $cancelledStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['cancelled']];

            $consignmentDepositReturnDetail->update([
                'status_id' => $cancelledStatusId,
                'updated_by' => Auth::id()
            ]);
        }

        return $this->consignmentDepositsRefundsDetails($consignmentDepositReturnId);
    }

    /**
     * Create consignment workflow
     *
     * @param int $consignmentId
     * @param string $consignmentType
     * @return mixed
     */
    public function createConsignmentWorkflow(int $consignmentId, string $consignmentType)
    {
        $consignmentWorkflowSettings = $this->settingRepositoryObj->getSettingDataByKey(
            array(
                'stockist_consignment_transaction_workflow'
            ));

        $consignmentWorkflow = collect(json_decode(
            $consignmentWorkflowSettings['stockist_consignment_transaction_workflow'][0]['value']));

        if($consignmentType == 'deposit') {

            $workflowCode = $consignmentWorkflow['consignment_deposit'];

            $workflowMappingTable = 'ConsignmentDepositRefund';

        } else if($consignmentType == 'refund') {

            $workflowCode = $consignmentWorkflow['consignment_refund'];

            $workflowMappingTable = 'ConsignmentDepositRefund';

        } else if($consignmentType == 'order') {

            $workflowCode = $consignmentWorkflow['consignment_order'];

            $workflowMappingTable = 'ConsignmentOrderReturn';

        } else if($consignmentType == 'return') {

            $workflowCode = $consignmentWorkflow['consignment_return'];

            $workflowMappingTable = 'ConsignmentOrderReturn';
        }

        //Transaction Details
        $consignmentDetails = (($consignmentType == 'deposit') || ($consignmentType == 'refund')) ?
            $this->consignmentDepositRefundObj->find($consignmentId) :
            $this->consignmentOrderReturnObj->find($consignmentId);

        $workflowDetail = $this->workflowRepositoryObj
            ->listWorkflowSteps($workflowCode);

        $workflowTrackingDetail = $this->workflowRepositoryObj->copyWorkflows(
            $consignmentId, $workflowMappingTable,
            $workflowDetail->id, $consignmentDetails->stockist->stockist_user_id
        );

        //Update Workflow ID
        $consignmentDetails->update(
            array(
                'workflow_tracking_id' => $workflowTrackingDetail['workflow']['workflow_tracking_id'],
                'updated_by' => Auth::id()
            )
        );
    }

    /**
     * Insert consignment deposits and refund record
     *
     * @param string $type
     * @param array $data
     * @return mixed
     */
    private function insertConsignmentDepositRefund(string $type, array $data)
    {
        $consignmentData = $data['consignment_deposit_refund'];

        //Get consignment deposit status
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_status',
                'consignment_deposit_and_refund_type',
                'consignment_refund_verification_status'
            )
        );

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']
                ->pluck('id','title')
                ->toArray()
        );

        $depositRefundType = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']
                ->pluck('id','title')
                ->toArray()
        );

        $refundVerificationStatus = array_change_key_case(
            $masterSettingsDatas['consignment_refund_verification_status']
                ->pluck('id','title')
                ->toArray()
        );

        //Get Stockist Details
        $stockist = $this->find($consignmentData['stockist_id']);

        //Get Stockist Consignment Master Setup
        $consignmentSetup = $this->validatesConsignmentDepositsRefunds($stockist->stockist_user_id, $type);

        $depositTypeCode = $this->consignmentDepositRefundTypeConfigCodes[$type];

        $depositInitialCode = $this->consignmentDepositRefundStatusConfigCodes['initial'];

        $depositPendingCode = $this->consignmentDepositRefundStatusConfigCodes['pending'];

        $refundVerificationPendingCode = $this->consignmentRefundVerificationStatusConfigCodes['pending'];

        $consignmentDepositData = [
            'stockist_id' => $consignmentData['stockist_id'],
            'type_id' => $depositRefundType[$depositTypeCode],
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'document_number' => NULL,
            'amount' => $consignmentData['amount'],
            'credit_limit' => floatval($consignmentData['amount']) * floatval($consignmentSetup['consignment_deposit_refund']['ratio']),
            'ratio' => $consignmentSetup['consignment_deposit_refund']['ratio'],
            'minimum_amount' => $consignmentSetup['consignment_deposit_refund']['minimum_amount'],
            'maximum_amount' => $consignmentSetup['consignment_deposit_refund']['maximum_amount'],
            'minimum_capping' => $consignmentSetup['consignment_deposit_refund']['minimum_capping'],
            'minimum_credit_limit_capping' => $consignmentSetup['consignment_deposit_refund']['minimum_credit_limit_capping'],
            'status_id' => ($type == 'deposit') ? $depositRefundStatus[$depositInitialCode] :
                $depositRefundStatus[$depositPendingCode],
        ];

        $consignmentDepositData['verification_status_id'] = ($type == 'refund') ?
            $refundVerificationStatus[$refundVerificationPendingCode] :
            NULL;

        $consignmentDeposit = Auth::user()->createdBy($this->consignmentDepositRefundObj)->create($consignmentDepositData);

        //Create Workflow
        if($type == 'refund'){
            $this->createConsignmentWorkflow($consignmentDeposit->id, 'refund');
        }

        return $this->consignmentDepositsRefundsDetails($consignmentDeposit->id);
    }

    /**
     * validates no pending consignment return before create new return by giving stockist user id
     *
     * @param int $stockistUserId
     * @return mixed
     */
    public function validatesConsignmentReturn(int $stockistUserId)
    {
        //Get related master data ID
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_order_and_return_type',
                'consignment_return_status'
            )
        );

        //Get Return Type Master ID
        $consignReturnType = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']->pluck('id','title')->toArray()
        );

        $returnTypeCode = $this->consignmentOrderReturnTypeConfigCodes['return'];

        $consignReturnTypeId = $consignReturnType[$returnTypeCode];

        //Get Return Pending Status ID
        $returnStatus = array_change_key_case(
            $masterSettingsDatas['consignment_return_status']->pluck('id','title')->toArray()
        );

        $returnPendingCode = $this->consignmentReturnStatusConfigCodes['pending'];

        $returnPendingId = $returnStatus[$returnPendingCode];

        //Get stockist details
        $stockist = $this->modelObj->where('stockist_user_id', $stockistUserId)->first();

        //Get pending consignment return transaction
        $pendingReturnTransaction = $this->consignmentOrderReturnObj
            ->where('stockist_id', $stockist->id)
            ->where('type_id', $consignReturnTypeId)
            ->where('status_id', $returnPendingId)
            ->first();

        $allowConsignmentReturnResult = ($pendingReturnTransaction) ? false : true;

        return collect([
            'is_allow_consignment_return' => $allowConsignmentReturnResult
        ]);
    }

    /**
     * get consignment order return filtered by below parameter
     *
     * @param string $consignmentOrderReturnType
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $statusId
     * @param int $warehouseReceivingStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getConsignmentOrderReturnByFilters(
        string $consignmentOrderReturnType = 'order',
        int $countryId = 0,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $statusId = 0,
        int $warehouseReceivingStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        //Get consignment order return type ID
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_order_and_return_type'
            )
        );

        $orderReturnType = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']->pluck('id','title')->toArray()
        );

        $typeCode = $this->consignmentOrderReturnTypeConfigCodes[$consignmentOrderReturnType];

        $typeId = $orderReturnType[$typeCode];

        $data = $this->consignmentOrderReturnObj
            ->with(['stockist.country', 'consignmentOrderReturnType',
                'status', 'actionBy', 'warehouseReceiveStatus',
                'createdBy'
            ])
            ->where('consignments_orders_returns.type_id', $typeId);


        $data = $data
            ->join('stockists', function ($join)
            use ($countryId){
                $join->on('consignments_orders_returns.stockist_id', '=', 'stockists.id')
                    ->where(function ($consignmentQuery) use ($countryId) {
                        $consignmentQuery->where('stockists.country_id', $countryId);
                    });

                //rnp check if user stockist
                if ($this->isUser('stockist')) {
                    $join->where('stockists.stockist_user_id', Auth::id());
                }elseif( $this->isUser('stockist_staff')){
                    $join->where('stockists.stockist_user_id', $this->getStockistParentUser());
                }
            });

        if ($text != '') {
            $data = $data
                ->join('users', function ($join){
                    $join->on('users.id', '=', 'stockists.member_user_id');
                })
                ->join('members', function ($join){
                    $join->on('users.id', '=', 'members.user_id');
                })
                ->where(function ($textQuery) use($text) {
                    $textQuery->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('members.name', 'like','%' . $text . '%')
                        ->orWhere('members.ic_passport_number', 'like','%' . $text . '%')
                        ->orWhere('stockists.stockist_number', 'like','%' . $text . '%')
                        ->orWhere('stockists.name', 'like','%' . $text . '%')
                        ->orWhere('consignments_orders_returns.document_number', 'like','%' . $text . '%');
                });
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('consignments_orders_returns.transaction_date','>=', $dateFrom)
                ->where('consignments_orders_returns.transaction_date','<=', $dateTo);
        }

        //check consignment order return status if given
        if($statusId > 0){
            $data = $data->where('consignments_orders_returns.status_id', $statusId);
        }

        //check consignment return warehouse receiving status if given
        if($warehouseReceivingStatusId > 0){
            $data = $data->where('consignments_orders_returns.status_id', $statusId);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('consignments_orders_returns.*');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get consignment order return detail by given ID
     *
     * @param int $consignmentOrderReturnId
     * @return mixed
     */
    public function consignmentOrderReturnDetails(int $consignmentOrderReturnId)
    {
        $consignmentOrderReturn = $this->consignmentOrderReturnObj
            ->with([
                'consignmentOrderReturnType', 'status', 'actionBy',
                'warehouseReceiveStatus', 'createdBy'
            ])
            ->find($consignmentOrderReturnId);

        $stockistData = $this->stockistDetails($consignmentOrderReturn->stockist->stockist_user_id);

        $workflow = $this->workflowRepositoryObj
            ->getTrackingWorkflowDetails($consignmentOrderReturn->workflow_tracking_id);

        return [
            'consignment_order_return' => array_merge(
                $consignmentOrderReturn->toArray(),
                [
                    'stockist_user_id' => $consignmentOrderReturn->stockist->stockist_user_id,
                    'products' => $consignmentOrderReturn->getConsignmentProducts(),
                ]
            ),
            'stockist_data' => $stockistData['stockist_data'],
            'workflow' => $workflow
        ];
    }

    /**
     * Insert consignment order and return record
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentOrderReturn(array $data)
    {
        $consignmentData = $data['consignment_order_return'];

        $type = $consignmentData['type'];

        //Get related master data ID
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_order_and_return_type',
                'consignment_order_status',
                'consignment_return_status'
            )
        );

        $orderReturnType = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']->pluck('id','title')->toArray()
        );

        $orderStatus = array_change_key_case(
            $masterSettingsDatas['consignment_order_status']->pluck('id','title')->toArray()
        );

        $returnStatus = array_change_key_case(
            $masterSettingsDatas['consignment_return_status']->pluck('id','title')->toArray()
        );

        $orderReturnTypeCode = $this->consignmentOrderReturnTypeConfigCodes[$type];

        $orderPendingCode = $this->consignmentOrderStatusConfigCodes['pending'];

        $returnPendingCode = $this->consignmentReturnStatusConfigCodes['pending'];

        $stockistId = $consignmentData['stockist_id'];

        //Get Stockist Details
        $stockist = $this->find($stockistId);

        $countryId = $stockist->country_id;

        $stockLocationId = $stockist->stockistLocation
            ->stockLocations
            ->pluck('id')
            ->toArray();

        $stockLocationId = (isset($stockLocationId[0])) ? $stockLocationId[0] : null;

        $locationArray[] = $stockist->stockistLocation->id;

        $consignmentProducts = [];

        //Form Consignment Product Records
        collect($consignmentData['products'])->each(function ($product)
        use(&$consignmentProducts, $stockistId, $countryId, $locationArray, $type){

            $effectivePrice = optional($this->productRepositoryObj
                ->productEffectivePricing(
                    $countryId,
                    $product['product_id'],
                    $locationArray
                ))
                ->toArray();

            $productDetail = $this->productRepositoryObj->find($product['product_id']);

            $availableQuantitySnapshot = 0;

            if($type == 'return'){
                $stockistProduct = $this->stockistConsignmentProductObj
                    ->where('stockist_id', $stockistId)
                    ->where('product_id', $product['product_id'])
                    ->first();

                $availableQuantitySnapshot = $stockistProduct->available_quantity;
            }

            $consignmentProduct = [
                'product_price_id' => $effectivePrice['id'],
                'available_quantity_snapshot' => $availableQuantitySnapshot,
                'quantity' => $product['quantity'],
                'unit_gmp_price_gst' => floatval($effectivePrice['gmp_price_tax']),
                'unit_nmp_price' => floatval($effectivePrice['nmp_price']),
                'gmp_price_gst' => floatval($effectivePrice['gmp_price_tax']) * floatval($product['quantity']),
                'nmp_price' => floatval($effectivePrice['nmp_price']) * floatval($product['quantity']),
                'product_id' => $productDetail->id,
                'name' => $productDetail->name,
                'sku' => $productDetail->sku,
                'uom' => $productDetail->uom
            ];

            array_push($consignmentProducts, $consignmentProduct);
        });

        //Get Status Id
        $orderReturnStatusId = ($type == 'order') ?
            $orderStatus[$orderPendingCode] :
            $returnStatus[$returnPendingCode];

        //Calculate Total Consignment Amount
        $totalGmpPriceGst = collect($consignmentProducts)->sum('gmp_price_gst');

        $totalNmpPrice = collect($consignmentProducts)->sum('nmp_price');

        //Create Consignment Order Return Record
        $consignmentOrderReturnData = [
            "stockist_id" => $consignmentData['stockist_id'],
            'stock_location_id' => $stockLocationId,
            "type_id" => $orderReturnType[$orderReturnTypeCode],
            "transaction_date" => Carbon::now()->format('Y-m-d'),
            "document_number" => NULL,
            "total_gmp" => $totalGmpPriceGst,
            "total_amount" => $totalNmpPrice,
            "total_tax" => floatval($totalGmpPriceGst) - floatval($totalNmpPrice),
            "remark" => $consignmentData['remark'],
            "status_id" => $orderReturnStatusId,
        ];

        $consignmentOrderReturn = Auth::user()->createdBy($this->consignmentOrderReturnObj)
            ->create($consignmentOrderReturnData);

        //Create Workflow
        $this->createConsignmentWorkflow($consignmentOrderReturn->id, $type);

        //Create Consignment Order Return Product and Product Clone
        collect($consignmentProducts)->each(function ($consignmentProduct)
        use ($consignmentOrderReturn){

            //Create Consignment Order Return Product
            $productData = [
                'consignment_order_return_id' => $consignmentOrderReturn->id,
                'product_price_id' => $consignmentProduct['product_price_id'],
                'available_quantity_snapshot' => $consignmentProduct['available_quantity_snapshot'],
                'quantity' => $consignmentProduct['quantity'],
                'unit_gmp_price_gst' => $consignmentProduct['unit_gmp_price_gst'],
                'unit_nmp_price' => $consignmentProduct['unit_nmp_price'],
                'gmp_price_gst' => $consignmentProduct['gmp_price_gst'],
                'nmp_price' => $consignmentProduct['nmp_price']
            ];

            $consignmentOrderReturnProduct = $this->consignmentOrderReturnProductObj
                ->create($productData);

            //Create Consignment Order Return Product Clone
            $productCloneData = [
                'consignment_order_return_product_id' => $consignmentOrderReturnProduct->id,
                'product_id' => $consignmentProduct['product_id'],
                'name' => $consignmentProduct['name'],
                'sku' => $consignmentProduct['sku'],
                'uom' => $consignmentProduct['uom']
            ];

            $this->consignmentOrderReturnProductCloneObj->create($productCloneData);
        });

        return $this->consignmentOrderReturnDetails($consignmentOrderReturn->id);
    }

    /**
     * validates total product quantity that can be return
     *
     * @param int $stockistId
     * @param int $productId
     * @return mixed
     */
    public function validatesConsignmentReturnProduct(int $stockistId, int $productId)
    {
        //Get Stockist Details
        $stockistDetails = $this->modelObj->find($stockistId);

        //get Product Details
        $productDetail = $this->productObj->find($productId);

        $productPrices = $productDetail->getProductPriceByCountry($stockistDetails->country_id);

        //get stockist product record
        $stockistProduct = $this->stockistConsignmentProductObj
            ->with('product')
            ->where('stockist_id', $stockistId)
            ->where('product_id', $productId)
            ->first();

        return collect([
            'product_id' => $productDetail->id,
            'product_name' => $productDetail->name,
            'product_sku' => $productDetail->sku,
            'base_price' => $productPrices,
            'available_quantity' => ($stockistProduct) ? $stockistProduct->available_quantity : 0
        ]);
    }

    /**
     * Create consignment transaction record
     *
     * @param int $consignmentId
     * @param string $consignmentType
     * @return mixed
     */
    public function createConsignmentTransaction(int $consignmentId, string $consignmentType)
    {
        //Get Consignment Transaction Details
        $consignmentDetails = (($consignmentType == 'deposit') || ($consignmentType == 'refund')) ?
            $this->consignmentDepositRefundObj->find($consignmentId) :
            $this->consignmentOrderReturnObj->find($consignmentId);

        $stockistDeposit = $stockistCreditLimit = $consignmentRatioProvided = $consignmentStockReturn = NULL;

        $cumulativeDeposit = $cumulativeCreditLimit = $cumulativeConsignedStock =
        $averageConsignmentRatio = $unutilisedCreditLimit = 0;

        //Assign Consignment Data
        if($consignmentType == 'deposit') {

            $stockistDeposit = $consignmentDetails->amount;

            $stockistCreditLimit = $consignmentDetails->credit_limit;

            $consignmentRatioProvided = $consignmentDetails->ratio;

        } else if($consignmentType == 'refund') {

            $stockistDeposit = -1 * abs(floatval($consignmentDetails->amount));

            $stockistCreditLimit = -1 * abs(floatval($consignmentDetails->credit_limit));

            $consignmentRatioProvided = $consignmentDetails->ratio;

        } else if ($consignmentType == 'order') {

            $consignmentStockReturn = $consignmentDetails->total_gmp;

        } else if ($consignmentType == 'return') {

            $consignmentStockReturn = -1 * abs(floatval($consignmentDetails->total_gmp));
        }

        //Get Last Transaction Record
        $lastTransactionDetail = $this->consignmentTransactionObj
            ->where('stockist_id', $consignmentDetails->stockist_id)
            ->orderBy('created_at', 'desc')
            ->first();

        //Get Cumulative Value
        if($lastTransactionDetail){

            $cumulativeDeposit = floatval($lastTransactionDetail->cumulative_deposit);

            $cumulativeCreditLimit = floatval($lastTransactionDetail->cumulative_credit_limit);

            $cumulativeConsignedStock = floatval($lastTransactionDetail->cumulative_consigned_stock);

            $unutilisedCreditLimit = floatval($lastTransactionDetail->unutilised_credit_limit);
        }

        //Calculate Cumulative Deposit
        $cumulativeDeposit += floatval($stockistDeposit);

        //Calculate Cumulative Credit Limit
        $cumulativeCreditLimit += floatval($stockistCreditLimit);

        //Calculate Cumulative Consigned Stock
        $cumulativeConsignedStock += floatval($consignmentStockReturn);

        //Calculate Average Ratio
        $averageConsignmentRatio = floatval($cumulativeConsignedStock) / floatval($cumulativeDeposit);

        //Calculate Unutilised Credit Limit
        $unutilisedCreditLimit += (($consignmentType == 'deposit') || ($consignmentType == 'refund')) ?
            floatval($stockistCreditLimit) : (floatval($consignmentStockReturn) * -1);

        //Save Consignment Transaction Records
        $transactionData = [
            'stockist_id' => $consignmentDetails->stockist_id,
            'mapping_id' => $consignmentId,
            'mapping_model' => (($consignmentType == 'deposit') || ($consignmentType == 'refund')) ?
                'consignments_deposits_refunds' : 'consignments_orders_returns',
            'stockist_deposit' => $stockistDeposit,
            'stockist_credit_limit' => $stockistCreditLimit,
            'consignment_ratio_provided' => $consignmentRatioProvided,
            'consignment_stock_return' => $consignmentStockReturn,
            'cumulative_deposit' => $cumulativeDeposit,
            'cumulative_credit_limit' => $cumulativeCreditLimit,
            'cumulative_consigned_stock' => $cumulativeConsignedStock,
            'average_consignment_ratio' => $averageConsignmentRatio,
            'unutilised_credit_limit' => $unutilisedCreditLimit
        ];

        $this->consignmentTransactionObj->create($transactionData);
    }

    /**
     * Update stockist consignment product quantity
     *
     * @param int $consignmentOrderReturnId
     * @param string $type
     * @return mixed
     */
    public function updateConsignmentProduct(int $consignmentOrderReturnId, string $type)
    {
        //Get Consignment Order Return Detail
        $consignmentDetail = $this->consignmentOrderReturnObj
            ->with('consignmentOrderReturnProduct.consignmentOrderReturnProductClone')
            ->find($consignmentOrderReturnId);

        $stockistId = $consignmentDetail->stockist_id;

        collect($consignmentDetail['consignmentOrderReturnProduct'])->each(function($consignmentProduct)
        use ($stockistId, $type){

            $productId = $consignmentProduct['consignmentOrderReturnProductClone']['product_id'];

            $productQuantity =  $consignmentProduct['quantity'];

            //get stockist product record
            $stockistProduct = $this->stockistConsignmentProductObj
                ->where('stockist_id', $stockistId)
                ->where('product_id', $productId)
                ->first();

            if($stockistProduct){

                $availableQty = $stockistProduct->available_quantity;

                $availableQty = ($type == 'order') ?
                    intval($availableQty) + intval($productQuantity) :
                    intval($availableQty) - intval($productQuantity);

                $stockistProduct->update([
                    'available_quantity' => $availableQty
                ]);

            } else {

                //Only consignment order product will be inserted
                if($type == 'order'){

                    $stockistConsignmentProduct = [
                        'stockist_id' => $stockistId,
                        'product_id' => $productId,
                        'available_quantity' => $productQuantity
                    ];

                    $this->stockistConsignmentProductObj->create($stockistConsignmentProduct);
                }
            }
        });
    }

    /**
     * Update stockist consignment return product quantity during approve session
     *
     * @param array $consignmentReturnDetail
     */
    public function updateConsignmentReturnProductQuantity(array $consignmentReturnDetail)
    {
        $consignmentReturnId = $consignmentReturnDetail['id'];

        $consignmentReturnProducts = $consignmentReturnDetail['products'];

        collect($consignmentReturnProducts)->each(function($product){

            $returnProductDetail = $this->consignmentOrderReturnProductObj->find($product['id']);

            $availableQuantitySnapshot = $returnProductDetail->available_quantity_snapshot;

            if(intval($product['quantity']) <= intval($availableQuantitySnapshot)){

                $updateData = [
                    'quantity' => $product['quantity'],
                    'gmp_price_gst' => floatval($returnProductDetail->unit_gmp_price_gst) *
                        floatval($product['quantity']),
                    'nmp_price' => floatval($returnProductDetail->unit_nmp_price) *
                        floatval($product['quantity']),
                ];

                $returnProductDetail->update($updateData);
            }
        });

        //Update Consignment Return Total Amount
        $consignmentReturnRecord = $this->consignmentOrderReturnObj->find($consignmentReturnId);

        $totalGmpPriceGst = $this->consignmentOrderReturnProductObj
            ->where('consignment_order_return_id', $consignmentReturnId)
            ->sum('gmp_price_gst');

        $totalNmpPrice = $this->consignmentOrderReturnProductObj
            ->where('consignment_order_return_id', $consignmentReturnId)
            ->sum('nmp_price');

        $updateData = [
            "total_gmp" => $totalGmpPriceGst,
            "total_amount" => $totalNmpPrice,
            "total_tax" => floatval($totalGmpPriceGst) - floatval($totalNmpPrice),
        ];

        $consignmentReturnRecord->update($updateData);

    }

    /**
     * get stockist sales daily payment verification list by below parameter
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param bool $excludeZeroBalance
     * @param array $selectedStockistIds
     * @return mixed
     */
    public function getSalesDailyPaymentVerificationLists
    (
        int $countryId = 0,
        $dateFrom = '',
        $dateTo = '',
        bool $excludeZeroBalance = false,
        $selectedStockistIds = array()
    )
    {
        $data = $this->stockistSalePaymentObj
            ->with(['stockist', 'paymentProvider.paymentsMode', 'updatedBy'])
            ->join('stockists', function ($join){
                $join->on('stockists.id', '=', 'stockists_sales_payments.stockist_id');
            })
            ->where('stockists.country_id', $countryId);

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('stockists_sales_payments.transaction_date', '>=', $dateFrom)
                ->where('stockists_sales_payments.transaction_date', '<=', $dateTo);
        }

        if (!empty($selectedStockistIds)){
            $data = $data
                ->whereIn('stockists_sales_payments.stockist_id', $selectedStockistIds);
        }

        if($excludeZeroBalance){
            $data = $data->where('stockists_sales_payments.outstanding_amount', '!=', 0);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data->select('stockists_sales_payments.*')->get();

        collect($data)->map(function ($payments){

            $payments->total_adjustment_amount = $payments->adjustment_amount;

            $payments->adjustment_amount = 0;

            $payments->remarks = "";

            $payments->pay_amount = 0;

            return $payments;
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * batch update stockist outstanding and ar payment balance
     *
     * @param array $datas
     * @return mixed
     */
    public function batchUpdateStockistOutstandingPayment(array $datas)
    {
        collect($datas['stockists_sales_payments'])->each(function($data){

            $stockistSalePaymentId = $data['id'];

            //Retrieve Stockist Sale Payment Details
            $stockistPaymentDetail = $this->stockistSalePaymentObj->find($stockistSalePaymentId);

            //Update Stockist Payment Mode Collected Total
            $stockistSalePaymentData = [
                'paid_amount' => floatval($stockistPaymentDetail->paid_amount) +
                    floatval($data['pay_amount']),
                'outstanding_amount' => floatval($stockistPaymentDetail->outstanding_amount) -
                    floatval($data['pay_amount']) - floatval($data['adjustment_amount']),
                'adjustment_amount' => floatval($stockistPaymentDetail->adjustment_amount) +
                    floatval($data['adjustment_amount']),
                'updated_by' => Auth::id()
            ];

            $stockistPaymentDetail->update($stockistSalePaymentData);

            //Insert Paid Transaction
            $payTransactionData = [
                'stockist_sale_payment_id' => $stockistSalePaymentId,
                'paid_amount' => floatval($data['pay_amount']),
                'adjustment_amount' => floatval($data['adjustment_amount']),
                'remarks' => $data['remarks'],
                'updated_by' => Auth::id()
            ];

            Auth::user()->createdBy($this->stockistSalePaymentTransactionObj)
                ->create($payTransactionData);

            //Get Stockist Deposit Setting
            $stockistDepositSetting = $this->stockistDepositSettingObj
                ->where('stockist_id', $stockistPaymentDetail->stockist_id)
                ->first();

            //Calculate Stockist AR Balance
            $stockistArbalance = floatval($stockistDepositSetting->ar_balance) - floatval($data['pay_amount']);

            //Update Stockist AR Balance
            $stockistDepositSetting->update([
                'ar_balance' => $stockistArbalance
            ]);
        });

        return [
            "result" => true
        ];
    }

    /**
     * get consignment deposit and consignment refund for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentDepositAndRefund()
    {
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_status',
                'consignment_deposit_and_refund_type'
            )
        );

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $depositRefundType = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']
                ->pluck('id', 'title')
                ->toArray()
        );

        $depositTypeId = $depositRefundType[$this->consignmentDepositRefundTypeConfigCodes['deposit']];

        $refundTypeId = $depositRefundType[$this->consignmentDepositRefundTypeConfigCodes['refund']];

        $pendingStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['pending']];

        $rejectedStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['rejected']];

        $approvedStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['approved']];

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->consignmentDepositRefundObj
            ->where('yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where(function ($query) use ($depositTypeId, $refundTypeId, $pendingStatusId, $rejectedStatusId, $approvedStatusId) {
                $query->where(function ($depositQuery) use ($depositTypeId, $pendingStatusId, $rejectedStatusId, $approvedStatusId) {
                    $depositQuery->where('type_id', $depositTypeId)
                        ->whereIn('status_id', [$pendingStatusId, $rejectedStatusId, $approvedStatusId]);
                })->orWhere(function ($refundQuery) use ($refundTypeId, $approvedStatusId) {
                    $refundQuery->where('type_id', $refundTypeId)
                        ->where('status_id', $approvedStatusId);
                });
            })
            ->whereIn('stockist_id', $stockistIdArray)
            ->select('id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get consignment deposit (rejected)
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentDepositReject()
    {
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_status',
                'consignment_deposit_and_refund_type'
            )
        );

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $depositRefundType = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']
                ->pluck('id', 'title')
                ->toArray()
        );

        $depositTypeId = $depositRefundType[$this->consignmentDepositRefundTypeConfigCodes['deposit']];

        $rejectedStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['rejected']];

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->consignmentDepositRefundObj
            ->where('yy_reject_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('type_id', $depositTypeId)
            ->where('status_id', $rejectedStatusId)
            ->whereIn('stockist_id', $stockistIdArray)
            ->select('id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get consignment order for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentOrder()
    {
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_order_status',
                'consignment_order_and_return_type'
            )
        );

        $orderStatus = array_change_key_case(
            $masterSettingsDatas['consignment_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $orderReturnType = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']
                ->pluck('id', 'title')
                ->toArray()
        );

        $orderTypeCode = $this->consignmentOrderReturnTypeConfigCodes['order'];

        $approvedStatusCode = $this->consignmentOrderStatusConfigCodes['approved'];

        $orderTypeId = $orderReturnType[$orderTypeCode];

        $approvedStatusId = $orderStatus[$approvedStatusCode];

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->consignmentOrderReturnObj
            ->where('yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('status_id', $approvedStatusId)
            ->where('type_id', $orderTypeId)
            ->whereIn('stockist_id', $stockistIdArray)
            ->whereExists(function ($query) {
                $query->select('consignments_orders_returns_products.id')
                    ->from('consignments_orders_returns_products')
                    ->join('consignments_orders_returns_products_clone', function ($join) {
                        $join->on('consignments_orders_returns_products_clone.consignment_order_return_product_id', '=', 'consignments_orders_returns_products.id');
                    })
                    ->join('products', function ($join) {
                        $join->on('consignments_orders_returns_products_clone.product_id', '=', 'products.id');
                    })
                    ->whereRaw('consignments_orders_returns_products.consignment_order_return_id = consignments_orders_returns.id')
                    ->where('products.inventorize', 1);
            })
            ->select('id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get consignment return for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentReturn()
    {
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_return_status',
                'consignment_order_and_return_type'
            )
        );

        $returnStatus = array_change_key_case(
            $masterSettingsDatas['consignment_return_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $orderReturnType = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']
                ->pluck('id', 'title')
                ->toArray()
        );

        $returnTypeCode = $this->consignmentOrderReturnTypeConfigCodes['return'];

        $verifiedStatusCode = $this->consignmentReturnStatusConfigCodes['verified'];

        $returnTypeId = $orderReturnType[$returnTypeCode];

        $verifiedStatusId = $returnStatus[$verifiedStatusCode];

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->consignmentOrderReturnObj
            ->where('yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('status_id', $verifiedStatusId)
            ->where('type_id', $returnTypeId)
            ->whereIn('stockist_id', $stockistIdArray)
            ->whereExists(function ($query) {
                $query->select('consignments_orders_returns_products.id')
                    ->from('consignments_orders_returns_products')
                    ->join('consignments_orders_returns_products_clone', function ($join) {
                        $join->on('consignments_orders_returns_products_clone.consignment_order_return_product_id', '=', 'consignments_orders_returns_products.id');
                    })
                    ->join('products', function ($join) {
                        $join->on('consignments_orders_returns_products_clone.product_id', '=', 'products.id');
                    })
                    ->whereRaw('consignments_orders_returns_products.consignment_order_return_id = consignments_orders_returns.id')
                    ->where('products.inventorize', 1);
            })
            ->select('id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist payment for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPayment()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->stockistSalePaymentTransactionObj
            ->join('stockists_sales_payments', function ($join) {
                $join->on('stockists_sales_payments.id', '=', 'stockists_sales_payments_transactions.stockist_sale_payment_id');
            })
            ->where('stockists_sales_payments_transactions.yy_payment_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('stockists_sales_payments_transactions.paid_amount', '<>', 0)
            ->whereIn('stockists_sales_payments.stockist_id', $stockistIdArray)
            ->select('stockists_sales_payments_transactions.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist payment adjustment for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPaymentAdjustment()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $stockistIdArray = $this->modelObj
            ->whereIn('country_id', $countryIdArray)
            ->pluck('id')
            ->toArray();

        $data = $this->stockistSalePaymentTransactionObj
            ->join('stockists_sales_payments', function ($join) {
                $join->on('stockists_sales_payments.id', '=', 'stockists_sales_payments_transactions.stockist_sale_payment_id');
            })
            ->where('stockists_sales_payments_transactions.yy_adjustment_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('stockists_sales_payments_transactions.adjustment_amount','<>', 0)
            ->whereIn('stockists_sales_payments.stockist_id', $stockistIdArray)
            ->select('stockists_sales_payments_transactions.id')
            ->distinct()
            ->get();

        return $data;
    }

    /*
     * get stockist outstanding summary by below parameter
     *
     * @param int $countryId
     * @param bool $excludeZeroBalance
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getStockistOutstandingSummary(
        int $countryId = 0,
        bool $excludeZeroBalance = true,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['country', 'member.country',
                'createdBy', 'status', 'stockistType',
                'depositSetting'
            ])
            ->join('stockists_deposits_settings', function ($join){
                $join->on('stockists.id', '=', 'stockists_deposits_settings.stockist_id');
            })
            ->where('stockists.country_id', $countryId);

        if($excludeZeroBalance){
            $data = $data->where('stockists_deposits_settings.ar_balance', '!=', 0);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data->select('stockists.*', 'stockists_deposits_settings.ar_balance');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data = ($paginate > 0) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * download stockist daily collection report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param string $collectionDateFrom
     * @param string $collectionDateTo
     * @param int $userId
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadDailyCollectionReport(
        int $countryId = 0,
        array $locations = array(),
        $collectionDateFrom = '',
        $collectionDateTo = '',
        int $userId = 0
    )
    {
        $paymentModeSettingQuery = $this->paymentModeSettingObj
            ->join('payments_modes_providers', 'payments_modes_providers.id', '=', 'payments_modes_settings.payment_mode_provider_id')
            ->where('country_id', '=', $countryId)
            ->where('active', '=', 1);

        $paymentMode = $paymentModeSettingQuery
            ->distinct()
            ->pluck('payments_modes_providers.name')
            ->toArray();

        $autoPaymentProviderIds = $paymentModeSettingQuery
            ->where('payments_modes_providers.is_stockist_payment_verification', 0)
            ->distinct()
            ->pluck('payments_modes_providers.id');

        //get stockist location type detail
        $stockistLocationType = $this->locationTypesObj
            ->where('code', $this->locationTypeCodeConfigCodes['stockist'])
            ->first();

        $stockistPayment = $this->stockistSalePaymentTransactionObj
            ->join('stockists_sales_payments', 'stockists_sales_payments.id', '=', 'stockists_sales_payments_transactions.stockist_sale_payment_id')
            ->join('stockists', 'stockists.id', '=', 'stockists_sales_payments.stockist_id')
            ->where('stockists_sales_payments_transactions.created_at', '>=', date('Y-m-d  H:i:s',strtotime($collectionDateFrom.' 00:00:00')))
            ->where('stockists_sales_payments_transactions.created_at', '<=', date('Y-m-d  H:i:s',strtotime($collectionDateTo.' 23:59:59')))
            ->where('stockists.country_id', $countryId)
            ->with(['stockistSalePayment.stockist', 'stockistSalePayment.paymentProvider', 'createdBy'])
            ->select('stockists_sales_payments_transactions.*');

        if($userId > 0){
            $stockistPayment = $stockistPayment->where('stockists_sales_payments_transactions.created_by', $userId);
        }

        if(!empty($locations)){

            $locationCode = $this->locationObj
                ->whereIn('id', $locations)
                ->where('location_types_id', $stockistLocationType->id)
                ->pluck('code');

            $stockistPayment = $stockistPayment->whereIn('stockists.stockist_number', $locationCode);
        }

        $stockistPayment = $stockistPayment->get();

        //Retrieve Stockist Sale Auto Payment Transaction
        $stockistSaleAutoPaymentTransactions = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('payments.mapping_id', '=', 'sales.id')
                    ->where(function ($query) {
                        $query
                            ->where('payments.mapping_model', 'sales');
                    });
            })
            ->where(function ($saleSubQuery) use ($locations, $stockistLocationType){

                $saleSubQuery->where('sales.channel_id', $stockistLocationType->id);

                if(!empty($locations)){
                    $saleSubQuery->whereIn('sales.transaction_location_id', $locations);
                }
            })
            ->whereIn('payment_mode_provider_id', $autoPaymentProviderIds)
            ->where('sales.country_id','=', $countryId)
            ->where('payments.created_at','>=', date('Y-m-d  H:i:s',strtotime($collectionDateFrom.'00:00:00')))
            ->where('payments.created_at','<=', date('Y-m-d  H:i:s',strtotime($collectionDateTo.'23:59:59')))
            ->select("payments.*")
            ->get();

        $list = [];

        foreach ($stockistPayment as $payment){

            $row = [];
            $row['locationCode'] = $payment->stockistSalePayment->stockist->stockist_number;
            $row['name'] = $payment->createdBy->name;
            $row['collection_date'] = date_format($payment->created_at,"Y-m-d");
            $row['transaction_date'] = $payment->stockistSalePayment->transaction_date;
            $row['iboId'] = $payment->stockistSalePayment->stockist->stockist_number;
            $row['iboName'] = $payment->stockistSalePayment->stockist->name;
            $row['collection'] = "payment";

            foreach ($paymentMode as $mode){
                $row[$mode] = 0;
            }

            if($payment->paid_amount) {

                $row[$payment->stockistSalePayment->paymentProvider->name] = $payment->paid_amount;

                $row['total'] = $payment->paid_amount;

            } else {
                $row['total'] = 0;
            }

            array_push($list, $row);

            if ($payment->adjustment_amount != 0) {

                $row['collection'] = "adjustment";

                foreach ($paymentMode as $mode)
                {
                    $row[$mode] = 0;
                }

                $row[$payment->stockistSalePayment->paymentProvider->name] = $payment->adjustment_amount;

                $row['total'] = $payment->adjustment_amount;

                array_push($list, $row);
            }
        }

        foreach ($stockistSaleAutoPaymentTransactions as $stockistSalePayment) {

            $sale = $this->saleObj->find($stockistSalePayment->mapping_id);

            $stockist = $this->modelObj
                ->where('stockist_number', $sale->transactionLocation->code)
                ->first();

            $row = [];
            $row['locationCode'] = $sale->transactionLocation->code;
            $row['name'] = $stockistSalePayment->createdBy->name;
            $row['collection_date'] = date_format($stockistSalePayment->created_at,"Y-m-d");
            $row['transaction_date'] = date_format($stockistSalePayment->created_at,"Y-m-d");
            $row['iboId'] = optional($stockist)->stockist_number;
            $row['iboName'] = optional($stockist)->name;
            $row['collection'] = "payment";

            foreach ($paymentMode as $mode){
                $row[$mode] = 0;
            }

            if($stockistSalePayment->amount) {

                $row[$stockistSalePayment->paymentModeProvider->name] = $stockistSalePayment->amount;

                $row['total'] = $stockistSalePayment->amount;

            } else {
                $row['total'] = 0;
            }

            array_push($list, $row);
        }

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = ['Location Code', 'User Name',"Stockist Payment Collection Date", "Document Trans Date", 'IBO ID', 'IBO Name', 'Collection Type'];

        $header = array_merge($header, $paymentMode);

        array_push($header, "Total");

        $col = "A";

        foreach ($header as $value) {

            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            if ($col < "H")
            {
                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(13);
            }
            $col++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension("C")->setWidth(17);
        $spreadsheet->getActiveSheet()->getStyle('C1')->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('D1')->getAlignment()->setWrapText(true);

        //row 2 start inserting data into spreadsheet
        $row = 2;

        foreach ($list as $data) {

            $col = "A";

            $total = 0;

            foreach($data as $attribute)
            {
                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $attribute);

                if ($col >= "H")
                {
                    $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

                    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(13);
                }
                $col++;
            }
            $row++;
        }

        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $cell = "G".$row;

        $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "Total");

        $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

        $col = "H";

        for($i = 0; $i <= count($paymentMode); $i++) {

            $cell = $col.$row;

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "=SUM(".$col."2:".$col."".($row-1).")");

            $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

            $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

            $col++;
        }

        // Output excel file
        $outputPath = Config::get('filesystems.subpath.stockists.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.stockists.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('daily_collection_report') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * download consignment deposit receipt note by consignment deposit refund id
     *
     * @param int $consignmentDepositRefundId
     * @return \Illuminate\Support\Collection|mixed
     * @throws \Mpdf\MpdfException
     */
    public function downloadDepositReceipt(int $consignmentDepositRefundId)
    {
        $consignmentDepositRefund = $this->consignmentDepositRefundObj
            ->find($consignmentDepositRefundId);

        $data = [];

        if (strtoupper($consignmentDepositRefund->consignmentDepositRefundType->title) == "DEPOSIT")
        {
            $data['title'] = "CONSIGNMENT DEPOSIT RECEIPT";
            $data['description'] = "Consignment Deposit";
        }
        else
        {
            $data['title'] = "CONSIGNMENT REFUND NOTE";
            $data['description'] = "Consignment Refund";
        }
        $data['currency'] = $consignmentDepositRefund->stockist->country->currency->code;
        $data['stockist'] = $consignmentDepositRefund->stockist->stockist_number;
        $data['no'] = $consignmentDepositRefund->document_number;
        $data['name'] = $consignmentDepositRefund->stockist->name;
        $data['issuer'] = $consignmentDepositRefund->createdBy->name;
        $data['approver'] = (!empty($consignmentDepositRefund->verified_by)) ?
            $consignmentDepositRefund->verifiedBy->name : '';
        $data['address'] =  $consignmentDepositRefund->stockist->businessAddress? $this->memberAddressHelper->getAddress($consignmentDepositRefund->stockist->businessAddress->addresses, ""): "";
        $data['tel'] = $consignmentDepositRefund->stockist->businessAddress->mobile_1_num;
        $data['date'] = $consignmentDepositRefund->transaction_date;
        $data['total'] = $consignmentDepositRefund->amount;
        $data['remark'] = $consignmentDepositRefund->remark;

        $payments = $consignmentDepositRefund->payments;
        $paymentsSummary = array();
        $payments->each(function($payment) use(&$paymentsSummary){
            if($payment->status == 1){ // only get success one
                $paymentsSummary[] = [
                    'method'=>$payment->paymentModeProvider->name,
                    'total' => $payment->amount
                ];
            }
        });

        $data['payments'] = $paymentsSummary;

        $view = 'invoices.consignment_deposit_receipt_note';

        $html = \View::make($view)
            ->with('data', $data)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 20];
        $mpdf = new PdfCreator($config);

        $mpdf->WriteHTML($html);

        $absoluteUrlPath = Config::get('filesystems.subpath.stockists.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('consignment_deposit_receipt_note') . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * download consignment product list based on stockist ID
     *
     * @param int $stockistId
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadConsignmentProduct(int $stockistId)
    {

        $stockist = $this->modelObj->find($stockistId);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->setActiveSheetIndex(0)->setCellValue("A1", "Stockist Code");
        $spreadsheet->setActiveSheetIndex(0)->setCellValue("B1", $stockist->stockist_number);

        $styleArray = [
            'alignment' => [
                'horizontal' => 'left',
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('B1')->applyFromArray($styleArray);

        // Creating header
        $header = ['SKU', 'Name', 'Quantity'];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."3";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        $styleArray = [
            'font' => [
                'bold' => true,
            ]
        ];

        $spreadsheet->getActiveSheet()->getStyle('A3:C3')->applyFromArray($styleArray);

        $list = $this->stockistConsignmentProductObj
            ->where('stockist_id', '=', $stockistId)
            ->with(['product'])
            ->get();

        $row = 4;

        foreach ($list as $data)
        {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, $data->product->sku);

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("B".$row, $data->product->name);

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("C".$row, $data->available_quantity);

            $row++;
        }

        // Output excel file

        $outputPath = Config::get('filesystems.subpath.stockists.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.stockists.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('stockist_consignment_product') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }
}