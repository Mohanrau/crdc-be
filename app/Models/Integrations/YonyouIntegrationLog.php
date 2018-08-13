<?php
namespace App\Models\Integrations;

use Illuminate\Database\Eloquent\Model;

class YonyouIntegrationLog extends Model
{
    protected $table = 'yy_integration_logs';

    protected $fillable = [
        'job_type',
        'mapping_model',
        'mapping_id',
        'request_data',
        'response_data',
        'return_code'
    ];
}
