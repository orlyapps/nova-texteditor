<?php

/*
 * nova:api statt nova — erst diese Gruppe enthält Authenticate/Authorize. Mit `nova` allein waren die
 * Vorlagen-Endpunkte (inkl. Anlegen) ohne Login erreichbar.
 */
Route::middleware('nova:api')->prefix('api')->group(function () {
    Route::resource('text_templates', config('nova-texteditor.text_template_controller'))->only(['index']);
    Route::resource('templates', config('nova-texteditor.template_controller'))->only(['index', 'store', 'update']);
});
