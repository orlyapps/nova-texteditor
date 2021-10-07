<?php

namespace Orlyapps\NovaTexteditor\Nodes;

class SalutationNode extends \ProseMirrorToHtml\Nodes\Node
{
    public function matching()
    {
        return $this->node->type === 'salutation';
    }

    public function tag()
    {
        return [
            'x-orlyapps-salutation' => [
                'attrs' => [
                    ':contact' => '$contact',
                    'type' => $this->node->attrs->type ?? 'lastname',
                ],
                'tag' => 'x-orlyapps-salutation',
            ],
        ];
    }

    public function selfClosing()
    {
        return false;
    }
}
