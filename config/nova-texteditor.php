<?php

return [
    'user_class' => \App\Models\User::class,
    'nova_user_class' => \App\Nova\User::class,
    'template_class' => \Orlyapps\NovaTexteditor\Models\Template::class,
    'template_controller' => \Orlyapps\NovaTexteditor\Http\Controllers\TemplateController::class,
    'text_template_controller' => \Orlyapps\NovaTexteditor\Http\Controllers\TextTemplateController::class,
    'categories' => [
        'letter' => 'Letter',
    ],
];
