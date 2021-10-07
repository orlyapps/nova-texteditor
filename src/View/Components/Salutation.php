<?php

namespace Orlyapps\NovaTexteditor\View\Components;

use Illuminate\View\Component;

class Salutation extends Component
{
    public $contact;
    public $type;

    public function __construct($contact, $type)
    {
        $this->contact = $contact;
        $this->type = $type;
    }

    public function render()
    {
        if ($this->type === 'lastname') {
            return <<<'blade'
            <p><strong>{{ optional($contact)->getSalutation() ?? __('Sehr geehrte Damen und Herren') }},</strong></p>
        blade;
        } elseif ($this->type === 'firstname') {
            return <<<'blade'
            <p><strong>{{ optional($contact)->getSalutationWithFirstname() ?? __('Sehr geehrte Damen und Herren') }},</strong></p>
        blade;
        } elseif ($this->type === 'general') {
            return <<<'blade'
            <p><strong>Sehr geehrte Damen und Herren</strong></p>
        blade;
        } elseif ($this->type === 'du') {
            return <<<'blade'
            <p><strong>Hallo {{ $contact->getSalutationInformal() }}</strong></p>
        blade;
        }

        return <<<'blade'
           <p><strong>Sehr geehrte Damen und Herren</strong></p>
        blade;
    }
}
