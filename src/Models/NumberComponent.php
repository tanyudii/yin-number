<?php

namespace tanyudii\YinNumber\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;

class NumberComponent extends Model
{
    protected $fillable = ["number_setting_id", "sequence", "type", "format"];

    public function number(): BelongsTo
    {
        return $this->belongsTo(Config::get("yin-number.models.number"));
    }
}
