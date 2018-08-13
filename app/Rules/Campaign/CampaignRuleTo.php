<?php
namespace App\Rules\Campaign;

use Illuminate\Contracts\Validation\Rule;

class CampaignRuleTo implements Rule
{
    private $fromAttribute, $toAttribute;

    /**
     * Create a new rule instance.
     * 
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;
        
        if (isset($value['from_qualify_rank_order']) && isset($value['to_qualify_rank_order'])) {
            if ($value['to_qualify_rank_order'] != 0 && $value['to_qualify_rank_order'] < $value['from_qualify_rank_order']) {
                $this->fromAttribute = 'from_qualify_rank_order';

                $this->toAttribute = 'to_qualify_rank_order';
                
                $result = false;
            }
        }

        if (isset($value['from_level']) && isset($value['to_level'])) {
            if ($value['to_level'] != 0 && $value['to_level'] < $value['from_level']) {
                $this->fromAttribute = 'from_level';
                
                $this->toAttribute = 'to_level';
                
                $result = false;
            }
        }

        if (isset($value['from_cv']) && isset($value['to_cv'])) {
            if ($value['to_cv'] != 0 && $value['to_cv'] < $value['from_cv']) {
                $this->fromAttribute = 'from_cv';
                
                $this->toAttribute = 'to_cv';
                
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.campaign.invalid-to', [
            "from" => $this->fromAttribute,
            "to" => $this->toAttribute
        ]);
    }
}
