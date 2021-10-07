<?php

namespace Orlyapps\NovaTexteditor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextTemplate extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(config('nova-texteditor.user_class'));
    }
}
