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
    /**
     * Legacy support for old tiptap 1.0
     */
    public function getTextAttribute($value)
    {
        $text = $this->attributes['text'];

        $replacements = [
            'hard_break' => 'hardBreak',
            'list_item' => 'listItem',
            'bullet_list' => 'bulletList'
        ];

        foreach ($replacements as $oldText => $newText) {
            $text = str_replace($oldText, $newText, $text);
        }

        return $text;
    }
    public function user()
    {
        return $this->belongsTo(config('nova-texteditor.user_class'));
    }
}
