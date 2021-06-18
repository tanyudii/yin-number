<?php

namespace tanyudii\YinNumber\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberComponent extends Model
{
    protected $fillable = ["number_setting_id", "sequence", "type", "format"];

    public function number(): BelongsTo
    {
        return $this->belongsTo(config("yin-number.models.number"));
    }
}
