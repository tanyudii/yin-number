<?php

namespace tanyudii\YinNumber\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateNumber(string $modelNamespace, $date = null, $subjectId = null)
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
