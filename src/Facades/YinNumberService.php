<?php

namespace tanyudii\YinNumber\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateNumber(string $modelNamespace, $date = null, $subjectId = null, int $nextCounter = 0)
 * @method static Model bookingNumber(string $modelNamespace, $date = null, $subjectId = null)
 *
 * @see \tanyudii\YinNumber\Services\YinNumberService
 */
class YinNumberService extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return "yin-number-service";
    }
}
