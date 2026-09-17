<?php

namespace Orlyapps\NovaTexteditor\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Orlyapps\NovaTexteditor\Contracts\PreviewsTemplates;
use Orlyapps\NovaTexteditor\Support\TemplateGate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TemplatePreviewController
{
    public function records(Request $request): JsonResponse
    {
        $previewer = $this->previewer();

        $validated = $request->validate([
            'category' => ['required', 'string'],
            'source' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $sources = $previewer->sources($validated['category']);
        $source = $validated['source'] ?? $sources[0]['key'] ?? null;

        return response()->json([
            'sources' => $sources,
            'source' => $source,
            'records' => $source ? $previewer->records($validated['category'], $source, $validated['search'] ?? null) : [],
        ]);
    }

    public function render(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'source' => ['nullable', 'string'],
            'record' => ['nullable'],
            'subject' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
        ]);

        try {
            return response()->json($this->previewer()->render(
                $validated['category'],
                $validated['source'] ?? null,
                $validated['record'] ?? null,
                $validated['subject'] ?? null,
                $validated['text'] ?? null,
            ));
        } catch (ModelNotFoundException|AuthorizationException|HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Die Vorschau konnte mit diesem Datensatz nicht erzeugt werden.'], 422);
        }
    }

    protected function previewer(): PreviewsTemplates
    {
        $previewerClass = config('nova-texteditor.template_previewer');

        abort_unless($previewerClass, 404);
        abort_unless(TemplateGate::allows('viewAny', config('nova-texteditor.template_class')), 403);

        return app($previewerClass);
    }
}
