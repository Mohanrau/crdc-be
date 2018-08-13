<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;

class CreditNote extends Model
{
    use HasAudit;

    protected $table = 'credit_notes';

    protected $fillable = [
        'sale_id',
        'mapping_id',
        'mapping_model',
        'credit_note_number',
        'credit_note_date'
    ];

    /**
     * get the sale detail for a given creditNoteObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class,'sale_id');
    }

    /**
     * get saleExchange info for a given creditNoteObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class, 'mapping_id');
    }
}
