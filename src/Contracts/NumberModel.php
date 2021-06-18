<?php

namespace tanyudii\YinNumber\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interface NumberSettingModel
 * @package tanyudii\YinNumber\Contracts
 */
interface NumberModel
{
    public function numberComponents(): HasMany;
}
