<?php

namespace Orlyapps\NovaTexteditor\Nodes;

use Tiptap\Core\Node;

class SignatureNode extends Node
{
    public static $name = 'signature';

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return [
            'x-signature',
             [
                ':user' => '$user',
                'type' => $node->attrs->type ?? 'user',
            ],
        ];
    }
}
