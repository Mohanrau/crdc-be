<?php
namespace App\Models\Kitting;

use App\Models\Languages\Language;
use Illuminate\Database\Eloquent\Model;

class KittingDescription extends Model
{
    protected $table = 'kitting_descriptions';

    protected $fillable = [
        'language_id',
        'kitting_id',
        'marketing_description',
        'active'
    ];

    /**
     * get language details for a given obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
