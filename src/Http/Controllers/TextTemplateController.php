<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Illuminate\Http\Request;
use Orlyapps\NovaTexteditor\Http\Resources\TextTemplateResource;
use Orlyapps\NovaTexteditor\Models\TextTemplate;

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
