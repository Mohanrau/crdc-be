<?php
namespace App\Rules\General;

use App\Interfaces\Masters\MasterInterface;
use Illuminate\Contracts\Validation\Rule;

class MasterDataIdExists implements Rule
{
    private $masterRepositoryObj, $masterKey, $attribute;

    /**
     * MasterDataIdExists constructor.
     *
     * @param MasterInterface $masterRepository
     * @param string $masterKey
     */
    public function __construct(MasterInterface $masterRepository, string $masterKey)
    {
        $this->masterRepositoryObj = $masterRepository;

        $this->masterKey = $masterKey;
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
        $this->attribute = $attribute;

        $masterDatas = $this->masterRepositoryObj->getMasterDataByKey(array($this->masterKey));

        $validateDatas = $masterDatas[$this->masterKey]->pluck('id');

        return ($validateDatas->contains($value)) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.in', ['attribute' => $this->attribute]);
    }
}