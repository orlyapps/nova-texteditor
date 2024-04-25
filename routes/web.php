<?php

Route::middleware('nova')->prefix('api')->group(function () {
    Route::resource('text_templates', config('nova-texteditor.text_template_controller'))->only(['index']);
    Route::resource('templates', config('nova-texteditor.template_controller'))->only(['index', 'store']);
});
