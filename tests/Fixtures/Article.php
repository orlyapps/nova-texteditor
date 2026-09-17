<?php

namespace Orlyapps\NovaTexteditor\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'test_articles';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<string, mixed>
     */
    public function getTextEditorVariablesAttribute(): array
    {
        return ['artikel-titel' => $this->title];
    }
}
