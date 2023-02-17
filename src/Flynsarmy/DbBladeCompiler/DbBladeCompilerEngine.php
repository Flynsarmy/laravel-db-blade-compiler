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
    
    /**
     * @param string $compiled_path
     * @param array $data
     * @return string
     */
    public function getContent(string $compiled_path, array $data)
    {
        return $this->evaluatePath($compiled_path, $data);
    }
}
