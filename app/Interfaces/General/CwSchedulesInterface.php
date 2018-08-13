<?php
namespace App\Interfaces\General;

interface CwSchedulesInterface
{
    /**
     * get CwSchedule based on the filter type
     *
     * @param string $filterType
     * @param array $parameter
     * @return mixed
     */
    public function getCwSchedulesList(string $filterType, array $parameter = []);

    /**
     * Get next number(s) of cw by cw name and number(s)
     *
     * @param string $cwName
     * @param int $numberOf
     * @return mixed
     */
    public function getNextCwByCwName(string $cwName, int $numberOf);

    /**
     * get enrollment ranks records
     *
     * @param array $parameter
     * @return mixed
     */
    public function getEnrollmentRanksList(array $parameter);

    /**
     * get team bonus ranks records
     *
     * @param array $parameter
     * @return mixed
     */
    public function getTeamBonusRanksList(array $parameter);
}