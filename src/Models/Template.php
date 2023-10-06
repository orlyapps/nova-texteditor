<?php

namespace Orlyapps\NovaTexteditor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $casts = [
        'text' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(config('nova-texteditor.user_class'));
    }
}
