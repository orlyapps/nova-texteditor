<?php

namespace Orlyapps\NovaTexteditor;

use Laravel\Nova\Nova;
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
            ->hasMigration('create_nova-texteditor_table');
    }

    public function bootingPackage()
    {
        Nova::serving(function () {
            Nova::script('nova-texteditor', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-texteditor', __DIR__.'/../dist/css/nova.css');
        });
    }
}
