<?php

namespace Orlyapps\NovaTexteditor\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Orlyapps\NovaTexteditor\NovaTexteditorServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [NovaTexteditorServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('test_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->boolean('hidden')->default(false);
        });
    }
}
