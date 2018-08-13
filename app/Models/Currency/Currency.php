<?php
namespace App\Models\Currency;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasAudit;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'active'
    ];
}
