<?php
namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class CountryDynamicContent extends Model
{
    protected $table = 'countries_dynamic_content';

    protected $fillable = [
        'type',
        'country_id',
        'content'
    ];

}
