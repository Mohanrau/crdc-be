<?php
namespace App\Repositories\Campaigns;

use App\{
    Interfaces\Campaigns\EsacPromotionInterface,
    Helpers\Traits\AccessControl,
    Models\Locations\Country,
    Models\Campaigns\Campaign,
    Models\Campaigns\EsacVoucherType,
    Models\Campaigns\EsacPromotion,
    Models\Campaigns\EsacPromotionVoucherSubType,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class EsacPromotionRepository extends BaseRepository implements EsacPromotionInterface
{
    use AccessControl;

    private $countryObj, 
        $campaignObj, 
        $esacVoucherTypeObj,
        $esacPromotionVoucherSubTypeObj;

    /**
     * EsacPromotionRepository constructor.
     *
     * @param EsacPromotion $model
     * @param Country $country
     * @param Campaign $campaign
     * @param EsacVoucherType $esacVoucherType
     * @param EsacPromotionVoucherSubType $esacPromotionVoucherSubType
     */
    public function __construct(
        EsacPromotion $model,
        Country $country,
        Campaign $campaign,
        EsacVoucherType $esacVoucherType,
        EsacPromotionVoucherSubType $esacPromotionVoucherSubType
    )
    {
        parent::__construct($model);

        $this->countryObj = $country;

        $this->campaignObj = $campaign;
        
        $this->esacVoucherTypeObj = $esacVoucherType;
        
        $this->esacPromotionVoucherSubTypeObj = $esacPromotionVoucherSubType;
    }
    
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $campaignId
     * @param int $taxable
     * @param int $voucherTypeId
     * @param string $entitledBy
     * @param int $maxPurchaseQty
     * @param string $search
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacPromotionsByFilters(
        int $countryId,
        int $campaignId = null,
        int $taxable = null,
        int $voucherTypeId = null,
        string $entitledBy = null,
        int $maxPurchaseQty = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['country', 
                'campaign', 
                'campaign.fromCwSchedule', 
                'campaign.toCwSchedule', 
                'esacVoucherType'
            ]);
        
        if (isset($countryId)) {
            $data = $data
                ->where('esac_promotions.country_id', $countryId);
        }

        if (isset($campaignId)) {
            $data = $data
                ->where('esac_promotions.campaign_id', $campaignId);
        }

        if (isset($taxable)) {
            $data = $data
                ->where('esac_promotions.taxable', $taxable);
        }
        
        if (isset($voucherTypeId)) {
            $data = $data
                ->where('esac_promotions.voucher_type_id', $voucherTypeId);
        }
        
        if (isset($entitledBy)) {
            $data = $data
                ->where('esac_promotions.entitled_by', $entitledBy);
        }

        if (isset($maxPurchaseQty)) {
            $data = $data
                ->where('max_purchase_qty', $maxPurchaseQty);
        }

        if (isset($searh)) {
            $data = $data
                ->where('esac_promotions.taxable', 'like', '%' . $searh . '%');
        }

        if (isset($active)) {
            $data = $data
                ->where('esac_promotions.active', $active);
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        
        switch (strtolower($orderBy)) {
            case 'country':
                $data = $data
                    ->select('esac_promotions.*')
                    ->join('countries', 'countries.id', '=', 'esac_promotions.country_id', 'left outer')
                    ->orderBy('countries.name', $orderMethod);
                break;
            case 'campaign':
                $data = $data
                    ->select('esac_promotions.*')
                    ->join('campaigns', 'campaigns.id', '=', 'esac_promotions.campaign_id', 'left outer')
                    ->orderBy('campaigns.name', $orderMethod);
                break;
            case 'esac_voucher_type':
                $data = $data
                    ->select('esac_promotions.*')
                    ->join('esac_voucher_types', 'esac_voucher_types.id', '=', 'esac_promotions.voucher_type_id', 'left outer')
                    ->orderBy('esac_voucher_types.name', $orderMethod);
                break;
            case 'last_modified_by':
                $data = $data
                    ->select('esac_promotions.*')
                    ->join('users as created_by_user', 'created_by_user.id', '=', 'esac_promotions.created_by', 'left outer')
                    ->join('users as updated_by_user', 'updated_by_user.id', '=', 'esac_promotions.updated_by', 'left outer')
                    ->orderByRaw('COALESCE(updated_by_user.name, created_by_user.name) ' . $orderMethod);
                break;
            case 'last_modified_at':
                $data = $data->orderByRaw('COALESCE(updated_at, created_at) ' . $orderMethod);
                break;
            default:
                $data = $data->orderBy($orderBy, $orderMethod);
                break;
        }
        
        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        if (!($this->isUser('root') || $this->isUser('back_office'))) {
            $data = $data->map(function ($esacPromotion) {
                $country = $esacPromotion->country;
                $campaign = $esacPromotion->campaign;
                $esacVoucherType = $esacPromotion->esacVoucherType;
                return [
                    'id' => $esacPromotion->id,
                    'taxable' => $esacPromotion->taxable,
                    'entitled_by' => $esacPromotion->entitled_by,
                    'max_purchase_qty' => $esacPromotion->max_purchase_qty,
                    'country_id' => $esacPromotion->country_id,
                    'campaign_id' => $esacPromotion->campaign_id,
                    'voucher_type_id' => $esacPromotion->voucher_type_id,
                    'country' => empty($country) ? null : [
                        'id' => $country->id,
                        'name' => $country->name,
                        'code' => $country->code,
                        'code_iso_2' => $country->code_iso_2
                    ],
                    'campaign' => empty($campaign) ? null : [
                        "id" => $campaign->id,
                        'name' => $campaign->name,
                        'report_group' => $campaign->report_group,
                        'country_id' => $campaign->country_id,
                        'from_cw_schedule_id' => $campaign->from_cw_schedule_id,
                        'to_cw_schedule_id' => $campaign->to_cw_schedule_id,
                        'from_cw_schedule' => empty($campaign->fromCwSchedule) ? null : [
                            'id' => $campaign->fromCwSchedule->id,
                            'cw_name' => $campaign->fromCwSchedule->cw_name,
                            'date_from' => $campaign->fromCwSchedule->date_from,
                            'date_to' => $campaign->fromCwSchedule->date_to,
                        ],
                        'to_cw_schedule' => empty($campaign->toCwSchedule) ? null : [
                            'id' => $campaign->toCwSchedule->id,
                            'cw_name' => $campaign->toCwSchedule->cw_name,
                            'date_from' => $campaign->toCwSchedule->date_from,
                            'date_to' => $campaign->toCwSchedule->date_to,
                        ]
                    ],
                    'esac_voucher_type' => empty($esacVoucherType) ? null : [
                        'id' => $esacVoucherType->id,
                        'name' => $esacVoucherType->name,
                        'description' => $esacVoucherType->description
                    ]
                ];
            });
        }
        
        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * get one esac promotion by id with additional data
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id)
    {
        $data = $this->modelObj
            ->with([
                'esacPromotionProductCategories', 
                'esacPromotionExceptionProducts', 
                'esacPromotionExceptionKittings', 
                'esacPromotionProducts', 
                'esacPromotionKittings', 
                'esacPromotionVoucherSubTypes',
                'campaign.fromCwSchedule',
                'campaign.toCwSchedule'
            ])
            ->findOrFail($id);
        
        $data->product_categories = $data->esacPromotionProductCategories()->pluck('product_category_id')->toArray();
        
        $data->exception_products = $data->esacPromotionExceptionProducts()->pluck('product_id')->toArray();
        
        $data->exception_kittings = $data->esacPromotionExceptionKittings()->pluck('kitting_id')->toArray();
        
        $data->products = $data->esacPromotionProducts()->pluck('product_id')->toArray();
        
        $data->kittings = $data->esacPromotionKittings()->pluck('kitting_id')->toArray();

        return $data;
    }
    
    /**
     * create or update esac promotion
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $esacPromotion = null;
        $errorBag = [];
        
        $esacPromotionData = [
            'country_id' => $data['country_id'],
            'campaign_id' => $data['campaign_id'],
            'taxable' => $data['taxable'],
            'voucher_type_id' => $data['voucher_type_id'],
            'entitled_by' => $data['entitled_by'],
            'max_purchase_qty' => $data['max_purchase_qty'],
            'active' => $data['active']
        ];
        
        if (isset($data['id'])) {
            $esacPromotion = $this->modelObj->findOrFail($data['id']);
        
            $esacPromotion->update(array_merge(['updated_by' => Auth::id()], $esacPromotionData));
        }
        else
        {
            $esacPromotion = Auth::user()
                ->createdBy($this->modelObj)
                ->create($esacPromotionData);
        }
        
        // Sync Product Category
        $esacPromotion->esacPromotionProductCategories()
            ->sync($data['product_categories']);
        
        // Sync Exception Product
        $esacPromotion->esacPromotionExceptionProducts()
            ->sync($data['exception_products']);

        // Sync Exception Kitting
        $esacPromotion->esacPromotionExceptionKittings()
            ->sync($data['exception_kittings']);

        // Sync Product
        $esacPromotion->esacPromotionProducts()
            ->sync($data['products']);

        // Sync Kitting
        $esacPromotion->esacPromotionKittings()
            ->sync($data['kittings']);

        // Sync Sub Types
        $oldPromotionVoucherSubTypes = $this->esacPromotionVoucherSubTypeObj
            ->where('promotion_id', $esacPromotion['id'])
            ->get();

        $newPromotionVoucherSubTypes = collect($data['esac_promotion_voucher_sub_types']);

        foreach ($oldPromotionVoucherSubTypes as $oldPromotionVoucherSubType) {
            $recordCount = $newPromotionVoucherSubTypes
                ->where('voucher_sub_type_id', $oldPromotionVoucherSubType['voucher_sub_type_id'])
                ->count();

            if ($recordCount <= 0) {
                $this->esacPromotionVoucherSubTypeObj
                    ->where('promotion_id', $esacPromotion['id'])
                    ->where('voucher_sub_type_id', $oldPromotionVoucherSubType['voucher_sub_type_id'])
                    ->delete();
            }
        }

        foreach ($data['esac_promotion_voucher_sub_types'] as $esacPromotionVoucherSubTypeItem) {
            $esacPromotionVoucherSubType = $this->esacPromotionVoucherSubTypeObj
                ->where('promotion_id', $esacPromotion['id'])
                ->where('voucher_sub_type_id', $esacPromotionVoucherSubTypeItem['voucher_sub_type_id'])
                ->first();

            $esacPromotionVoucherSubTypeData = [
                'promotion_id' => $esacPromotion['id'],
                'voucher_sub_type_id' => $esacPromotionVoucherSubTypeItem['voucher_sub_type_id'],
                'voucher_period_id' => $esacPromotionVoucherSubTypeItem['voucher_period_id'],
                'voucher_amount' => $esacPromotionVoucherSubTypeItem['voucher_amount'],
                'min_purchase_amount' => $esacPromotionVoucherSubTypeItem['min_purchase_amount']
            ];

            if (empty($esacPromotionVoucherSubType)) {
                $this->esacPromotionVoucherSubTypeObj
                    ->create($esacPromotionVoucherSubTypeData);
            }
            else {
                $this->esacPromotionVoucherSubTypeObj
                    ->where('promotion_id', $esacPromotion['id'])
                    ->where('voucher_sub_type_id', $esacPromotionVoucherSubTypeItem['voucher_sub_type_id'])
                    ->update($esacPromotionVoucherSubTypeData);
            }
        }

        return array_merge(['errors' => $errorBag] ,
            $this->show($esacPromotion['id'])->toArray()
        );
    }
    
    /**
     * delete esac promotion
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {   
        $esacPromotion = $this->modelObj
            ->findOrFail($id);

        $esacPromotion->esacPromotionProductCategories()
            ->detach();

        $esacPromotion->esacPromotionExceptionProducts()
            ->detach();

        $esacPromotion->esacPromotionExceptionKittings()
            ->detach();

        $esacPromotion->esacPromotionProducts()
            ->detach();

        $esacPromotion->esacPromotionKittings()
            ->detach();

        $this->esacPromotionVoucherSubTypeObj
            ->where('promotion_id', $id)
            ->delete();

        $deleteStatus = $esacPromotion
            ->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}