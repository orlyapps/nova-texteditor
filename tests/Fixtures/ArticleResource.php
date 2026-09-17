<?php

namespace Orlyapps\NovaTexteditor\Tests\Fixtures;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class ArticleResource extends Resource
{
    public static $model = Article::class;

    public static $title = 'title';

    public static $search = ['title'];

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('hidden', false);
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
