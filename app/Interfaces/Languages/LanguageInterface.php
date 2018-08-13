<?php
namespace App\Interfaces\Languages;

use App\Interfaces\BaseInterface;

interface LanguageInterface extends BaseInterface
{
    /**
     * get all records
     *
     * @param int|null $countryId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getLanguages(
        int $countryId = null,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );
}