<?php

namespace Orlyapps\NovaTexteditor\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;
use Orlyapps\NovaTexteditor\Nova\Fields\TextEditor;

class Template extends Resource
{
    /**
     * Sort Index (Custom)
     *
     * @var int
     */
    public static function index()
    {
        return 99;
    }

    /**
     * Get the icon for the navigation
     *
     * @return string
     */
    public static function icon()
    {
        return 'file-invoice';
    }

    public static $group = 'Einstellungen';

    public static $globallySearchable = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Orlyapps\NovaTexteditor\Models\Template::class;

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Templates');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Template');
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Select::make(_('Category'), 'category')
                ->options(config('nova-texteditor.categories'))
                ->displayUsingLabels()
                ->help(__('The template category determines the use of the template'))
                ->sortable(),
            Text::make(__('Name'), 'name')->sortable()->rules('required'),
            TextEditor::make(__('Vorlage'), 'text')
                ->blocks([
                    'salutation' => 'Anrede',
                    'signature' => 'Signatur',
                ])
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
