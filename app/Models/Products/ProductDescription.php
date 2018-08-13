<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use App\Models\Languages\Language;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasAudit;

    protected $table = 'product_descriptions';

    protected $fillable = [
        'language_id',
        'product_id',
        'marketing_description',
        'benefits',
        'specification',
        'active'
    ];

    /**
     * get language details for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
