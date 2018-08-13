<?php
namespace App\Helpers\Classes;

/**
 * RandomPassword class for various common function.
 */
class RandomPassword
{
    /**
     * generate random password
     *
     * @param int $length
     * @return bool|string
     */
    public static function generate(int $length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}