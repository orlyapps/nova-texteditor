<?php

namespace Orlyapps\NovaTexteditor\Contracts;

/**
 * Rendert eine Vorlage mit einem echten Datensatz der App. Registriert über
 * `config('nova-texteditor.template_previewer')`; ohne Registrierung gibt es keine Vorschau.
 */
interface PreviewsTemplates
{
    /**
     * Datensatz-Arten, mit denen Vorlagen dieser Kategorie gerendert werden können.
     *
     * @return list<array{key: string, label: string}>
     */
    public function sources(string $category): array;

    /**
     * Die neuesten bzw. zur Suche passenden Datensätze einer Quelle, die die angemeldete Person sehen darf.
     *
     * @return list<array{id: int|string, label: string}>
     */
    public function records(string $category, string $source, ?string $search = null): array;

    /**
     * Rendert Betreff und Text (ungespeicherter Editor-Inhalt) mit dem Datensatz — ohne etwas zu speichern oder zu senden.
     *
     * @return array{subject: ?string, html: string, recipient: ?string}
     */
    public function render(string $category, ?string $source, int|string|null $record, ?string $subject, ?string $text): array;
}
