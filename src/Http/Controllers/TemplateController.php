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
        $categories = str($request->category)->explode(',');
        $templates = Template::query()
            ->whereIn('category', $categories)
            ->where(function ($query) use ($request) {
                $query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })
            ->get();

        return TemplateResource::collection($templates);
    }

    public function store(Request $request)
    {
        $template = Template::create($request->all());

        return TemplateResource::make($template);
    }
}
