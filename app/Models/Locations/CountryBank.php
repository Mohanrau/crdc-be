<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class CountryBank extends Model
{
    use HasAudit;

    protected $table = 'country_banks';

    protected $fillable = [
        'country_id',
        'name',
        'swift_code',
        'active'
    ];
}
