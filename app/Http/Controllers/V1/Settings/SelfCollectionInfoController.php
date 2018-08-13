<?php
namespace App\Http\Controllers\V1\Settings;

use App\Models\Locations\State;
use App\Models\Settings\SelfCollectionInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SelfCollectionInfoController extends Controller
{
    private $selfCollectionInfoObj, $stateObj;

    /**
     * SelfCollectionInfoController constructor.
     */
    public function __construct(
        SelfCollectionInfo $selfCollectionInfo,
        State $state
    )
    {
        $this->middleware('auth');

        $this->selfCollectionInfoObj = $selfCollectionInfo;

        $this->stateObj = $state;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSelfCollectionList(Request $request)
    {
        request()->validate([
            "country_id" => "required|integer|exists:countries,id"
        ]);

        $states = $areas = [];

        $selfCollectionObj = $this->selfCollectionInfoObj->where('country_id', $request->input('country_id'));

        if($selfCollectionObj->whereNotNull('state_id')->count())
        {
            $stateIds = $selfCollectionObj->distinct()->orderBy('state_id')->pluck('state_id');

            $states = $this->stateObj->whereIn('id', $stateIds)->get();

            foreach ($states as $state)
            {
                $areas[$state->name] = $this->selfCollectionInfoObj->where('country_id', $request->input('country_id'))->where('state_id', $state->id)->get();
            }
        }
        else
        {
            $areas = $this->selfCollectionInfoObj->where('country_id', $request->input('country_id'))->get();
        }

        return collect([
            'states' => $states,
            'areas' => $areas
        ]);
    }
}
