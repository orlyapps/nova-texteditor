<?php

namespace Orlyapps\NovaTexteditor;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Orlyapps\NovaTexteditor\NovaTexteditor
 */
class NovaTexteditorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'nova-texteditor';
    }
}
