<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'Insufficient balance';
    protected $code = 400;
}
