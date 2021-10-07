<?php

namespace Orlyapps\NovaTexteditor;

use Orlyapps\NovaTexteditor\Nodes\PageBreakNode;
use Orlyapps\NovaTexteditor\Nodes\ParagraphNode;
use Orlyapps\NovaTexteditor\Nodes\SalutationNode;
use Orlyapps\NovaTexteditor\Nodes\SignatureNode;
use ProseMirrorToHtml\Marks\Bold;
use ProseMirrorToHtml\Marks\Code;
use ProseMirrorToHtml\Marks\Italic;
use ProseMirrorToHtml\Marks\Link;
use ProseMirrorToHtml\Marks\Subscript;
use ProseMirrorToHtml\Marks\Superscript;
use ProseMirrorToHtml\Marks\Underline;
use ProseMirrorToHtml\Nodes\Blockquote;
use ProseMirrorToHtml\Nodes\BulletList;
use ProseMirrorToHtml\Nodes\CodeBlock;
use ProseMirrorToHtml\Nodes\HardBreak;
use ProseMirrorToHtml\Nodes\Heading;
use ProseMirrorToHtml\Nodes\ListItem;
use ProseMirrorToHtml\Nodes\OrderedList;
use ProseMirrorToHtml\Nodes\Table;
use ProseMirrorToHtml\Nodes\TableCell;
use ProseMirrorToHtml\Nodes\TableHeader;
use ProseMirrorToHtml\Nodes\TableRow;

class Renderer extends \ProseMirrorToHtml\Renderer
{
    protected $marks = [
        Bold::class,
        Code::class,
        Italic::class,
        Link::class,
        Subscript::class,
        Superscript::class,
        Underline::class,
    ];

    protected $nodes = [
        Blockquote::class,
        BulletList::class,
        CodeBlock::class,
        HardBreak::class,
        Heading::class,
        ListItem::class,
        OrderedList::class,
        Table::class,
        TableCell::class,
        TableHeader::class,
        TableRow::class,

        // Custom Nodes
        ParagraphNode::class,
        PageBreakNode::class,
        SignatureNode::class,
        SalutationNode::class,

    ];
}
