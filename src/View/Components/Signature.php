<?php

namespace Orlyapps\NovaTexteditor\View\Components;

use Illuminate\View\Component;

class Signature extends Component
{
    public $user;
    public $type;

    public function __construct($user, $type = null)
    {
        $this->user = $user;
        $this->type = $type;
    }

    public function render()
    {
        return view('nova-texteditor::components.signature');
    }
}
