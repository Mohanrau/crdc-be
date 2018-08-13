<?php

namespace App\Models\Integrations;

use App\{
    Helpers\Traits\HasAudit
};

use Illuminate\Database\Eloquent\Model;

class Yonyou extends Model
{
    use HasAudit;

    protected $table = 'yy_integration_log';

    protected $fillable = [
        'sale_id',
        'sale_type',
        'request_data',
        'response_result'
    ];
}
