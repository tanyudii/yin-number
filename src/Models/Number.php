<?php

namespace tanyudii\YinNumber\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use tanyudii\YinNumber\Contracts\NumberModel;

class Number extends Model implements NumberModel
{
    protected $fillable = ["name", "model", "reset_type"];

    public function numberComponents(): HasMany
    {
        return $this->hasMany(Config::get("yin-number.models.number_component"));
    }
}
