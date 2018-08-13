<?php
namespace App\Rules\Enrollments;

use App\{
    Interfaces\Enrollments\EnrollmentInterface,
    Models\Masters\MasterData
};
use Illuminate\Contracts\Validation\Rule;

class CheckEnrollmentStatus implements Rule
{
    private
        $enrollmentRepositoryObj,
        $masterDataObj,
        $status
    ;

    /**
     * CheckEnrollmentStatus constructor.
     *
     * @param EnrollmentInterface $enrollmentInterface
     * @param MasterData $masterData
     */
    public function __construct(EnrollmentInterface $enrollmentInterface, MasterData $masterData)
    {
        $this->enrollmentRepositoryObj = $enrollmentInterface;

        $this->masterDataObj = $masterData;
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
        $enrollmentData = $this->enrollmentRepositoryObj->getEnrollmentTempData($value);

        $enrollmentStatus = $this->masterDataObj->getIdByTitle(
            config('mappings.enrollment_status')['pending'],
            'enrollment_status');

        $this->status = $this->masterDataObj->find($enrollmentData->status_id)->title;

        if ($enrollmentData->status_id != $enrollmentStatus)
            return false;
        else
            return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.enrollment.cant_resume', [
            'status' => $this->status
        ]);
    }
}
