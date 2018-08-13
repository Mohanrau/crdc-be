<?php
namespace App\Repositories\Campaigns;

use App\{
    Interfaces\Campaigns\EsacVoucherTypeInterface,
    Helpers\Traits\AccessControl,
    Models\Campaigns\EsacVoucherType,
    Models\Campaigns\EsacVoucherSubType,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class EsacVoucherTypeRepository extends BaseRepository implements EsacVoucherTypeInterface
{
    use AccessControl;

    private $esacVoucherSubTypeObj;

    /**
     * EsacVoucherTypeRepository constructor.
     *
     * @param EsacVoucherType $model
     * @param EsacVoucherSubType $esacVoucherSubType
     */
    public function __construct(
        EsacVoucherType $model,
        EsacVoucherSubType $esacVoucherSubType
    )
    {
        parent::__construct($model);

        $this->esacVoucherSubTypeObj = $esacVoucherSubType;
    }
    
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param string $name
     * @param string $description
     * @param int $search
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacVoucherTypesByFilters(
        int $countryId,
        string $name = null,
        string $description = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'name',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['country', 'esacVoucherSubTypes']);
        
        if (isset($countryId)) {
            $data = $data
                ->where('esac_voucher_types.country_id', $countryId);
        }
        
        if (isset($name)) {
            $data = $data
                ->where('esac_voucher_types.name', 'like', '%' . $name . '%');
        }
        
        if (isset($description)) {
            $data = $data
                ->where('esac_voucher_types.description', 'like', '%' . $description . '%');
        }
        
        if (isset($searh)) {
            $data = $data
                ->where('esac_voucher_types.name', 'like', '%' . $searh . '%')
                ->orWhere('esac_voucher_types.description', 'like', '%' . $searh . '%');
        }

        if (isset($active)) {
            $data = $data
                ->where('esac_voucher_types.active', $active);
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        
        switch (strtolower($orderBy)) {
            case 'country':
                $data = $data
                    ->select('esac_voucher_types.*')
                    ->join('countries', 'countries.id', '=', 'esac_voucher_types.country_id', 'left outer')
                    ->orderBy('countries.name', $orderMethod);
                break;
            case 'last_modified_by':
                $data = $data
                    ->select('esac_voucher_types.*')
                    ->join('users as created_by_user', 'created_by_user.id', '=', 'esac_voucher_types.created_by', 'left outer')
                    ->join('users as updated_by_user', 'updated_by_user.id', '=', 'esac_voucher_types.updated_by', 'left outer')
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
            $data = $data->map(function ($esacVoucher) {
                $country = $esacVoucher->country;

                $esacVoucherSubTypes = $esacVoucher->esacVoucherSubTypes;

                return [
                    'id' => $esacVoucher->id,
                    'name' => $esacVoucher->name,
                    'description' => $esacVoucher->description,
                    'country_id' => $esacVoucher->country_id,
                    'country' => empty($country) ? null : [
                        'id' => $country->id,
                        'name' => $country->name,
                        'code' => $country->code,
                        'code_iso_2' => $country->code_iso_2
                    ],
                    'esac_voucher_sub_types' => empty($esacVoucherSubTypes) ? [] : 
                        $esacVoucherSubTypes->map(function ($esacVoucherSubType) {
                            return [
                                'id' => $esacVoucherSubType->id,
                                'name' => $esacVoucherSubType->name,
                                'description' => $esacVoucherSubType->description
                            ];
                        })
                ];
            });
        }
        
        return $totalRecords->merge(['data' => $data]);
    }
    
    /**
     * get one esac voucher type by id
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        return $this->modelObj
            ->with(['country', 'esacVoucherSubTypes'])
            ->findOrFail($id);
    }
    
    /**
     * create or update esac voucher type
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $esacVoucherType = null;
        $errorBag = [];
        
        $esacVoucherTypeData = [
            'country_id' => $data['country_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'active' => $data['active']
        ];
        
        if (isset($data['id'])) {
            $esacVoucherType = $this->modelObj->findOrFail($data['id']);
        
            $esacVoucherType->update(array_merge(['updated_by' => Auth::id()], $esacVoucherTypeData));
        }
        else
        {
            $esacVoucherType = Auth::user()
                ->createdBy($this->modelObj)
                ->create($esacVoucherTypeData);
        }
        
        // optionally sync esac voucher sub types
        if (isset($data['esacVoucherSubTypes'])) {
            $esacVoucherSubTypeIdArray = [];
            
            foreach ($data['esacVoucherSubTypes'] as $esacVoucherSubTypeItem)
            {
                if (isset($esacVoucherSubTypeItem['id'])) {
                    array_push($esacVoucherSubTypeIdArray, $esacVoucherSubTypeItem['id']);
                }
            }

            $this->esacVoucherSubTypeObj
                ->where('voucher_type_id', $esacVoucherType['id'])
                ->each(function ($item, $key) use ($data) {
                    if (!in_array($item['id'], $esacVoucherSubTypeIdArray)) {
                        $this->esacVoucherSubTypeObj->destroy($item['id']);
                    }
                });
            
            foreach ($data['esacVoucherSubTypes'] as $esacVoucherSubTypeItem)
            {
                $esacVoucherSubTypeData = [
                    'voucher_type_id' => $esacVoucherType['id'],
                    'name' => $esacVoucherSubTypeItem['name'],
                    'description' => $esacVoucherSubTypeItem['description']
                ];

                if (isset($esacVoucherSubTypeItem['id'])) {
                    $esacVoucherSubType = $this->esacVoucherSubTypeObj-findOrFail($esacVoucherSubTypeItem['id']);
        
                    $esacVoucherSubType->update(array_merge(['updated_by' => Auth::id()], $esacVoucherSubTypeData));
                }
                else {
                    $esacVoucherSubType = Auth::user()
                        ->createdBy($this->esacVoucherSubTypeObj)
                        ->create($esacVoucherSubTypeData);
                }
            }
        }

        return array_merge(['errors' => $errorBag] ,
            $this->show($esacVoucherType['id'])->toArray()
        );
    }
    
    /**
     * delete esac voucher type
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {
        $this->esacVoucherSubTypeObj
            ->where('voucher_type_id', $id)
            ->delete();
        
        $deleteStatus = $this->modelObj
            ->findOrFail($id)
            ->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}