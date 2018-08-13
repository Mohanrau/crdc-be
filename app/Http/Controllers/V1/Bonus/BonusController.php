<?php
namespace App\Http\Controllers\V1\Bonus;

use App\{
    Http\Controllers\Controller,
    Interfaces\Bonus\BonusInterface,
    Models\Masters\MasterData,
    Rules\Bonus\BonusSummaryExists,
    Rules\Bonus\YearlyIncomeExists,
    Rules\Stockists\StockistCommissionExists,
    Models\General\CWSchedule,
    Models\Stockists\Stockist,
    Models\Stockists\StockistCommission
};
use Illuminate\{
    Http\Request,
    Validation\Rule
};

class BonusController extends Controller
{
    private $obj,
            $cWSchedule,
            $stockist,
            $stockistCommission;

    /**
     * BonusController constructor.
     *
     * @param BonusInterface $bonusRepository
     */
    public function __construct(
        BonusInterface $bonusRepository, 
        CWSchedule $cWSchedule, 
        Stockist $stockist, 
        StockistCommission $stockistCommission
    )
    {
        // $this->middleware('auth');

        $this->obj = $bonusRepository;

        $this->cWSchedule = $cWSchedule;
        $this->stockist = $stockist;
        $this->stockistCommission = $stockistCommission;
    }

    /**
     * Get CW Bonus report in excel format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCwBonusReport(Request $request)
    {
        request()->validate([
            'cw_id' => 'required|exists:cw_schedules,id',
            'user_ids' => 'present|array',
            'user_ids.*.user_id' => ['integer', new BonusSummaryExists($request->input('cw_id'))]

        ],[],
        [
            'cw_id' => 'commission week',
            'user_ids' => 'user'
        ]);

        $userIds = ($request->has('user_ids') ? $request->input('user_ids') : []);

        return response(
            $this->obj->getCwBonusReport(
                $request->input('cw_id'),
                $userIds
            )
        );
    }

    /**
     * Get User Id array from Request data
     *
     * @param mixed $input
     * @return mixed
     */
    private function getUserIdsInput($input)
    {
        $ids = [];

        if (array_key_exists('user_ids', $input))
        {
            foreach ($input['user_ids'] as $ibo)
            {
                $ids[$ibo['user_id']] = $ibo['user_id'];
            }
        }

        return $ids;
    }

    /**
     * Customise the error message for user ids
     *
     * @param $validator
     * @return \Illuminate\Support\Collection
     */
    private function customiseError($validator)
    {
        $newMessage = collect([]);

        foreach ($validator->errors()->messages()  as $key => $value)
        {
            if (substr($key, 0, 9) == "user_ids.")
            {
                $newMessage->put(substr($key, 9), [$value[0]]);
            }
            else
            {
                $newMessage->put($key, [$value[0]]);
            }
        }

        return $newMessage;
    }

