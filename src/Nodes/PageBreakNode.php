<?php

namespace Orlyapps\NovaTexteditor\Nodes;

use Tiptap\Core\Node;

class PageBreakNode extends Node
{
    public static $name = 'page-break';

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return [
            'page-break',
        ];
    }
}
