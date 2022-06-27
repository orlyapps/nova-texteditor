<?php

namespace Orlyapps\NovaTexteditor\Nova\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Nacmartin\PhpExecJs\PhpExecJs;

class TextEditor extends Field
{
    protected $variableResolver = null;

    /**
     * Set the buttons that should be available in the menu.
     *
     * @param  array  $buttons
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
            'heute' => '"' . now()->format('d.m.Y') . '"',
        ];
    }

    public function resolveVariables($callback)
    {
        $this->variableResolver = $callback;

        return $this;
    }

    public function variables($variables)
    {
        return $this->withMeta(['variables' => $variables]);
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

    public function blocks(array $blocks)
    {
        return $this->withMeta(['blocks' => $blocks]);
    }

    public function templateCategory($templateCategory)
    {
        return $this->withMeta(['templateCategory' => $templateCategory]);
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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
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
        \Event::listen('eloquent.saved: ' . get_class($model), function ($savedModel) use ($attribute, $request, $requestAttribute) {
            $value = $this->evaluateVariables($request[$requestAttribute], $savedModel);
            $savedModel->withoutEvents(function () use ($value, $attribute, $savedModel) {
                $fresh = $savedModel->fresh();
                $fresh->{$attribute} = $value;
                $fresh->save();
            });
        });
    }
    protected function evaluateVariables($attributeValue, $model)
    {
        if ($this->variableResolver == null) {
            return $attributeValue;
        }

        $variables = array_merge($this->defaultVariables(), call_user_func($this->variableResolver, $model));
        foreach ($variables as $variable => $value) {
            $attributeValue = str_replace('{{' . $variable . '}}', $value, $attributeValue);
            $attributeValue = str_replace('{{ ' . $variable . ' }}', $value, $attributeValue);
        }
        return $attributeValue;
    }
}
