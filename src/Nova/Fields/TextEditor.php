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
}
