<?php

namespace Orlyapps\NovaTexteditor\Nova\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Nacmartin\PhpExecJs\PhpExecJs;

class TextEditor extends Field
{
    protected $variableResolver = null;

    public static function createJsonDocument($text)
    {
        return [
            'type' => 'doc',
            'content' => [
                0 => [
                    'type' => 'paragraph',
                    'content' => [
                        0 => [
                            'text' => $text,
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ];
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
            'heute' => '"' . now()->format('d.m.Y') . '"'
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
                $fresh->{$attribute} = json_decode($value, true);
                $fresh->save();
            });
        });

        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = json_decode($request[$requestAttribute], true);
        }
    }

    protected function evaluateVariables($attributeValue, $model)
    {
        if ($this->variableResolver == null) {
            return $attributeValue;
        }
        // Variabeln im JSON String finden und Werte resolven
        $matches = [];
        preg_match_all('/{{(.*?)}}/', $attributeValue, $matches, PREG_OFFSET_CAPTURE, 0);

        $expressions = collect($matches[0])->map(fn ($i) => $i[0]);
        $variables = array_merge($this->defaultVariables(), call_user_func($this->variableResolver, $model));

        $phpexecjs = new PhpExecJs();
        $results = collect();
        foreach ($expressions as $index => $expression) {
            // Variabeln durch Werte ersetzen
            foreach ($variables as $variable => $value) {
                if (is_string($value)) {
                    $expression = str_replace($variable, "'" . $value . "'", $expression);
                } else {
                    $expression = str_replace($variable, $value, $expression);
                }
                $expression = stripslashes($expression);
            }
            try {
                $expression = trim(str_replace(['{{', '}}'], '', $expression));
                $results->push($phpexecjs->evalJs($this->jsFunctions() . $expression));
            } catch (\Throwable $th) {
                // Wenn ein JS error passiert, wird die uninterpretierte expression weiterhin angezeigt
                // Aber ohne {{ xxx }}
                $results->push(trim(str_replace(['{{', '}}'], '', $expression)));
            }
        }

        foreach ($expressions as $index => $expression) {
            $attributeValue = str_replace($expression, $results[$index], $attributeValue);
        }
        return $attributeValue;
    }

    protected function jsFunctions()
    {
        return <<<EOF
        function format(number, decimals = 2, dec_point = ',', thousands_sep = '.') {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        };
EOF;
    }
}
