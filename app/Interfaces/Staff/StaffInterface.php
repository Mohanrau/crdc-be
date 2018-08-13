<?php
namespace App\Interfaces\Staff;

interface StaffInterface
{
    /**
     * create new staff
     *
     * @param array $data
     * @return mixed
     */
    public function registerStaff(array $data);

    /**
     * get staff details for a given staffId
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * get staff details for a given staffId
     *
     * @param int $staffId
     * @return mixed
     */
    public function staffDetails(int $staffId);
}