<?php
namespace App\Models\Campaigns;

use App\Models\Campaigns\EsacVoucherType;
use App\Helpers\Traits\LastModified;
use Illuminate\Database\Eloquent\Model;

class EsacVoucherSubType extends Model
{
    use LastModified;
    
    protected $table = 'esac_voucher_sub_types';

    protected $appends = ['last_modified_by', 'last_modified_at'];

    protected $fillable = [
        'voucher_type_id',
        'name',
        'description',
        'active',
        'updated_by'
    ];

    /**
     * get esac voucher type for a given EsacVoucherSubTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacVoucherType()
    {
        return $this->belongsTo(EsacVoucherType::class, 'voucher_type_id', 'id');
    }
}