    /**
     * Get Bonus statement report in pdf format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBonusStatement(Request $request)
    {
        // Manipulate input data to return user_ids.<user_id> instead of array index as per requested.
        $input = $request->all();

        $ids = $this->getUserIdsInput($input);
        
        $input['user_ids'] = $ids;

        $validation = \Validator::make($input,
                [
                    'cw_id' => 'required|exists:cw_schedules,id',
                    'user_ids' => 'required',
                    'user_ids.*' => ['integer', new BonusSummaryExists($request->input('cw_id'))]
                ], [],
                [
                    'cw_id' => 'commission week',
                    'user_ids' => 'user'
                ]
            );

        if ($validation->fails())
        {
            return response($this->customiseError($validation),422);
        }

        return response(
            $this->obj->getBonusStatement(
                $request->input('cw_id'),
                $ids
            )
        );
    }

    /**
     * Get Yearly income summary report in excel format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getYearlyIncomeReport(Request $request)
    {
        $type = ['Cp58', 'Lembaga Hasil Dalam Negeri', 'Statement', 'Summary'];

        // Manipulate input data to return user_ids.<user_id> instead of array index as per requested.
        $input = $request->all();

        $masterData = MasterData::whereId($input['report_type'])->first();
        
        if ( !empty($masterData) )
        {
            $input['report_type'] = $masterData->title;
        }
        
        $ids = $this->getUserIdsInput($input);
        
        $input['user_ids'] = $ids;

        $validation = \Validator::make($input,
                [
                    'report_type' => ['required', Rule::in($type)],
                    'year' => 'required|date_format:Y',
                    'country_id' => 'required|integer|exists:countries,id',
                    'user_ids' => 'required',
                    'user_ids.*' => [new YearlyIncomeExists( $request->input('year') ), 'integer', 'exists:users,id']
                ], [],
                [ 
                    'report_type' => 'report type',
                    'country_id' => 'country',
                    'user_ids' => 'user'
                ]
            );

        if ($validation->fails())
        {
            return response( $this->customiseError($validation),422);
        }

        $reportType = $request->input('report_type');

        switch ($reportType) {
            case $type[0]: // report CP58
                
                return response(
                    $this->obj->getCP58Form()
                );
                break;

            case $type[1]: // report LHDN
                
                return response(
                    $this->obj->getLHDNsheet()
                );
                break;

            case $type[2]: // report Statement
                
                return response(
                    $this->obj->getYearlyIncomeStatement()
                );
                break;

            default: // report summary
                
                return response(
                    $this->obj->getYearlyIncomeSummary(
                        $request->input('year'),
                        $request->input('country_id'),
                        $ids
                    )
                );
                break;
        }
    }

    /**
     * Get CP-37f form in pdf format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCp37fForm(Request $request)
    {
        return response(
            $this->obj->getCp37fForm()
        );
    }

    /**
     * Get Self billed invoice in pdf format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSelfBilledInvoice()
    {
        return response(
            $this->obj->getSelfBilledInvoice()
        );
    }

    /**
     * Get Stockist Commission statement in pdf format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getStockistCommissionStatement(Request $request)
    {
        request()->validate([
            'cw_id' => 'required|exists:cw_schedules,id',
            'stockist_id' => [
                'required',
                'exists:stockists,id', 
                new StockistCommissionExists(
                    $request->input('cw_id'),
                    $this->cWSchedule,
                    $this->stockist,
                    $this->stockistCommission
                )
            ]
        ]);

        return response(
            $this->obj->getStockistCommissionStatement(
                $request->input('cw_id'),
                $request->input('stockist_id')
            )
        );
    }

    /**
     * Get Sponsor Tree report in excel format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSponsorTree(Request $request)
    {
        request()->validate([
            'cw_id' => 'required|exists:cw_schedules,id',
            'user_id' => ['required', 'integer', new BonusSummaryExists($request->input('cw_id'))]
        ]);

        return response(
            $this->obj->getSponsorTree(
                $request->input('cw_id'),
                $request->input('user_id')
            )
        );
    }

    /**
     * Get Incentive summary report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getIncentiveSummary()
    {
        return response($this->obj->getIncentiveSummary());
    }

    /**
     * Get Welcome Bonus summary report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWelcomeBonusSummary()
    {
        return response($this->obj->getWelcomeBonusSummary());
    }

    /**
     * Get Welcome Bonus Details report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWelcomeBonusDetail()
    {
        return response($this->obj->getWelcomeBonusDetail());
    }

    /**
     * Get Bonus Adjustment Listing report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBonusAdjustmentListing()
    {
        return response($this->obj->getBonusAdjustmentListing());
    }

    /**
     * Get Bonus Adjustment Listing report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get77kReport()
    {
        return response($this->obj->get77kReport());
    }

    /**
     * Get Bonus Adjustment Listing report in excel format
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWhtReport()
    {
        return response($this->obj->getWhtReport());
    }
}