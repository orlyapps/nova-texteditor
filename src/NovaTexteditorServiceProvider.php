<?php

namespace Orlyapps\NovaTexteditor;

use Laravel\Nova\Nova;
use Orlyapps\NovaTexteditor\Nova\Template;
use Orlyapps\NovaTexteditor\Nova\TextTemplate;
use Orlyapps\NovaTexteditor\View\Components\Salutation;
use Orlyapps\NovaTexteditor\View\Components\Signature;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NovaTexteditorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nova-texteditor')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasViewComponent('orlyapps', Salutation::class)
            ->hasViewComponent('orlyapps', Signature::class)
            ->hasMigration('create_nova-texteditor_table');
    }

    public function bootingPackage()
    {
        Nova::resources([
            TextTemplate::class,
            Template::class,
        ]);
        Nova::serving(function () {
            Nova::script('nova-texteditor', __DIR__ . '/../dist/js/field.js');
            Nova::style('nova-texteditor', __DIR__ . '/../dist/css/nova.css');
        });
    }
}
