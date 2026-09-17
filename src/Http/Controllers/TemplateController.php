<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Orlyapps\NovaTexteditor\Http\Resources\TemplateResource;
use Orlyapps\NovaTexteditor\Support\TemplateGate;

class TemplateController
{
    /**
     * Vorlagen der angefragten Kategorien — mit `all=1` zusätzlich alle übrigen (Flag `matches`).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $templateClass = config('nova-texteditor.template_class');

        try {
            $templates = $this->visibleTemplates($request)
                ->when(! $request->boolean('all'), fn (Builder $query) => $query->whereIn('category', $this->categories($request)))
                ->get();
        } catch (\Throwable $th) {
            $templates = collect();
        }

        return TemplateResource::collection($templates)->additional([
            'meta' => [
                'can_create' => TemplateGate::allows('create', $templateClass),
                'categories' => config('nova-texteditor.categories', []),
            ],
        ]);
    }

    public function store(Request $request): TemplateResource
    {
        $templateClass = config('nova-texteditor.template_class');

        abort_unless(TemplateGate::allows('create', $templateClass), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'subject' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
        ]);

        $template = $templateClass::create([
            'name' => $validated['name'],
            'category' => str($validated['category'])->explode(',')->first(),
            'subject' => $validated['subject'] ?? null,
            'text' => $validated['text'] ?? null,
        ]);

        return TemplateResource::make($template);
    }

    /**
     * Überschreibt Text (und Betreff, falls mitgeschickt) einer bestehenden Vorlage.
     */
    public function update(Request $request, string $template): TemplateResource
    {
        $model = $this->visibleTemplates($request)->findOrFail($template);

        abort_unless(TemplateGate::allows('update', $model), 403);

        $validated = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
        ]);

        $model->update($validated);

        return TemplateResource::make($model);
    }

    protected function visibleTemplates(Request $request): Builder
    {
        return config('nova-texteditor.template_class')::query()
            ->where(function (Builder $query) use ($request) {
                $query->whereNull('user_id')->orWhere('user_id', $request->user()?->getKey());
            });
    }

    /**
     * @return array<int, string>
     */
    protected function categories(Request $request): array
    {
        return str($request->category)->explode(',')->filter()->values()->all();
    }
}
