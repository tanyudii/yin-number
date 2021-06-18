<?php

namespace tanyudii\YinNumber;

trait HasNumberSetting
{
    public function getDateColumn()
    {
        return config("yin-number.default_date_column");
    }

    public function getNumberColumn()
    {
        return config("yin-number.default_number_column");
    }
}
