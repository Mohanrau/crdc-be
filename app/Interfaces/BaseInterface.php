<?php
namespace App\Interfaces;

interface BaseInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getAll(
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * create new record
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * update one record
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id);

    /**
     * delete user by id
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);
}