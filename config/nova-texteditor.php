<?php

return [
    'user_class' => \App\Models\User::class,
    'nova_user_class' => \App\Nova\User::class,
    'template_class' => \Orlyapps\NovaTexteditor\Models\Template::class,
    'template_controller' => \Orlyapps\NovaTexteditor\Http\Controllers\TemplateController::class,
    'categories' => [
        'letter' => 'Letter',
    ],
];
