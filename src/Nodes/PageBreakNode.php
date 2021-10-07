<?php

namespace Orlyapps\NovaTexteditor\Nodes;

class PageBreakNode extends \ProseMirrorToHtml\Nodes\Node
{
    public function matching()
    {
        return $this->node->type === 'page-break';
    }

    public function tag()
    {
        return 'page-break';
    }

    public function selfClosing()
    {
        return false;
    }
}
