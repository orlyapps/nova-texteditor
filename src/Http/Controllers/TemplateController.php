<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Illuminate\Http\Request;
use Orlyapps\NovaTexteditor\Http\Resources\TemplateResource;
use Orlyapps\NovaTexteditor\Models\Template;

class TemplateController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $templates = Template::query()
            ->where('category', $request->category)
            ->where(function ($query) use ($request) {
                $query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })
            ->get();
        return TemplateResource::collection($templates);
    }
}
