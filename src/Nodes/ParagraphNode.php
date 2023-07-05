<?php

namespace Orlyapps\NovaTexteditor\Nodes;

use Tiptap\Core\Node;

class ParagraphNode extends Node
{
    public function matching()
    {
        return $this->node->type === 'paragraph';
    }

    public function selfClosing()
    {
        return false;
    }

    public function tag()
    {
        if (isset($this->node->attrs)) {
            return [
                'p' => [
                    'attrs' => [
                        'class' => 'text-' . $this->node->attrs->textAlign,
                    ],
                    'tag' => 'p',
                ],
            ];
        } else {
            return 'p';
        }
    }
}
