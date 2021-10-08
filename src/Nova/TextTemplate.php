<?php

namespace Orlyapps\NovaTexteditor\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Resource;

class TextTemplate extends Resource
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
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Orlyapps\NovaTexteditor\Models\TextTemplate::class;

    /**
     * The resource group name
     *
     * @var string
     */
    public static $group = 'Einstellungen';

    /**
     * Indicates if the resoruce should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the icon for the navigation
     *
     * @return string
     */
    public static function icon()
    {
        return 'keyboard';
    }

    /**
     * Get the displayble label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Text blocks');
    }

    /**
     * Get the displayble singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('text block');
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
        'id', 'name',
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
            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required'),
            BelongsTo::make(__('User'), 'user', config('nova-texteditor.nova_user_class'))
                ->searchable()
                ->nullable()
                ->help(__('Text block is only visible for this user')),
            Text::make(__('Shortcode'), 'shortcode')
                ->sortable()
                ->rules('required', 'unique:text_templates,shortcode,{{resourceId}}', 'nullable'),
            Textarea::make(__('Text block'), 'text')
                ->alwaysShow()
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
