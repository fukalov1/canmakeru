<?php


namespace App\Exceptions;

use Exception;

class ThrowableCustom extends Exception
{
    protected $message;

    /** @var int */
    protected $http_code = 400;



}
