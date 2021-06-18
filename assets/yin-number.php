<?php

return [
    /**
     * Namespace of models
     */
    "models" => [
        "number" => \tanyudii\YinNumber\Models\Number::class,
        "number_component" => \tanyudii\YinNumber\Models\NumberComponent::class,
    ],

    /**
     * Default number column
     */
    "default_number_column" => "number",

    /**
     * Default date column
     */
    "default_date_column" => "date",

    /**
     * Number component type rules
     */
    "number_setting_component" => [
        "year" => ["y", "Y"],
        "month" => ["m", "M", "F", "n"],
        "day" => ["d", "D", "j", "l"],
    ],
];
