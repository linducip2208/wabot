<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaWidgetLead extends Model
{
    protected $fillable = [
        'wa_widget_id', 'name', 'message', 'ip_address', 'user_agent',
    ];

    public function widget(): BelongsTo
    {
        return $this->belongsTo(WaWidget::class, 'wa_widget_id');
    }
}
