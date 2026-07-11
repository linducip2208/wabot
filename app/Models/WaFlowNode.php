<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaFlowNode extends Model
{
    protected $fillable = [
        'flow_id', 'type', 'label', 'position_x', 'position_y',
        'config', 'reply_message', 'channel', 'media_url', 'ai_key_id',
        'condition_field', 'condition_operator', 'condition_value',
        'next_node_id_true', 'next_node_id_false', 'wait_seconds', 'sort_order',
    ];

    protected $casts = [
        'config' => 'json',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'wait_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(WaFlow::class, 'flow_id');
    }

    public function aiKey(): BelongsTo
    {
        return $this->belongsTo(WaAiKey::class, 'ai_key_id');
    }

    public function nextNodeTrue(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_node_id_true');
    }

    public function nextNodeFalse(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_node_id_false');
    }
}
