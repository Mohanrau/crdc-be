<?php
namespace App\Rules\CW;

use App\Interfaces\General\CwSchedulesInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class CwCheck implements Rule
{
    private $cwScheduleObj;

    /**
     * CwCheck constructor.
     *
     * @param CwSchedulesInterface $cwSchedulesInterface
     */
    public function __construct(CwSchedulesInterface $cwSchedulesInterface)
    {
        $this->cwScheduleObj = $cwSchedulesInterface;
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
        $currentCw = $this->cwScheduleObj
            ->getCwSchedulesList('current',
                ['sort' => 'cw_name', 'order' => 'desc', 'limit' => 1, 'offset' => 0]
            );

        $currentCw = $currentCw['data']->first();

        $diffInDays = Carbon::parse($currentCw['date_from'])->diffInDays(Carbon::now());

        if ($diffInDays == 0) {
            $previousCw = $this->cwScheduleObj
                ->getCwSchedulesList('past',
                    ['sort' => 'cw_name', 'order' => 'desc', 'limit' => 1, 'offset' => 0]
                );

            $previousCw = $previousCw['data']->first();

            return $value === $previousCw['id'] || $value === $currentCw['id'];
        }

        return $value === $currentCw['id'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.cw.current_or_previous_cw');
    }
}
