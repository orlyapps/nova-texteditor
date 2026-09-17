<?php

use Illuminate\Support\Facades\Blade;
use Orlyapps\NovaTexteditor\EditorContent;
use Orlyapps\NovaTexteditor\Tests\Fixtures\Greeting;

beforeEach(function () {
    Blade::component('test-greeting', Greeting::class);
    config(['nova-texteditor.components' => [
        'x-test-greeting' => ['bindings' => [':contact' => '$contact'], 'attributes' => ['type']],
    ]]);
});

test('Blade- und PHP-Syntax wird als Text ausgegeben statt ausgeführt', function (string $input, string $expected) {
    // Act
    $html = EditorContent::render($input);

    // Assert
    expect($html)->toBe($expected);
})->with([
    'Echo' => ['<p>{{ strtoupper("xy") }}</p>', '<p>{{ strtoupper("xy") }}</p>'],
    'Raw-Echo' => ['<p>{!! strtoupper("xy") !!}</p>', '<p>{!! strtoupper("xy") !!}</p>'],
    'Dreifach-Echo' => ['<p>{{{ strtoupper("xy") }}}</p>', '<p>{{{ strtoupper("xy") }}}</p>'],
    'Direktive' => ['<p>@php echo strtoupper("xy"); @endphp</p>', '<p>@php echo strtoupper("xy"); @endphp</p>'],
    'Bereits escaptes Echo' => ['<p>@{{ strtoupper("xy") }}</p>', '<p>{{ strtoupper("xy") }}</p>'],
    'E-Mail-Adresse' => ['<p>info@hundeschule.de</p>', '<p>info@hundeschule.de</p>'],
    'Leerer Text' => ['', ''],
]);

test('PHP-Tags werden nicht ausgeführt', function () {
    // Act
    $html = EditorContent::render('<p><?php echo strtoupper("xy"); ?></p>');

    // Assert
    expect($html)->not->toContain('XY')->toContain('&lt;?php');
});

test('nicht registrierte Komponenten werden als Text ausgegeben', function (string $tag) {
    // Act
    $html = EditorContent::render($tag);

    // Assert
    expect($html)->not->toContain('XY')->toStartWith('&lt;');
})->with([
    'dynamic-component' => ['<x-dynamic-component :component="strtoupper(\'xy\')"></x-dynamic-component>'],
    'Doppelpunkt-Syntax' => ['<x:test-greeting :contact="strtoupper(\'xy\')"></x:test-greeting>'],
    'Livewire' => ['<livewire:foo :bar="strtoupper(\'xy\')" />'],
]);

test('registrierte Komponenten rendern mit festen Bindungen, manipulierte Ausdrücke werden ersetzt', function () {
    // Act
    $html = EditorContent::render(
        '<x-test-greeting type="d-u 1!" :contact="strtoupper(\'xy\')"></x-test-greeting>',
        ['contact' => 'Erika']
    );

    // Assert
    expect($html)->toBe('<p>Hallo Erika (du)</p>');
});

test('ohne Registrierung in der Config wird auch ein bekannter Block als Text ausgegeben', function () {
    // Arrange
    config(['nova-texteditor.components' => []]);

    // Act
    $html = EditorContent::render('<x-test-greeting :contact="$contact"></x-test-greeting>', ['contact' => 'Erika']);

    // Assert
    expect($html)->toStartWith('&lt;x-test-greeting');
});

test('einfache Ausgaben von Variablen und Eigenschaften bleiben erlaubt', function () {
    // Act
    $html = EditorContent::render(
        '<p>{{ $contact->firstname }} / {{ $contact?->firstname }} / {{ $name }}</p>',
        ['contact' => (object) ['firstname' => 'Anna'], 'name' => 'Bello']
    );

    // Assert
    expect($html)->toBe('<p>Anna / Anna / Bello</p>');
});

test('Aufrufe, Ausdrücke und sensible Eigenschaften werden in Ausgaben nicht ausgeführt', function (string $input) {
    // Act
    $html = EditorContent::render($input, ['contact' => (object) ['firstname' => 'Anna', 'password' => 'hash']]);

    // Assert
    expect($html)->toBe($input);
})->with([
    'Methodenaufruf' => ['<p>{{ $contact->getKey() }}</p>'],
    'Passwort' => ['<p>{{ $contact->password }}</p>'],
    'Ausdruck' => ['<p>{{ $contact->firstname . strtoupper("xy") }}</p>'],
]);
