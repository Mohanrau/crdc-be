<?php
namespace App\Exceptions\Shop;

use App\Exceptions\{
    Code,
    AppException
};

class EmptyCartException extends AppException
{
    protected
        $message = "message.cart.empty",
        $code = Code::EMPTY_CART
    ;
}