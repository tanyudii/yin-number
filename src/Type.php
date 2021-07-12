<?php

namespace tanyudii\YinNumber;

class Type
{
    const RESET_TYPE_DAILY = "DAILY";
    const RESET_TYPE_MONTHLY = "MONTHLY";
    const RESET_TYPE_YEARLY = "YEARLY";

    const RESET_TYPE_OPTIONS = [
        self::RESET_TYPE_DAILY,
        self::RESET_TYPE_MONTHLY,
        self::RESET_TYPE_YEARLY,
    ];

    const COMPONENT_TYPE_TEXT = "TEXT";
    const COMPONENT_TYPE_YEAR = "YEAR";
    const COMPONENT_TYPE_MONTH = "MONTH";
    const COMPONENT_TYPE_DAY = "DAY";
    const COMPONENT_TYPE_COUNTER = "COUNTER";

    const COMPONENT_TYPE_OPTIONS = [
        self::COMPONENT_TYPE_TEXT,
        self::COMPONENT_TYPE_YEAR,
        self::COMPONENT_TYPE_MONTH,
        self::COMPONENT_TYPE_DAY,
        self::COMPONENT_TYPE_COUNTER,
    ];
}
