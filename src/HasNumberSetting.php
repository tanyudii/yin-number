<?php

namespace tanyudii\YinNumber;

use Illuminate\Support\Facades\Config;

trait HasNumberSetting
{
    public function getDateColumn()
    {
        return Config::get("yin-number.default_date_column");
    }

    public function getNumberColumn()
    {
        return Config::get("yin-number.default_number_column");
    }
}
