<?php
namespace App\Repositories\Currency;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Currency\CurrencyInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\General\CwSchedulesInterface,
    Models\Currency\Currency,
    Models\Currency\CurrencyConversion,
    Repositories\BaseRepository
};
use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\Auth
};

class CurrencyRepository extends BaseRepository implements CurrencyInterface
{
    use ResourceRepository;

    private $currencyConversionObj, $settingRepositoryObj, $cwSchedulesRepositoryObj;

    /**
     * Currency constructor.
     *
     * @param Currency $model
     * @param CurrencyConversion $currencyConversion
     * @param CwSchedulesInterface $cwSchedulesInterface
     */
    public function __construct(
        Currency $model,
        CurrencyConversion $currencyConversion,
        SettingsInterface $settingsInterface,
        CwSchedulesInterface $cwSchedulesInterface
    )
    {
        parent::__construct($model);

        $this->currencyConversionObj = $currencyConversion;

        $this->settingRepositoryObj = $settingsInterface;

        $this->cwSchedulesRepositoryObj = $cwSchedulesInterface;
    }

    /**
     * get specified master with all masterData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * Store a newly created currencies conversion resource
     *
     * @param array $data
     * @return mixed
     */
    public function currenciesConversionsStore(array $data)
    {
        return Auth::user()->createdBy($this->currencyConversionObj)->create($data);
    }

    /**
     * get currency conversion filtered by the following parameters
     *
     * @param int|NULL $fromCurrencyId
     * @param int|NULL $toCurrencyId
     * @param int|NULL $cwId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getCurrenciesConversionsByFilters(
        int $fromCurrencyId = NULL,
        int $toCurrencyId = NULL,
        int $cwId = NULL,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->currencyConversionObj
            ->with(['fromCurrency', 'toCurrency', 'cw']);

        //check fromCurrencyId if given
        if($fromCurrencyId > 0){
            $data = $data->where('from_currency_id', $fromCurrencyId);
        }

        //check toCurrencyId if given
        if($toCurrencyId > 0){
            $data = $data->where('to_currency_id', $toCurrencyId);
        }

        //check cw id if given
        if($cwId > 0){
            $data = $data->where('cw_id', $cwId);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get currency conversion rate through base currency by fromCurrencyId and toCurrencyId
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return decimal
     */
    public function getCurrenciesConversionsRate(int $fromCurrencyId, int $toCurrencyId)
    {
        //Get Base Currency
        $baseCurrencySettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('base_currency'));

        $baseCurrencyCode = $baseCurrencySettings['base_currency'][0]->value;

        $baseCurrencyDetail = $this->modelObj
            ->where('code', $baseCurrencyCode)
            ->active()
            ->first();

        $rate = 0;

        if(!empty($baseCurrencyDetail)){

            if($fromCurrencyId == $baseCurrencyDetail->id || $toCurrencyId == $baseCurrencyDetail->id){

                $rate = $this->retrieveConversionRate($fromCurrencyId, $toCurrencyId);

            } else {

                $fromRate = $this->retrieveConversionRate($fromCurrencyId, $baseCurrencyDetail->id);

                $toRate = $this->retrieveConversionRate($baseCurrencyDetail->id, $toCurrencyId);

                $rate = floatval($fromRate) * floatval($toRate);
            }
        }

        return $rate;
    }

    /**
     * retrieve conversion rate by fromCurrencyId and toCurrencyId
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return decimal
     */
    private function retrieveConversionRate(int $fromCurrencyId, int $toCurrencyId)
    {
        //Get Current CW
        $cwObj = $this->cwSchedulesRepositoryObj
            ->getCwSchedulesList('current',
                ['sort' => 'cw_name', 'order' => 'desc', 'limit' => 0]
            );

        $currentCwObj = $cwObj['data']->first();

        $currentCwId = $currentCwObj['id'];

        $conversionDetail = $this->currencyConversionObj
            ->where('from_currency_id', $fromCurrencyId)
            ->where('to_currency_id', $toCurrencyId)
            ->where('cw_id', $currentCwId)
            ->first();

        return (isset($conversionDetail->rate)) ? $conversionDetail->rate : 0;
    }
}