<?php

namespace Orlyapps\NovaTexteditor\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Orlyapps\NovaTexteditor\Preview\NovaTemplatePreviewer;

class ArticlePreviewer extends NovaTemplatePreviewer
{
    protected function sourceModels(string $category): array
    {
        return $category === 'articles' ? [Article::class => 'Artikel', 'App\\DoesNotExist' => 'Fehlt'] : [];
    }

    protected function contactFor(?Model $model): mixed
    {
        return $model?->author;
    }

    protected function extraVariables(string $category, ?Model $model): array
    {
        return ['stufe' => 2];
    }
}
