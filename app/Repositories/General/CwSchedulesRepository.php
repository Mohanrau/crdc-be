<?php
namespace App\Repositories\General;

use App\{
    Interfaces\General\CwSchedulesInterface,
    Models\General\CWSchedule,
    Models\Bonus\EnrollmentRank,
    Models\Bonus\TeamBonusRank,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class CwSchedulesRepository extends BaseRepository implements CwSchedulesInterface
{
    private $enrollmentRankObj, $teamBonusRankObj;

    /**
     * CwSchedules constructor.
     *
     * @param CWSchedule $model
     * @param EnrollmentRank $enrollmentRank
     * @param TeamBonusRank $teamBonusRank
     */
    public function __construct(
        CWSchedule $model,
        EnrollmentRank $enrollmentRank,
        TeamBonusRank $teamBonusRank
    )
    {
        parent::__construct($model);

        $this->enrollmentRankObj = $enrollmentRank;

        $this->teamBonusRankObj = $teamBonusRank;
    }

    /**
     * get one record
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get CwSchedule based on the filter type
     *
     * @param string $filterType
     * @param array $parameter
     * @return mixed|static
     */
    public function getCwSchedulesList(string $filterType, array $parameter = [])
    {
        $cwScheduleQuery = $this->modelObj
            ->where(function ($cwScheduleSubQuery) use ($filterType,$parameter) {
                $curr_date = date('Y-m-d');

                if($filterType == 'past'){

                    $cwScheduleSubQuery->where('date_to', '<', $curr_date);

                } else if($filterType == 'current_past'){

                    $cwScheduleSubQuery->where('date_from', '<=', $curr_date);

                } else if($filterType == 'current_back_date'){

                    $cwScheduleSubQuery->where('date_from', '<=', $curr_date);

                    $cwScheduleSubQuery->where('date_to', '>=', $curr_date);

                    $cwScheduleSubQuery->orWhere('date_to', Date('Y-m-d',strtotime($curr_date . '-1 days')));

                } else if($filterType == 'current'){

                    $cwScheduleSubQuery->where('date_from', '<=', $curr_date);

                    $cwScheduleSubQuery->where('date_to', '>=', $curr_date);

                } else if($filterType == 'next'){

                    $cwScheduleSubQueryData = $this->modelObj
                        ->where('date_from', '>', $curr_date)
                        ->orderBy('cw_name')
                        ->first();

                    $cwScheduleSubQuery->where('id', $cwScheduleSubQueryData->id);

                } else if($filterType == 'future'){

                    $cwScheduleSubQuery->where('date_from', '>', $curr_date);

                } else if($filterType == 'current_future'){

                    $cwScheduleSubQueryData = $this->modelObj
                        ->where('date_from', '<=', $curr_date)
                        ->where('date_to', '>=', $curr_date)
                        ->first();

                    $cwScheduleSubQuery->where('date_from', '>', $curr_date);

                    $cwScheduleSubQuery->orwhere('id', $cwScheduleSubQueryData->id);

                } else if($filterType == 'custom'){

                    $cwScheduleSubQuery->where('cw_name', $parameter['custom_cw_name']);

                } else if(in_array($filterType, array('custom_past', 'custom_current_past', 'custom_future', 'custom_current_future'))){

                    $cwScheduleSubQueryData = $this->modelObj
                        ->where('cw_name', $parameter['custom_cw_name'])
                        ->first();

                    if(in_array($filterType, array('custom_future', 'custom_current_future'))){

                        $cwScheduleSubQuery->where('date_from', '>', $cwScheduleSubQueryData->date_from);

                    } else if(in_array($filterType, array('custom_past', 'custom_current_past'))){

                        $cwScheduleSubQuery->where('date_to', '<', $cwScheduleSubQueryData->date_from);

                    }

                    if(in_array($filterType, array('custom_current_past', 'custom_current_future'))){
                        $cwScheduleSubQuery->orwhere('id', $cwScheduleSubQueryData->id);
                    }
                }
            });

        if(isset($parameter['sort'])){
            $cwScheduleQuery = $cwScheduleQuery->orderBy($parameter['sort'], $parameter['order']);
        }

        $totalRecords = collect([
            'total' => $cwScheduleQuery->count(),
        ]);

        $data = (isset($parameter['limit']) && ($parameter['limit'])) ?
            $cwScheduleQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $cwScheduleQuery->get();

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * Get next number(s) of cw by cw name and number(s)
     *
     * @param string $cwName
     * @param int $numberOf
     * @return mixed
     */
    public function getNextCwByCwName(string $cwName, int $numberOf)
    {
        return $this->getCwSchedulesList(
            'custom_future',
            [
                'custom_cw_name' => $cwName,
                'sort' => 'cw_name',
                'order' => 'asc',
                'limit' => $numberOf,
                'offset' => 0
            ]
        );
    }

    /**
     * get all enrollment ranks records
     *
     * @param array $parameter
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getEnrollmentRanksList(array $parameter)
    {
        $enrollmentQuery = $this->enrollmentRankObj->orderBy($parameter['sort'], $parameter['order']);

        $totalRecords = collect([
            'total' => $enrollmentQuery->count(),
        ]);

        $data = ($parameter['limit']) ? 
            $enrollmentQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $enrollmentQuery->get();

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * get all team bonus ranks records
     *
     * @param array $parameter
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getTeamBonusRanksList(array $parameter)
    {
        $teamBonusQuery = $this->teamBonusRankObj->orderBy($parameter['sort'], $parameter['order']);

        $totalRecords = collect([
            'total' => $teamBonusQuery->count(),
        ]);

        $data = ($parameter['limit']) ? 
            $teamBonusQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $teamBonusQuery->get();

        return $totalRecords->merge(['data' => $data]);
    }
}