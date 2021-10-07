<?php

namespace Orlyapps\NovaTexteditor\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TextTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'shortcode' => $this->shortcode,
            'name' => $this->name,
            'text' => $this->text,
        ];
    }
}
