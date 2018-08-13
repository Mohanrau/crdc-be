<?php
namespace App\Interfaces\Enrollments;

use App\Models\Sales\Sale;

interface EnrollmentInterface
{
    /**
     * create new enrollment
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * create back office enrollment
     *
     * @param array $data
     * @return mixed
     */
    public function createBackOfficeEnrollment(array $data);

    /**
     * process enrollment after sale created and payment done
     *
     * @param Sale $saleId
     * @return mixed
     */
    public function processEnrollment(Sale $saleId);

    /**
     * get enrollment temp data using sms_code
     * @param string $smsCode
     * @return mixed
     */
    public function getEnrollmentTempData(string $smsCode);

    /**
     * get enrollment types by country id
     *
     * @param int $countryId
     * @return mixed
     */
    public function getEnrollmentsTypes(int $countryId);

    /**
     * Update enrollment temp data sales details
     *
     * @param int $sale_id
     */
    public function updateEnrollmentTempSale(int $sale_id);
}