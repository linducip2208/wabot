<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaFormSubmission extends Model
{
    protected $fillable = [
        'form_id', 'contact_id', 'phone', 'data', 'message_id', 'status',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(WaForm::class, 'form_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }
}
