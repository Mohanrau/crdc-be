<?php
namespace App\Http\Controllers\V1\Settings;

use App\{
    Interfaces\Settings\SettingsInterface,
    Http\Controllers\Controller,
    Models\Locations\Country,
    Rules\General\CountryGIROTypesExists
};
use Illuminate\{
    Http\Request,
    Support\Facades\Validator
};

class SettingController extends Controller
{
    private $obj, $countryObj;

    /**
     * SettingController constructor.
     *
     * @param SettingsInterface $settingsInterface
     * @param Country $country
     */
    public function __construct(SettingsInterface $settingsInterface, Country $country)
    {
        $this->middleware('auth');

        $this->obj = $settingsInterface;

        $this->countryObj = $country;
    }

    /**
     * get setting value
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSettingValueByKeys(Request $request)
    {
        request()->validate([
            'keys' => 'required|string|in:payment_mode',
        ]);

        return response($this->obj->getSettingDataByKey($request->input('keys')));
    }

    /**
     * get rounding adjustment setting
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getRoundingAdjustmentSetting(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        return response($this->obj->getRoundingAdjustment($request->input('country_id')));
    }

    /**
     * Get GIRO Type Listing
     *
     * @param int $countryId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getGiroTypeSetting(int $countryId)
    {
        Validator::make([
            "country_id" => $countryId
        ], [
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
                new CountryGIROTypesExists($this->countryObj, $this->obj)
            ],
        ])->validate();

        $countryCode = $this->countryObj->find($countryId)->code_iso_2;

        $giroTypes = collect(json_decode($this->obj->getSettingDataByKey(['giro_type'])['giro_type'][0]->value));

        return response( array('giro_types' => $giroTypes->get($countryCode)) );
    }
}
