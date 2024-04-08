<?php

use Orlyapps\NovaTexteditor\Http\Controllers\TemplateController;
use Orlyapps\NovaTexteditor\Http\Controllers\TextTemplateController;

Route::middleware('nova')->prefix('api')->group(function () {
    Route::resource('text_templates', TextTemplateController::class)->only(['index']);
    Route::resource('templates', config('nova-texteditor.template_controller'))->only(['index', 'store']);
});
