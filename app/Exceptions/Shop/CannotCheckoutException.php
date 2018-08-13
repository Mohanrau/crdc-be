<?php
namespace App\Exceptions\Shop;

use App\Exceptions\{
    Code,
    AppException
};

class CannotCheckoutException extends AppException
{
    protected
        $message = "message.cart.cannot-checkout",
        $code = Code::CANNOT_CHECKOUT
    ;
}