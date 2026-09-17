<?php

use App\Models\User;
use Orlyapps\NovaTexteditor\Http\Controllers\TemplateController;
use Orlyapps\NovaTexteditor\Http\Controllers\TextTemplateController;
use Orlyapps\NovaTexteditor\Models\Template;

return [
    'user_class' => User::class,
    'nova_user_class' => App\Nova\User::class,
    'template_class' => Template::class,
    'template_controller' => TemplateController::class,
    'text_template_controller' => TextTemplateController::class,
    /*
     * Blade-Komponenten, die EditorContent::render() in Editor-Texten ausführen darf — alles andere wird als Text
     * ausgegeben. Schlüssel = Tag, `bindings` = feste Ausdrücke, `attributes` = erlaubte Klartext-Attribute.
     */
    'components' => [],

    /*
     * Implementierung von Contracts\PreviewsTemplates (z. B. eine Ableitung von Preview\NovaTemplatePreviewer).
     * Ohne Eintrag gibt es keine Vorschau.
     */
    'template_previewer' => null,

    'categories' => [
        'letter' => 'Letter',
    ],
];
