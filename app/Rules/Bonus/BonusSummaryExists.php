<?php
namespace App\Rules\Bonus;

use Illuminate\Contracts\Validation\Rule;
use App\Models\{
    Bonus\BonusSummary,
    Users\User,
    General\CWSchedule
};

class BonusSummaryExists implements Rule
{
    protected $cwId, $error, $name, $cwName;

    /**
     * Create a new rule instance.
     *
     * @param  int  $cwId
     * @return void
     */
    public function __construct($cwId)
    {
        $this->cwId = $cwId;
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
        $cw = CWSchedule::whereId($this->cwId)->first();

        if (empty($cw))
        {
            return true;
        }

        $user = User::whereId($value)->first();

        if (empty($user))
        {
            $this->error = 1;

            return false;
        }

        if (BonusSummary::where('user_id', '=', $user->id)->where('cw_id', '=', $cw->id)->count() > 0)
        {
           return true;
        }

        $this->name = $user->name;

        $this->cwName = $cw->cw_name;

        $this->error = 2;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->error == 1)
        {
            return trans('validation.not_in', ['attribute' => "user"]);
        }
        else
        {
            return trans('validation.bonus_summary_not_found', ['user' => $this->name, 'cw' => $this->cwName]);
        }
    }
}
