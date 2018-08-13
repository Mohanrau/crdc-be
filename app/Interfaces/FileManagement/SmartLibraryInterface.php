<?php
namespace App\Interfaces\FileManagement;

interface SmartLibraryInterface
{
    /**
     * get distinct products belong to union of countries
     *
     * @param array $countries
     * @param string $text
     * @return array
     */
    public function getSmartLibraryProduct(array $countries, string $text);

    /**
     * get upload file type list
     *
     * @return array
     */
    public function getSmartLibraryFileTypeList();

    /**
     * get smart library filtered by the following parameters
     *
     * @param int $countryId
     * @param string $title
     * @param string $fileType
     * @param int $saleTypeId
     * @param int $productCategoryId
     * @param int $productId
     * @param int $status
     * @param int $newJoinerEssentialTools
     * @param int $useMobileFilter
     * @param array $countries
     * @param array $languages
     * @param array $fileTypes
     * @param array $productCategories  
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getSmartLibrariesByFilters(
        int $countryId = 0,
        string $title = '',
        string $fileType = '',
        int $saleTypeId = 0,
        int $productCategoryId = 0,
        int $productId = 0,
        int $status = 2,
        int $newJoinerEssentialTools = 2,
        int $useMobileFilter = 0,
        array $countries = array(),
        array $languages = array(),
        array $fileTypes = array(),
        array $productCategories = array(),
        int $paginate = 20,
        string $orderBy = 'sequence_priority',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get one smart library by id
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id);

    /**
     * delete one smart library by id
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * create or update smart library
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);
}