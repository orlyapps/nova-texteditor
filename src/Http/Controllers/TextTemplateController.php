<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Orlyapps\NovaTexteditor\Models\TextTemplate;
use Illuminate\Http\Request;
use Orlyapps\NovaTexteditor\Http\Resources\TextTemplateResource;

class TextTemplateController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TextTemplateResource::collection(TextTemplate::all());
    }
}
