<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Illuminate\Http\Request;
use Orlyapps\NovaTexteditor\Http\Resources\TemplateResource;

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
        $templateClass = config('nova-texteditor.template_class');

        try {
            $templates = $templateClass::query()
                ->whereIn('category', $categories)
                ->where(function ($query) use ($request) {
                    $query->whereNull('user_id')->orWhere('user_id', $request->user()->id);
                })
                ->get();

            return TemplateResource::collection($templates);
        } catch (\Throwable $th) {
            return TemplateResource::collection(collect());
        }
    }

    public function store(Request $request)
    {
        if (blank($request->name)) {
            return;
        }
        $category = str($request->category)->explode(',')->first();
        $templateClass = config('nova-texteditor.template_class');

        $template = $templateClass::create([
            'name' => $request->name,
            'category' => $category,
            'subject' => $request->subject,
            'text' => $request->text,
        ]);

        return TemplateResource::make($template);
    }
}
