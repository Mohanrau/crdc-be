<?php
namespace App\Repositories\Campaigns;

use App\{
    Interfaces\Campaigns\EsacVoucherSubTypeInterface,
    Helpers\Traits\AccessControl,
    Models\Campaigns\EsacVoucherType,
    Models\Campaigns\EsacVoucherSubType,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class EsacVoucherSubTypeRepository extends BaseRepository implements EsacVoucherSubTypeInterface
{
    use AccessControl;

    private $esacVoucherTypeObj;
    
    /**
     * EsacVoucherSubTypeRepository constructor.
     *
     * @param EsacVoucherSubType $model
     * @param EsacVoucherType $esacVoucherType
     */
    public function __construct(
        EsacVoucherSubType $model, 
        EsacVoucherType $esacVoucherType
    )
    {
        parent::__construct($model);

        $this->esacVoucherTypeObj = $esacVoucherType;
    }

    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int|null $voucherTypeId
     * @param string|null $name
     * @param string|null $description
     * @param string|null $search
     * @param int|null $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getEsacVoucherSubTypesByFilters(
        int $countryId,
        int $voucherTypeId = null,
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
            ->with(['esacVoucherType']);
        
        if (isset($countryId)) {
            $esacVoucherTypeIndex = $this->esacVoucherTypeObj
                ->where('country_id', $countryId)
                ->pluck('id')
                ->toArray();

            $data = $data
                ->whereIn('esac_voucher_sub_types.voucher_type_id', $esacVoucherTypeIndex);
        }

        if (isset($voucherTypeId)) {
            $data = $data
                ->where('esac_voucher_sub_types.voucher_type_id', $voucherTypeId);
        }

        if (isset($name)) {
            $data = $data
                ->where('esac_voucher_sub_types.name', 'like', '%' . $name . '%');
        }
        
        if (isset($description)) {
            $data = $data
                ->where('esac_voucher_sub_types.description', 'like', '%' . $description . '%');
        }
        
        if (isset($searh)) {
            $data = $data
                ->where('esac_voucher_types.name', 'like', '%' . $searh . '%')
                ->orWhere('esac_voucher_types.description', 'like', '%' . $searh . '%');
        }
        
        if (isset($active)) {
            $data = $data
                ->where('esac_voucher_sub_types.active', $active);
        }
        
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        switch (strtolower($orderBy)) {
            case 'country':
                $data = $data
                    ->select('esac_voucher_sub_types.*')
                    ->join('countries', 'countries.id', '=', 'esac_voucher_sub_types.country_id', 'left outer')
                    ->orderBy('countries.name', $orderMethod);
                break;
            case 'esac_voucher_type':
                $data = $data
                    ->select('esac_voucher_sub_types.*')
                    ->join('esac_voucher_types', 'esac_voucher_types.id', '=', 'esac_voucher_sub_types.voucher_type_id', 'left outer')
                    ->orderBy('esac_voucher_types.name', $orderMethod);
                break;
            case 'last_modified_by':
                $data = $data
                    ->select('esac_voucher_sub_types.*')
                    ->join('users as created_by_user', 'created_by_user.id', '=', 'esac_voucher_sub_types.created_by', 'left outer')
                    ->join('users as updated_by_user', 'updated_by_user.id', '=', 'esac_voucher_sub_types.updated_by', 'left outer')
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
            $data = $data->map(function ($esacVoucherSubType) {
                $esacVoucherType = $esacVoucherSubType->esacVoucherType;

                return [
                    'id' => $esacVoucherSubType->id,
                    'name' => $esacVoucherSubType->name,
                    'description' => $esacVoucherSubType->description,
                    'voucher_type_id' => $esacVoucherSubType->voucher_type_id,
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
     * get one esac voucher sub type by id
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $esacVoucherSubType = $this->modelObj
            ->with(['esacVoucherType'])
            ->findOrFail($id);

        $country = collect(
            [
                'country_id' => $esacVoucherSubType['esacVoucherType']['country_id']
            ]
        );

        return $country
            ->merge($esacVoucherSubType);
    }
    
    /**
     * create or update esac voucher sub type
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $esacVoucherSubType = null;
        $errorBag = [];
        
        $esacVoucherSubTypeData = [
            'voucher_type_id' => $data['voucher_type_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'active' => $data['active']
        ];
        
        if (isset($data['id'])) {
            $esacVoucherSubType = $this->modelObj->findOrFail($data['id']);
        
            $esacVoucherSubType->update(array_merge(['updated_by' => Auth::id()], $esacVoucherSubTypeData));
        }
        else
        {
            $esacVoucherSubType = Auth::user()
                ->createdBy($this->modelObj)
                ->create($esacVoucherSubTypeData);
        }

        return array_merge(['errors' => $errorBag],
            $this->show($esacVoucherSubType['id'])->toArray()
        );
    }

    /**
     * delete esac voucher sub type
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {   
        $deleteStatus = $this->modelObj
            ->findOrFail($id)
            ->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}