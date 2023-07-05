<?php

namespace Orlyapps\NovaTexteditor\Nodes;

use Tiptap\Core\Node;

class SalutationNode extends Node
{
    public static $name = 'salutation';

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return [
            'x-salutation',
             [
                ':contact' => '$contact',
                'type' => $node->attrs->type ?? 'lastname',
            ],
        ];
    }
}
