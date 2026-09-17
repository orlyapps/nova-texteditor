<?php

namespace Orlyapps\NovaTexteditor\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Orlyapps\NovaTexteditor\Support\TemplateGate;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            // Ohne Übersetzung null statt des rohen Schlüssels (z. B. „course“) — der Editor blendet die Kategorie dann aus.
            'category_label' => config('nova-texteditor.categories')[$this->category] ?? null,
            'matches' => in_array($this->category, str($request->category)->explode(',')->all(), true),
            'subject' => $this->subject,
            'text' => $this->text,
            'can_update' => TemplateGate::allows('update', $this->resource),
        ];
    }
}
