<?php
namespace App\Models\Payments;

use Illuminate\Database\Eloquent\Model;

class PaymentModeDocumentDetail extends Model
{
    protected $table = 'payments_modes_document_details';

    protected $fillable = [
        'country_id',
        'payment_mode_provider_id',
        'document_data'
    ];


}
