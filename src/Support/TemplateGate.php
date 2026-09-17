<?php

namespace Orlyapps\NovaTexteditor\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class TemplateGate
{
    /**
     * Prüft eine Policy-Ability für Vorlagen. Registriert die App keine Policy für die Vorlagen-Klasse,
     * bleibt das bisherige Verhalten (alles erlaubt) erhalten.
     *
     * @param  class-string<Model>|Model  $template
     */
    public static function allows(string $ability, string|Model $template): bool
    {
        if (Gate::getPolicyFor($template) === null) {
            return true;
        }

        return Gate::allows($ability, $template);
    }
}
