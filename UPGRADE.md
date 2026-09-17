# Upgrade Guide

## Von `feature/nova5` auf `feature/v6`

Geschätzter Aufwand: 30–60 Minuten. Pflichtschritte sind mit **(Pflicht)** markiert – ohne sie brechen Blöcke oder Vorlagen.

### 1. Package-Version umstellen (Pflicht)

```json
"orlyapps/nova-texteditor": "dev-feature/v6"
```

```bash
composer update orlyapps/nova-texteditor
php artisan route:cache   # die Vorlagen-Routen haben sich geändert
```

### 2. Editor-Texte sicher rendern (Pflicht)

Bisher wurden Editor-Inhalte mit `Blade::render()` gerendert. Damit konnte jede Person mit Schreibzugriff PHP auf dem Server ausführen (`{{ system('…') }}` im Text), und über eingesetzte Platzhalter-Werte (z. B. Kundennamen) auch Dritte.

Alle Stellen, die Editor-Text rendern, auf `EditorContent` umstellen:

```php
// vorher
return Blade::render($this->text, ['contact' => $customer, 'user' => $user]);

// nachher
use Orlyapps\NovaTexteditor\EditorContent;

return EditorContent::render($this->text, ['contact' => $customer, 'user' => $user]);
```

Typische Fundstellen: `render()`-Methoden von Mail/Letter/Invoice/Note/Template-Models und `displayUsing()` von `TextEditor`-Feldern.

```bash
grep -rn "Blade::render" app Modules --include='*.php'
```

`Blade::render()` für Dateien aus `resources/views` (z. B. PDF-Templates) bleibt unverändert.

**Was weiter funktioniert:** registrierte Blöcke (Schritt 3) und einfache Ausgaben wie `{{ $contact->firstname }}` oder `{{ $customer?->lastname }}`.
**Was jetzt als Text erscheint:** Funktions-/Methodenaufrufe, Ausdrücke, `{!! !!}`, `@if`/`@php` & Co., `<?php`, nicht registrierte Komponenten sowie `password`, `remember_token`, `two_factor_*`, `api_token`.

> Vor dem Deploy prüfen, ob gespeicherte Texte (Vorlagen, Mails, Formular-Texte) andere Blade-Syntax nutzen – sie wird danach als Text angezeigt.

### 3. Blöcke registrieren (Pflicht)

`EditorContent` rendert nur Komponenten aus `nova-texteditor.components`. Ohne Eintrag erscheinen Blöcke als `<x-…>`-Text.

```php
// config/nova-texteditor.php
'components' => [
    'x-orlyapps-salutation' => ['bindings' => [':contact' => '$contact'], 'attributes' => ['type']],
    'x-orlyapps-signature' => ['bindings' => [':user' => '$user'], 'attributes' => ['type']],
    'x-orlyapps-invoice-positions' => ['bindings' => [':invoice' => '$invoice']],
],
```

- `bindings`: feste Blade-Ausdrücke. Ist die Bindung im Text vorhanden, wird sie immer mit diesem Ausdruck neu geschrieben.
- `attributes`: erlaubte Klartext-Attribute (nur Buchstaben).

> **Achtung bei `Config::set(['nova-texteditor' => [...]])`** (z. B. in einem Modul-ServiceProvider): Das ersetzt die gesamte Config inklusive `components`. Stattdessen mergen:
>
> ```php
> Config::set(['nova-texteditor' => [...config('nova-texteditor', []), 'categories' => [...]]]);
> ```

### 4. Eigene Blöcke/Extensions auf tiptap 3 umstellen (Pflicht, falls vorhanden)

Eigene Nodes dürfen tiptap **nicht** selbst bündeln – zwei tiptap-Core-Kopien in einem Editor brechen unter tiptap 3. Stattdessen stellt das Package seine Instanz unter `window.NovaTexteditor` bereit, und `window.TextEditorNotes` nimmt Factories entgegen.

```js
// vorher
import { mergeAttributes, Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import View from "./Salutation.vue";

export default Node.create({ name: "orlyapps-salutation", /* … */ addNodeView() { return VueNodeViewRenderer(View); } });

// nachher
import View from "./Salutation.vue";

export default ({ Node, mergeAttributes, VueNodeViewRenderer }) =>
    Node.create({
        name: "orlyapps-salutation",
        group: "block",
        atom: true,
        draggable: true,
        selectable: true,
        // addAttributes / parseHTML / renderHTML unverändert
        addNodeView() {
            return VueNodeViewRenderer(View);
        },
    });
```

Registrierung bleibt gleich: `window.TextEditorNotes = [InvoicePositions, Signature, Salutation]`.

**Node-Views** nutzen die globale Hülle `<texteditor-block>` (Griff, Hoch/Runter, Entfernen) statt eigenem `node-view-wrapper`:

