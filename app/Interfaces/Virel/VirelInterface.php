<?php
namespace App\Interfaces\Virel;

interface VirelInterface
{
    /**
     * get user by email/old_member_id
     *
     * @param string $email
     * @param int $old_member_id
     * @return mixed
     */
    public function getUser(string $email = null, int $old_member_id = null);

    /**
     * get member by old_member_id
     *
     * @param int $old_member_id
     * @return mixed
     */
    public function getMember(int $old_member_id);

    /**
     * get product category list
     *
     * @return mixed
     */
    public function getProductCategories();

    /**
     * get product list
     *
     * @return mixed
     */
    public function getProducts();

    /**
     * get promo product list
     *
     * @return mixed
     */
    public function getPromoProducts();
}