<?php
namespace App\Repositories\Languages;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Languages\LanguageInterface,
    Models\Languages\Language,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class LanguageRepository extends BaseRepository implements LanguageInterface
{
    use ResourceRepository;

    /**
     * LanguageRepository constructor.
     *
     * @param Language $model
     */
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    /**
     * get language details for a given id
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get all records
     *
     * @param int|null $countryId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     *
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getLanguages(
        int $countryId = null,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        //check if country id is set
        if (!is_null($countryId)){
            $data = $this->modelObj
                ->select(['languages.*'])
                ->with(['countryLanguages' => function($query) use($countryId){
                    $query->where('country_id', $countryId)->orderBy('order', 'asc');
                }])
                ->join('country_languages', 'country_languages.language_id', '=', 'languages.id')
                ->where('country_languages.country_id', $countryId)
                ->orderBy('order', 'asc');

            $totalRecords = collect(
                [
                    'total' => $data->count()
                ]
            );
        }
        else
        {
            $data = $this->modelObj
                ->orderBy($orderBy, $orderMethod);

            $totalRecords = collect(
                [
                    'total' => $this->modelObj->orderBy($orderBy, $orderMethod)->count()
                ]
            );
        }

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }
}