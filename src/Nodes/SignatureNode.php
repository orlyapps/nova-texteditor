<?php

namespace Orlyapps\NovaTexteditor\Nodes;

class SignatureNode extends \ProseMirrorToHtml\Nodes\Node
{
    public function matching()
    {
        return $this->node->type === 'signature';
    }

    public function tag()
    {
        return [
            'x-orlyapps-signature' => [
                'attrs' => [
                    ':user' => '$user',
                    'type' => $this->node->attrs->type ?? 'user'
                ],
                'tag' => 'x-orlyapps-signature'
            ]
        ];
    }

    public function selfClosing()
    {
        return false;
    }
}
