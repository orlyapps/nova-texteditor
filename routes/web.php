<?php

use Orlyapps\NovaTexteditor\Http\Controllers\TemplatePreviewController;

/*
 * nova:api statt nova — erst diese Gruppe enthält Authenticate/Authorize. Mit `nova` allein waren die
 * Vorlagen-Endpunkte (inkl. Anlegen) ohne Login erreichbar.
 */
Route::middleware('nova:api')->prefix('api')->group(function () {
    Route::resource('text_templates', config('nova-texteditor.text_template_controller'))->only(['index']);
    Route::get('templates/preview/records', [TemplatePreviewController::class, 'records']);
    Route::post('templates/preview', [TemplatePreviewController::class, 'render']);
    Route::resource('templates', config('nova-texteditor.template_controller'))->only(['index', 'store', 'update']);
});
