<?php
namespace App\Exceptions;

class Code
{
    // Location Code ===================================================================================================
    const COUNTRY_ALREADY_SET = 00001;
    const COUNTRY_NOT_SET = 00002;
    const LOCATION_ALREADY_SET = 00003;

    // Shop Code =======================================================================================================
    const EMPTY_CART = 01001;
    const CANNOT_CHECKOUT = 01002;

    // Master ==========================================================================================================
    const INVALID_SALE_TYPE = 02001;
    const MASTER_NOT_FOUND = 02002;
}