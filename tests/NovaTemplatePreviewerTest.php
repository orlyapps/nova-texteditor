<?php

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Orlyapps\NovaTexteditor\Tests\Fixtures\Article;
use Orlyapps\NovaTexteditor\Tests\Fixtures\ArticlePreviewer;
use Orlyapps\NovaTexteditor\Tests\Fixtures\ArticleResource;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Nova::resources([ArticleResource::class]);
    $this->previewer = new ArticlePreviewer;
});

test('Quellen enthalten nur existierende Models mit Nova-Resource', function () {
    // Act
    $sources = $this->previewer->sources('articles');

    // Assert
    expect($sources)->toBe([['key' => Article::class, 'label' => 'Artikel']])
        ->and($this->previewer->sources('unbekannt'))->toBe([]);
});

test('Datensätze folgen Novas Sichtbarkeit und Suche, neueste zuerst', function () {
    // Arrange
    $first = Article::create(['title' => 'Welpenkurs']);
    Article::create(['title' => 'Versteckt', 'hidden' => true]);
    $second = Article::create(['title' => 'Mantrailing']);

    // Act
    $all = $this->previewer->records('articles', Article::class);
    $search = $this->previewer->records('articles', Article::class, 'Welpen');

    // Assert
    expect($all)->toBe([['id' => $second->id, 'label' => 'Mantrailing'], ['id' => $first->id, 'label' => 'Welpenkurs']])
        ->and($search)->toBe([['id' => $first->id, 'label' => 'Welpenkurs']]);
});

test('Datensätze ohne view-Recht werden weder angeboten noch gerendert', function () {
    // Arrange
    $article = Article::create(['title' => 'Geheim']);
    Gate::policy(Article::class, ArticlePolicyDenyingView::class);

    // Act & Assert
    expect($this->previewer->records('articles', Article::class))->toBe([])
        ->and(fn () => $this->previewer->render('articles', Article::class, $article->id, null, '<p>x</p>'))
        ->toThrow(HttpException::class);
});

test('die Vorschau ersetzt Model- und Zusatz-Platzhalter in Betreff und Text', function () {
    // Arrange
    $article = Article::create(['title' => 'Welpenkurs', 'author' => 'Erika']);

    // Act
    $result = $this->previewer->render('articles', Article::class, $article->id, '<p>Neu: { artikel-titel }</p>', '<p>{artikel-titel} Stufe { stufe } {{ $contact }}</p>');

    // Assert
    expect($result)->toBe([
        'subject' => 'Neu: Welpenkurs',
        'html' => '<p>Welpenkurs Stufe 2 Erika</p>',
        'recipient' => null,
    ]);
});

test('ohne Datensatz wird der Text ohne Model-Platzhalter gerendert', function () {
    // Act
    $result = $this->previewer->render('articles', null, null, null, '<p>{ artikel-titel }</p>');

    // Assert
    expect($result['html'])->toBe('<p>{ artikel-titel }</p>');
});

test('eine Quelle außerhalb der Kategorie wird abgelehnt', function () {
    // Arrange
    $article = Article::create(['title' => 'Welpenkurs']);

    // Act & Assert
    expect(fn () => $this->previewer->render('andere', Article::class, $article->id, null, '<p>x</p>'))
        ->toThrow(HttpException::class);
});

test('Blade-Code im Vorschau-Text wird nicht ausgeführt', function () {
    // Act
    $result = $this->previewer->render('articles', null, null, null, '<p>{{ strtoupper("xy") }}</p>');

    // Assert
    expect($result['html'])->toBe('<p>{{ strtoupper("xy") }}</p>');
});

class ArticlePolicyDenyingView
{
    public function view(mixed $user, Article $article): Response
    {
        return Response::deny();
    }
}
