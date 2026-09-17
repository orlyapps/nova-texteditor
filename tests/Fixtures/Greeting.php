<?php

namespace Orlyapps\NovaTexteditor\Tests\Fixtures;

use Illuminate\View\Component;

class Greeting extends Component
{
    public function __construct(public mixed $contact = null, public ?string $type = null) {}

    public function render(): string
    {
        return '<p>Hallo {{ $contact ?? "Gast" }} ({{ $type }})</p>';
    }
}
