<?php
namespace App\Models\Sales;

use App\Models\Sales\Sale;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class SaleCorporateSale extends Model
{
    protected $table = 'sales_corporate_sales';

    protected $fillable = [
        'sale_id',
        'company_name',
        'company_reg_number',
        'company_address',
        'company_email',
        'person_in_charge',
        'contact_country_code_id',
        'contact_number'
    ];

    /**
     * get sale details for a given saleCorporateSaleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * get country details for a given saleCorporateSaleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'contact_country_code_id');
    }
}
