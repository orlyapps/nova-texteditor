<?php

namespace Orlyapps\NovaTexteditor;

use Illuminate\Support\Facades\Blade;

/**
 * Rendert Inhalte aus dem Texteditor (Mails, Briefe, Vorlagen, Notizen …) sicher über Blade.
 *
 * Erlaubt sind nur die in `nova-texteditor.components` registrierten Blöcke (z. B. `<x-orlyapps-salutation>`) und
 * einfache Ausgaben wie `{{ $contact->firstname }}`. Ohne Registrierung wird jede Komponente als Text ausgegeben. Alles andere im Text — inklusive bereits
 * eingesetzter Platzhalter-Werte wie Kundennamen aus der Online-Buchung — ist Nutzereingabe und darf nie als
 * Blade/PHP ausgeführt werden: `{{ system('…') }}` im Text war sonst Code-Ausführung auf dem Server.
 * Deshalb IMMER diese Klasse statt `Blade::render()` für Editor-Inhalte verwenden.
 */
final class EditorContent
{
    /**
     * `{{ $variable }}` bzw. `{{ $variable->eigenschaft }}` (auch verkettet, auch `?->`) — keine Aufrufe, keine Operatoren.
     */
    private const SIMPLE_ECHO = '/\{\{\s*\$([a-zA-Z_]\w*)((?:\??->[a-zA-Z_]\w*)*)\s*\}\}/';

    /**
     * Eigenschaften, die auch über einfache Ausgaben nie in einen Text gelangen dürfen.
     *
     * @var list<string>
     */
    private const FORBIDDEN_PROPERTIES = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'api_token'];

    /**
     * @param  array<string, mixed>  $data
     */
    public static function render(?string $content, array $data = [], bool $deleteCachedView = false): string
    {
        if (blank($content)) {
            return '';
        }

        return Blade::render(self::neutralize($content), $data, $deleteCachedView);
    }

    /**
     * Entschärft alle Blade-/PHP-Syntax außer den erlaubten Komponenten. Das Ergebnis sieht nach dem Rendern
     * genauso aus wie der eingegebene Text (`{{ x }}` bleibt als Text `{{ x }}` sichtbar).
     */
    public static function neutralize(string $content): string
    {
        $components = [];

        // Komponenten-Tags (<x-…>, <x:…>, <livewire:…>, <flux:…>): erlaubte kanonisch neu aufbauen, alle anderen als Text ausgeben.
        $content = preg_replace_callback(
            '/<(\/?)((?:x[-:]|[a-z][\w-]*:)[\w\-.:]*)((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/i',
            function (array $match) use (&$components): string {
                $tag = strtolower($match[2]);

                if (! array_key_exists($tag, self::components())) {
                    return e($match[0]);
                }

                $token = "\u{E000}".count($components)."\u{E001}";
                $components[$token] = $match[1] === '/' ? "</{$tag}>" : self::buildOpeningTag($tag, $match[3]);

                return $token;
            },
            $content
        );

        // Einfache Ausgaben erhalten (kanonisch neu geschrieben), bevor alle übrigen {{ … }} entschärft werden.
        $content = preg_replace_callback(self::SIMPLE_ECHO, function (array $match) use (&$components): string {
            preg_match_all('/[a-zA-Z_]\w*/', $match[2], $properties);

            if (array_intersect($properties[0], self::FORBIDDEN_PROPERTIES)) {
                return $match[0];
            }

            $token = "\u{E000}".count($components)."\u{E001}";
            $components[$token] = '{{ $'.$match[1].$match[2].' }}';

            return $token;
        }, $content);

        $content = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $content);

        // @if, @php, @include … → @@if (Blade gibt „@if“ als Text aus). E-Mail-Adressen (a@b) bleiben unberührt.
        $content = preg_replace('/\B@(?=\w)/', '@@', $content);

        // {{ … }}, {!! … !!}, {{{ … }}} → @{{ … }} (Blade gibt die Klammern als Text aus).
        $content = preg_replace('/(?<!@)(\{\{|\{!!)/', '@$1', $content);

        return strtr($content, $components);
    }

    /**
     * Erlaubte Komponenten mit ihren festen Blade-Bindungen, z. B.
     * `'x-orlyapps-salutation' => ['bindings' => [':contact' => '$contact'], 'attributes' => ['type']]`.
     * Vorhandene Bindungen werden mit dem festen Ausdruck neu gesetzt, der übergebene Wert wird ignoriert
     * (sonst wäre `:contact="system('…')"` ausführbar). Attribute sind nur Buchstaben.
     *
     * @return array<string, array{bindings?: array<string, string>, attributes?: list<string>}>
     */
    private static function components(): array
    {
        return collect(config('nova-texteditor.components', []))
            ->mapWithKeys(fn (array $definition, string $tag) => [strtolower($tag) => $definition])
            ->all();
    }

    private static function buildOpeningTag(string $tag, string $rawAttributes): string
    {
        $definition = self::components()[$tag];
        $attributes = [];

        preg_match_all('/([:\w\-]+)\s*=\s*"([^"]*)"/', $rawAttributes, $matches, PREG_SET_ORDER);

        foreach ($matches as [, $name, $value]) {
            if (in_array($name, $definition['attributes'] ?? [], true)) {
                $attributes[] = $name.'="'.preg_replace('/[^A-Za-z]/', '', $value).'"';
            }
        }

        foreach ($definition['bindings'] ?? [] as $name => $expression) {
            if (preg_match('/(?<![\w:])'.preg_quote($name, '/').'\s*=/', $rawAttributes)) {
                $attributes[] = $name.'="'.$expression.'"';
            }
        }

        return '<'.$tag.' '.implode(' ', $attributes).'>';
    }
}
