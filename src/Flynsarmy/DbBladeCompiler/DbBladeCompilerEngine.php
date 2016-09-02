<?php namespace Flynsarmy\DbBladeCompiler;

use Illuminate\View\Engines\CompilerEngine;

class DbBladeCompilerEngine extends CompilerEngine
{
    /**
     * DbBladeCompilerEngine constructor.
     *
     * @param DbBladeCompiler $bladeCompiler
     */
    public function __construct(DbBladeCompiler $bladeCompiler)
    {
        parent::__construct($bladeCompiler);
    }
}
