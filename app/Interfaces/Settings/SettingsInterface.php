<?php
namespace App\Interfaces\Settings;

interface SettingsInterface
{
    /**
     * get setting data by setting key
     *
     * @param array $key
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSettingDataByKey(array $key);

    /**
     * get rounding adjustment setting
     *
     * @param int $countryId
     * @return mixed
     */
    public function getRoundingAdjustment(int $countryId);

    /**
     * get sales cancellation cooling off period, buy back policy and buy back percentage for a given countryId
     *
     * @param int $countryId
     * @return array
     */
    public function getSalesCancellationPolicy(int $countryId);

    /**
     * get running number for a given running code, countryId and locationId
     *
     * @param string $code
     * @param int $countryId
     * @param int $locationId
     * @return mixed
     */
    public function getRunningNumber (string $code, int $countryId, int $locationId = 0);
}