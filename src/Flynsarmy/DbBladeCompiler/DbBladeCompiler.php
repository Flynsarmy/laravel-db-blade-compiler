<?php namespace Flynsarmy\DbBladeCompiler;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\CompilerInterface;

class DbBladeCompiler extends BladeCompiler implements CompilerInterface
{

    /** @var Repository */
    protected $config;

    public function __construct($filesystem, $cache_path, Repository $config)
    {
        // Get Current Blade Instance
        $blade = app('view')->getEngineResolver()->resolve('blade')->getCompiler();

        parent::__construct($filesystem, $cache_path);
        $this->rawTags          = $blade->rawTags;
        $this->contentTags      = array_map('stripcslashes', $blade->contentTags);
        $this->escapedTags      = array_map('stripcslashes', $blade->escapedTags);
        $this->extensions       = $blade->getExtensions();
        $this->customDirectives = $blade->getCustomDirectives();
        $this->config           = $config;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  Model $path
     * @return void
     */
    public function compile($path = null)
    {
        if (is_null($path)) {
            return;
        }

        // Defaults to '__db_blade_compiler_content_field' property
        $property = $this->config->get('db-blade-compiler.model_property');
        // Defaults to 'contents' column
        $column = $path->{$property};
        // Grab the column contents
        $string = $path->{$column};
        // Compile to PHP
        $contents = $this->compileString($string);

        if (!is_null($this->cachePath)) {
            $this->files->put($this->getCompiledPath($path), $contents);
        }
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  Model $model
     * @return string
     */
    public function getCompiledPath($model)
    {
        /*
         * A unique path for the given model instance must be generated
         * so the view has a place to cache. The following generates a
         * path using almost the same logic as Blueprint::createIndexName()
         *
         * e.g db_table_name_id_4
         */
        $field = $this->config->get('db-blade-compiler.model_property');
        $path  = 'db_' . $model->getTable() . '_' . $model->{$field} . '_';
        if (is_null($model->primaryKey)) {
            $path .= $model->id;
        } else if (is_array($model->primaryKey)) {
            $path .= implode('_', $model->primaryKey);
        } else {
            $path .= $model->primaryKey;
        }

        $path = strtolower(str_replace(array('-', '.'), '_', $path));

        return $this->cachePath . '/' . md5($path);
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string $path
     * @return bool
     */
    public function isExpired($path)
    {
        if (!$this->config->get('db-blade-compiler.cache')) {
            return true;
        }
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!$this->cachePath || !$this->files->exists($compiled)) {
            return true;
        }

        $lastModified = strtotime($path->updated_at);

        // if ( $lastModified >= $this->files->lastModified($compiled) )
        //     echo $lastModified . ' ('.date('r', $lastModified).') > ' . $this->files->lastModified($compiled) . ' ('.date('r', $this->files->lastModified($compiled)).')<br/>';

        return $lastModified >= $this->files->lastModified($compiled);
    }
}
