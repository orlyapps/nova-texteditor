<?php

namespace Orlyapps\NovaTexteditor\View\Components;

use Illuminate\View\Component;

class InvoicePositions extends Component
{
    public $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function render()
    {
        return view('nova-texteditor::components.invoice-positions');
    }
}