```vue
<template>
    <texteditor-block title="Anrede" :editor="editor" :node="node" :get-pos="getPos" :delete-node="deleteNode" :selected="selected">
        <!-- optionale Einstellungen, z. B. ein <select> -->
    </texteditor-block>
</template>

<script>
export default {
    props: ["editor", "node", "getPos", "deleteNode", "updateAttributes", "selected", "decorations", "innerDecorations", "extension", "view", "HTMLAttributes"],
};
</script>
```

Keine Imports aus `@tiptap/*` mehr in der App – danach die Abhängigkeiten entfernen und neu bauen:

```bash
npm uninstall @tiptap/core @tiptap/vue-3
npm run production
```

Das gespeicherte HTML (`<x-orlyapps-…>`, `{ platzhalter }`) ist unverändert, bestehende Inhalte müssen nicht migriert werden.

### 5. Vorlagen-API (prüfen)

- Die Routen laufen unter `nova:api`: **nicht angemeldete Aufrufe erhalten 401**. Eigene Aufrufe von außerhalb Novas funktionieren nicht mehr.
- `POST /api/templates` validiert: ohne `name` → 422 (bisher leere Antwort).
- Ist für die Vorlagen-Klasse eine Policy registriert, gelten `create` (Anlegen) und `update` (Überschreiben). Die Buttons im Editor blenden sich entsprechend aus.
- Tipp: `template_class` auf das App-Model zeigen lassen, wenn die Policy dort registriert ist (z. B. `App\Models\Template`).

### 6. Toolbar-Konfiguration (prüfen)

`TextEditor::buttons([...])` nutzt dieselben Namen. Unterschiede:

| Name | vorher | jetzt |
|:--|:--|:--|
| `history` | ein Block | zwei Buttons Rückgängig/Wiederholen |
| `heading`, `textAlign` | mehrere Buttons | je ein Menü |
| `link` | – | neu (öffnet das Link-Menü) |
| `br` | Umbruch der Toolbar | ignoriert (Toolbar bricht responsiv um) |
| `sinkListItem`/`liftListItem` | vertauscht | korrigiert |

Die Hilfe-Kästen unter dem Editor entfallen: `blocks()` und `variables()`/`showHelp()` erzeugen Toolbar-Menüs, zusätzlich erreichbar über „/“.

`withTextTemplates()` bleibt – Textbausteine erscheinen im „/“-Menü statt im Mention-Popup.

### 7. Neue optionale Features

**Platzhalter je Kategorie** (z. B. in der Vorlagen-Resource):

```php
TextEditor::make('Vorlage', 'text')
    ->variableCatalog($catalog, 'category', $this->category);
```

`$catalog`: `['invoice' => [['label' => 'Rechnung', 'variables' => ['rechnungsnummer', …], 'conditional' => false]], …]`. Der Editor reagiert auf das `category-change`-Event des Select-Felds.

**Vorschau mit echten Datensätzen:**

```php
// config/nova-texteditor.php
'template_previewer' => \App\Support\TextEditor\TemplatePreviewer::class,

// Nova-Feld
TextEditor::make('Vorlage', 'text')->templatePreview();
```

```php
use Illuminate\Database\Eloquent\Model;
use Orlyapps\NovaTexteditor\Preview\NovaTemplatePreviewer;

class TemplatePreviewer extends NovaTemplatePreviewer
{
    protected function sourceModels(string $category): array
    {
        return ['invoice' => [Invoice::class => 'Rechnung']][$category] ?? [];
    }

    protected function contactFor(?Model $model): mixed
    {
        return $model?->customer;
    }
}
```

Die Vorschau nutzt Novas `buildIndexQuery` (Suche + `indexQuery`-Sichtbarkeit) und die `view`-Policy der Models und erfordert das `viewAny`-Recht auf die Vorlagen-Klasse. Platzhalter kommen aus `getTextEditorVariablesAttribute()`, Blade-Daten zusätzlich aus `getTextEditorRenderPropsAttribute()`.

### 8. Checkliste

- [ ] `composer update orlyapps/nova-texteditor` auf `dev-feature/v6`
- [ ] alle `Blade::render(<Editor-Text>)` → `EditorContent::render()`
- [ ] `nova-texteditor.components` gepflegt, `Config::set` mergt statt ersetzt
- [ ] eigene Nodes als Factories, `@tiptap/*` aus der App entfernt, Assets neu gebaut
- [ ] Produktionsdaten auf Blade-Syntax außer einfachen Ausgaben geprüft
- [ ] nach dem Deploy `php artisan route:cache`
- [ ] Browser-Check: Mail/Brief/Rechnung mit Anrede, Signatur, Rechnungspositionen
