<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class ForeignBelongTo implements Rule
{
    private
        $primaryAttribute,
        $foreignAttribute,
        $primaryId,
        $foreignObj,
        $foreignId,
        $doCheck
    ;

    /**
     * ForeignBelongTo constructor.
     *
     * @param Model $foreign
     * @param string $primaryAttribute
     * @param int $primaryId
     * @param int|null $foreignId
     * @param bool $doCheck
     */
    public function __construct(
        Model $foreign,
        string $primaryAttribute,
        int $primaryId,
        int $foreignId = null,
        bool $doCheck = true
    )
    {
        $this->primaryAttribute = $primaryAttribute;

        $this->primaryId = $primaryId;

        $this->foreignObj = $foreign;

        $this->foreignId = $foreignId;

        $this->doCheck = $doCheck;
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
        if ($this->doCheck){
            $this->foreignAttribute = $attribute;

            $found = $this->foreignObj
                ->where($this->primaryAttribute , $this->primaryId)
                ->where(
                    'id',
                    is_null($this->foreignId) ?  $value : $this->foreignId
                )
                ->count();

            if (!! $found)
                return true;

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.foreign.not_belong', [
            'foreign' => $this->foreignAttribute,
            'primary' => $this->primaryAttribute
        ]);
    }
}
