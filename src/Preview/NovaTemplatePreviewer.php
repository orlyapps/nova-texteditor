<?php

namespace Orlyapps\NovaTexteditor\Preview;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Orlyapps\NovaTexteditor\Contracts\PreviewsTemplates;
use Orlyapps\NovaTexteditor\EditorContent;

/**
 * Vorlagen-Vorschau mit echten Datensätzen über Nova: Quellen sind Models mit Nova-Resource, die Auswahl nutzt
 * Novas Index-Query (Suche + indexQuery-Sichtbarkeit) und die `view`-Policy, gerendert wird über EditorContent.
 *
 * Die App liefert nur, welche Models zu einer Kategorie gehören, und optional Empfänger:in und Zusatz-Platzhalter.
 * Platzhalter liest die Klasse aus `getTextEditorVariablesAttribute()`, Blade-Daten zusätzlich aus
 * `getTextEditorRenderPropsAttribute()`, sofern das Model die Methoden hat.
 */
abstract class NovaTemplatePreviewer implements PreviewsTemplates
{
    protected int $recordLimit = 20;

    /**
     * Models, mit denen Vorlagen dieser Kategorie gerendert werden können.
     *
     * @return array<class-string<Model>, string> Model-Klasse => Bezeichnung
     */
    abstract protected function sourceModels(string $category): array;

    /**
     * Empfänger:in für `$contact` (z. B. Anrede). Beim Versand je Empfänger:in, in der Vorschau eine passende Person.
     */
    protected function contactFor(?Model $model): mixed
    {
        return null;
    }

    /**
     * Platzhalter, die nicht am Model hängen, sondern beim Rendern der Kategorie zusätzlich übergeben werden.
     *
     * @return array<string, mixed>
     */
    protected function extraVariables(string $category, ?Model $model): array
    {
        return [];
    }

    /**
     * Blade-Daten für die erlaubten Komponenten.
     *
     * @return array<string, mixed>
     */
    protected function renderData(string $category, ?Model $model, mixed $contact): array
    {
        return array_merge(
            ['contact' => $contact, 'user' => auth()->user()],
            $model && method_exists($model, 'getTextEditorRenderPropsAttribute') ? $model->getTextEditorRenderPropsAttribute() : [],
        );
    }

    public function sources(string $category): array
    {
        return collect($this->sourceModels($category))
            ->filter(fn (string $label, string $model) => class_exists($model) && Nova::resourceForModel($model))
            ->map(fn (string $label, string $model) => ['key' => $model, 'label' => $label])
            ->values()
            ->all();
    }

    public function records(string $category, string $source, ?string $search = null): array
    {
        $resource = $this->resourceFor($category, $source);

        return $this->visibleQuery($resource, $search)
            ->limit($this->recordLimit)
            ->get()
            ->filter(fn (Model $model) => $this->canView($model))
            ->map(fn (Model $model) => ['id' => $model->getKey(), 'label' => (string) (new $resource($model))->title()])
            ->values()
            ->all();
    }

    public function render(string $category, ?string $source, int|string|null $record, ?string $subject, ?string $text): array
    {
        $model = null;

        if ($source && filled($record)) {
            $model = $this->visibleQuery($this->resourceFor($category, $source))->findOrFail($record);
            abort_unless($this->canView($model), 403);
        }

        $contact = $this->contactFor($model);
        $data = $this->renderData($category, $model, $contact);

        return [
            'subject' => filled($subject) ? trim(strip_tags(EditorContent::render($this->replaceVariables($subject, $category, $model), $data))) : null,
            'html' => EditorContent::render($this->replaceVariables((string) $text, $category, $model), $data),
            'recipient' => data_get($contact, 'fullname') ?? data_get($contact, 'name'),
        ];
    }

    /**
     * @return class-string
     */
    protected function resourceFor(string $category, string $source): string
    {
        abort_unless(collect($this->sources($category))->contains('key', $source), 422, 'Unbekannte Quelle für diese Kategorie.');

        return Nova::resourceForModel($source);
    }

    /**
     * Novas Index-Query der Resource: dieselbe Sichtbarkeit und Suche wie in der Liste, neueste zuerst.
     *
     * @param  class-string  $resource
     */
    protected function visibleQuery(string $resource, ?string $search = null): Builder
    {
        $model = $resource::newModel();

        return $resource::buildIndexQuery(NovaRequest::createFrom(request()), $model->newQuery(), $search)
            ->reorder()
            ->orderByDesc($model->getQualifiedKeyName());
    }

    protected function canView(Model $model): bool
    {
        return Gate::getPolicyFor($model) === null || Gate::allows('view', $model);
    }

    protected function replaceVariables(string $value, string $category, ?Model $model): string
    {
        $variables = array_merge(
            $model && method_exists($model, 'getTextEditorVariablesAttribute') ? $model->getTextEditorVariablesAttribute() : [],
            $this->extraVariables($category, $model),
        );

        foreach ($variables as $variable => $replacement) {
            $value = str_replace(['{'.$variable.'}', '{ '.$variable.' }'], (string) $replacement, $value);
        }

        return $value;
    }
}
