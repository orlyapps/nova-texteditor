# Changelog

All notable changes to `nova-texteditor` will be documented in this file.

## 6.0.0 – unreleased (`feature/v6`)

Breaking Release. Umstieg von `feature/nova5`: siehe [UPGRADE.md](UPGRADE.md).

### Sicherheit

- **Blade-/PHP-Ausführung in Editor-Texten verhindert:** Neues `Orlyapps\NovaTexteditor\EditorContent::render()` ersetzt `Blade::render()` für Editor-Inhalte. Nur in `nova-texteditor.components` registrierte Blöcke (mit festen Bindungen) und einfache Ausgaben wie `{{ $contact->firstname }}` werden ausgeführt; `{{ strtoupper(…) }}`, `{!! !!}`, `@php`, `<?php` und fremde Komponenten-Tags erscheinen als Text.
- **Vorlagen-Routen erfordern Anmeldung:** `/api/templates` und `/api/text_templates` laufen unter `nova:api` statt `nova` und waren bisher ohne Login erreichbar (inkl. Anlegen von Vorlagen).
- **Policy-Prüfung für Vorlagen:** Anlegen und Überschreiben prüfen `create`/`update` der Vorlagen-Policy (ohne registrierte Policy bleibt alles erlaubt).

### Neu

- **tiptap 3** (3.31) statt tiptap 2, inkl. `UndoRedo`, `Gapcursor`, `FontSize` aus `@tiptap/extension-text-style`, floating-ui statt tippy.
- **Robuste Link-Erkennung:** Eigene Autolink-Regel – typografische Anführungszeichen und Satzzeichen am Rand werden nicht mehr mit verlinkt, `„www.x.de“` und `www.x.de.` werden erkannt, ungültige TLDs nicht teilweise verlinkt.
- **Responsive Toolbar** mit Heroicons, Gruppen und „Mehr“-Menü unter 640 px; Tooltips über Novas floating-vue inkl. Tastenkürzel.
- **BubbleMenu** bei Textauswahl: Fett, Kursiv, Unterstrichen, Durchgestrichen, Link setzen/bearbeiten/öffnen/entfernen.
- **Slash-Menü „/“** für Absatzformate, variable Blöcke, Platzhalter und Textbausteine (ersetzt das Mention-Popup).
- **Variable Blöcke** mit gemeinsamer Hülle `<texteditor-block>`: Drag-Griff, Hoch/Runter (Touch), Entfernen.
- **Platzhalter-Hervorhebung:** bekannte Platzhalter als Chip, unbekannte rot unterstrichen (nur Decoration, HTML unverändert).
- **Vorlagen-Auswahl** mit Suche, passende Kategorien zuerst plus alle übrigen („Andere Kategorien“), mobil als Sheet.
- **Vorlagen überschreiben:** geladene Vorlage direkt überschreiben oder als neue speichern (Nova-Modals statt `window.prompt`), Rückfrage vor dem Ersetzen bearbeiteter Texte, Hervorhebung bei Änderungen seit dem Laden.
- **`TextEditor::variableCatalog($catalog, $attribute, $current)`:** Platzhalter abhängig von einem anderen Formularfeld (z. B. Vorlagen-Kategorie), gruppiert, clientseitig umgeschaltet.
- **`TextEditor::templatePreview()`:** Vorschau-Umschalter mit echten Datensätzen. Contract `Contracts\PreviewsTemplates`, Basis-Implementierung `Preview\NovaTemplatePreviewer` (Nova-Index-Query, Policy, Platzhalter, sicheres Rendern); Endpunkte `GET /api/templates/preview/records`, `POST /api/templates/preview`.
- **API:** `PUT /api/templates/{id}`; `GET /api/templates?all=1` liefert zusätzlich Vorlagen anderer Kategorien. Ressource enthält `category`, `category_label`, `matches`, `can_update`; `meta.can_create`, `meta.categories`.
- **JS-API für Erweiterungen:** `window.NovaTexteditor` (`Node`, `Mark`, `Extension`, `mergeAttributes`, `VueNodeViewRenderer`, `NodeViewWrapper`, `NodeViewContent`, `nodeViewProps`, `BlockView`).
- **Tests:** Testbench + Pest (`composer test`).

### Geändert

- `window.TextEditorNotes` akzeptiert Factories `(tiptap) => Extension` (bisher nur fertige Extensions).
- `Link`: `openOnClick: false`, `linkOnPaste: true`, `defaultProtocol: 'https'`, Protokolle `mailto`/`tel`.
- `POST /api/templates` validiert (`name` Pflicht) und antwortet mit 422 statt still nichts zu tun.
- `buttons()`: `history` erzeugt zwei Buttons (Rückgängig/Wiederholen), `heading` und `textAlign` sind Menüs, neuer Button `link`. `sinkListItem`/`liftListItem` waren vertauscht und sind korrigiert.
- Hilfe-Kästen unter dem Editor („Variable Blöcke“, Platzhalter) entfallen – beides liegt als Menü in der Toolbar.
- `/api/templates` behält die Datenbank-Reihenfolge (relevant für `selectFirstTemplate()`); sortiert wird nur in der Auswahl.

### Entfernt

- FontAwesome, `tippy.js`, `@tiptap/extension-mention`, `vue-template-compiler`, eigene `font-size.js`.
- Button-Komponenten `components/buttons/*`, `MentionList.vue`, `text-templates-search.js`.

### Build

- laravel-mix 6 bleibt; `webpack` auf 5.104.1 gepinnt (5.111 bricht laravel-mix).

## 1.0.0 - 202X-XX-XX

- initial release
