<?php

namespace Orlyapps\NovaTexteditor\Nova\Fields;

use Illuminate\Support\Facades\Schema;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class TextEditor extends Field
{
    protected $variableResolver = null;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|\Closure|callable|object|null  $attribute
     * @param  (callable(mixed, mixed, ?string):mixed)|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, ?callable $resolveCallback = null)
    {
        $this->name = $name;
        $this->resolveCallback = $resolveCallback;

        $this->default(null);

        if ($attribute instanceof Closure || (is_callable($attribute) && is_object($attribute))) {
            $this->computedCallback = $attribute;
            $this->attribute = 'ComputedField';
        } else {
            $this->attribute = $attribute ?? str_replace(' ', '_', Str::lower($name));
        }
    }

    /**
     * Set the buttons that should be available in the menu.
     *
     * @return $this
     */
    public function buttons(array $buttons)
    {
        return $this->withMeta([
            'buttons' => $buttons,
        ]);
    }

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'text-editor';

    public function defaultVariables()
    {
        return [
            'heute' => '"'.now()->format('d.m.Y').'"',
        ];
    }

    public function variables($variables)
    {
        if (is_callable($variables)) {
            $this->variableResolver = $variables;

            return $this->withMeta(['variables' => call_user_func($variables)]);
        }

        return $this->withMeta(['variables' => $variables]);
    }

    /**
     * Platzhalter abhängig von einem anderen Feld (z. B. der Vorlagen-Kategorie) anzeigen.
     *
     * Der Katalog wird komplett mitgeschickt; der Editor wechselt die Platzhalter clientseitig, sobald sich
     * das Feld `$attribute` ändert — ohne dependsOn-Sync, der den Editor-Inhalt zurücksetzen würde.
     *
     * @param  array<string, list<array{label: string, variables: list<string>}>>  $catalog  Wert von `$attribute` → Platzhalter-Gruppen
     * @return $this
     */
    public function variableCatalog(array $catalog, string $attribute, ?string $currentValue = null)
    {
        return $this->withMeta([
            'variableCatalog' => [
                'attribute' => $attribute,
                'current' => $currentValue,
                'groups' => (object) $catalog,
            ],
        ]);
    }

    /**
     * Vorschau mit echten Datensätzen (Umschalter „Vorschau“ im Editor). Braucht einen registrierten
     * `nova-texteditor.template_previewer` und `variableCatalog()` bzw. `templateCategory()` für die Kategorie.
     *
     * @return $this
     */
    public function templatePreview()
    {
        return $this->withMeta(['templatePreview' => filled(config('nova-texteditor.template_previewer'))]);
    }

    /**
     * Set the default Text value
     *
     * @param  string  $default
     * @return $this
     */
    public function defaultValue($default)
    {
        return $this->withMeta(['defaultValue' => $default]);
    }

    public function prefix($prefix)
    {
        return $this->withMeta(['prefix' => $prefix]);
    }

    public function saveAsJson()
    {
        return $this->withMeta(['saveAsJson' => true]);
    }

    public function blocks(array $blocks)
    {
        return $this->withMeta(['blocks' => $blocks]);
    }

    public function withTextTemplates()
    {
        return $this->withMeta(['withTextTemplates' => true]);
    }

    public function templateCategory($templateCategory)
    {
        return $this->withMeta(['templateCategory' => $templateCategory]);
    }

    public function selectFirstTemplate()
    {
        return $this->withMeta(['selectFirstTemplate' => true]);
    }

    /**
     * Steuert, ob die Vorlagen-Auswahl (Dropdown und selectFirstTemplate) das Betreff-Feld setzt.
     *
     * @return $this
     */
    public function syncTemplateSubject(bool $sync = true)
    {
        return $this->withMeta(['syncTemplateSubject' => $sync]);
    }

    public function showHelp()
    {
        return $this->withMeta(['showHelp' => true]);
    }

    public function enablePreview(string $previewUrl)
    {
        return $this->withMeta(['previewUrl' => $previewUrl]);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        /**
         * Variabeln erst nach dem Speichern evaluieren und ersetzen
         */
        $value = $this->evaluateVariables($request[$requestAttribute], $model);
        $subjectValue = $this->evaluateVariables($request['subject'], $model);
        if (data_get($this->meta, 'saveAsJson')) {
            $value = json_decode($value);
        }
        $model->{$attribute} = $value;

        if (Schema::hasColumn($model->getTable(), 'subject')) {
            $model->subject = $subjectValue;
        }
    }

    protected function evaluateVariables($attributeValue, $model)
    {
        if ($this->variableResolver == null) {
            return $attributeValue;
        }

        $variables = array_merge($this->defaultVariables(), call_user_func($this->variableResolver, $model));

        foreach ($variables as $variable => $value) {
            $attributeValue = str_replace('{{'.$variable.'}}', $value, $attributeValue);
            $attributeValue = str_replace('{{ '.$variable.' }}', $value, $attributeValue);
            $attributeValue = str_replace('{'.$variable.'}', $value, $attributeValue);
            $attributeValue = str_replace('{ '.$variable.' }', $value, $attributeValue);
        }

        return $attributeValue;
    }
}
