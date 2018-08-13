<?php
namespace App\Models\Payments;

use App\{
    Models\Locations\Country,
    Models\Masters\MasterData,
    Models\Users\User,
    Helpers\Traits\HasAudit
};
use Illuminate\Database\Eloquent\Model;

class AeonTransaction extends Model
{
    use HasAudit;

    protected $table = 'aeon_transactions';

    protected $fillable = [
        'country_id',
        'user_id',
        'aeon_number',
        'ic_number',
        'application_date',
        'agent_code',
        'agreement_no',
        'request_amount',
        'approved_amount',
        'application_form_document',
        'ic_document',
        'salary_slip_document',
        'bank_book_document',
        'auto_debit',
        'remarks',
        'pending_remarks',
        'approval_status_id',
        'approval_date',
        'request_file_name',
        'updated_by'
    ];

    /**
     * get country detail for a given aeonTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * get user for a given aeonTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * get approval status for a given aeonTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvalStatus()
    {
        return $this->belongsTo(MasterData::class,'approval_status_id');
    }

}
