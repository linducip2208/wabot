<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaForm extends Model
{
    protected $fillable = [
        'user_id', 'meta_account_id', 'name', 'header_text', 'body_text',
        'components', 'status', 'submission_count',
    ];

    protected $casts = [
        'components' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(WaFormSubmission::class, 'form_id');
    }

    public function componentTypes(): array
    {
        return [
            'text_input' => 'Text Input',
            'text_area' => 'Text Area',
            'dropdown' => 'Dropdown',
            'radio' => 'Radio Button',
            'checkbox' => 'Checkbox',
            'date_picker' => 'Date Picker',
            'phone_number' => 'Phone Number',
            'email' => 'Email',
            'number' => 'Number',
        ];
    }
}
