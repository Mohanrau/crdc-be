<?php
namespace App\Http\Controllers\V1\Enrollments;

use App\{Http\Requests\Enrollments\BackOfficeEnrollmentRequest,
    Http\Requests\Enrollments\EnrollmentRequest,
    Interfaces\Enrollments\EnrollmentInterface,
    Http\Controllers\Controller,
    Models\Masters\MasterData,
    Rules\Enrollments\CheckEnrollmentStatus};
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    private $obj;

    /**
     * EnrollmentController constructor.
     *
     * @param EnrollmentInterface $enrollmentInterface
     */
    public function __construct(EnrollmentInterface $enrollmentInterface)
    {
        $this->middleware('auth');

        $this->obj = $enrollmentInterface;
    }

    /**
     * create enrollment member
     *
     * @param EnrollmentRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function create(EnrollmentRequest $request)
    {
        //TODO implement rnp -----------------------------

        return response($this->obj->create($request->all()));
    }

    /**
     * create backOffice Enrollment
     *
     * @param BackOfficeEnrollmentRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createBackOfficeEnrollment(BackOfficeEnrollmentRequest $request)
    {
        return response($this->obj->createBackOfficeEnrollment($request->all()));
    }

    /**
     * get temp data using sms_code
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTempData(Request $request)
    {
        request()->validate([
            'sms_code' => [
                'required',
                'min:7',
                'exists:enrollments_temp_data,sms_code',
                new CheckEnrollmentStatus($this->obj, new MasterData())
            ]
        ]);

        return response($this->obj->getEnrollmentTempData($request->input('sms_code')));
    }

    /**
     * get enrollments types by country id
     *
     * @param int $countryId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEnrollmentTypesByCountry(int $countryId)
    {
        return response($this->obj->getEnrollmentsTypes($countryId));
    }
}
