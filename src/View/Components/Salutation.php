<?php

namespace Orlyapps\NovaTexteditor\View\Components;

use Illuminate\View\Component;

class Salutation extends Component
{

    public function __construct(public $contact, public $type)
    {
    }

    public function render()
    {
        if ($this->type === 'lastname') {
            return <<<'blade'
            <p><strong>{{ $contact->salutationWithLastname ?? __('Sehr geehrte Damen und Herren') }},</strong></p>
        blade;
        } elseif ($this->type === 'firstname') {
            return <<<'blade'
            <p><strong>{{ $contact->salutationWithFirstname() ?? __('Sehr geehrte Damen und Herren') }},</strong></p>
        blade;
        } elseif ($this->type === 'dear') {
            return <<<'blade'
            <p><strong>{{ $contact->salutationDear ?? __('Sehr geehrte Damen und Herren') }},</strong></p>
        blade;
        } elseif ($this->type === 'general') {
            return <<<'blade'
            <p><strong>Sehr geehrte Damen und Herren</strong></p>
        blade;
        } elseif ($this->type === 'du') {
            return <<<'blade'
            <p><strong>{{ $contact->salutationInformal }}</strong></p>
        blade;
        }

        return <<<'blade'
           <p><strong>Sehr geehrte Damen und Herren</strong></p>
        blade;
    }
}
