<?php namespace Flynsarmy\DbBladeCompiler;

use View, Closure, ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Model;

class DbView extends \Illuminate\View\View implements ArrayAccess, Renderable
{

    protected $content_field = null;

    /** @var Repository */
    protected $config;

    /**
     * DbView constructor.
     *
     * @param Repository            $config
     * @param DbBladeCompilerEngine $engine
     */
    public function __construct(Repository $config, DbBladeCompilerEngine $engine)
    {
        $this->config = $config;
        $this->engine = $engine;
    }

    /**
     * Get a evaluated view contents for the given view.
     *
     * @param  Model  $model
     * @param  array  $data
     * @param  array  $mergeData
     * @param  string $content_field
     * @return DbView
     */
    public function make(Model $model, $data = array(), $mergeData = array(), $content_field = null)
    {
        $this->path = $model;
        $this->data = array_merge($mergeData, $this->parseData($data));
        if (!is_null($content_field)) {
            $this->content_field = $content_field;
        } else {
            $this->content_field = $this->config->get('db-blade-compiler.model_default_field');
        }

        return $this;
    }

    /**
     * @param  string $content_field
     * @return DbView
     */
    public function field($content_field)
    {
        $this->content_field = $content_field;

        return $this;
    }

    /**
     * Get the string contents of the view.
     *
     * @param  callable $callback
     * @return string
     */
    public function render(callable $callback = null)
    {
        $contents = $this->renderContents();

        $response = isset($callback) ? $callback($this, $contents) : null;

        // Once we have the contents of the view, we will flush the sections if we are
        // done rendering all views so that there is nothing left hanging over when
        // anothoer view is rendered in the future by the application developers.
        // Before flushing, check Laravel version for correct method use
        if ( version_compare(app()->version(), '5.4.0') >= 0 ) 
            View::flushStateIfDoneRendering();
        else 
            View::flushSectionsIfDoneRendering();

        return $response ?: $contents;
    }

    /**
     * Get the contents of the view instance.
     *
     * @return string
     */
    protected function renderContents()
    {
        // We will keep track of the amount of views being rendered so we can flush
        // the section after the complete rendering operation is done. This will
        // clear out the sections for any separate views that may be rendered.
        View::incrementRender();

        $contents = $this->getContents();

        // Once we've finished rendering the view, we'll decrement the render count
        // so that each sections get flushed out next time a view is created and
        // no old sections are staying around in the memory of an environment.
        View::decrementRender();

        return $contents;
    }

    protected function getContents()
    {
        $field                = $this->config->get('db-blade-compiler.model_property');
        $this->path->{$field} = $this->content_field;

        return parent::getContents();
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array
     */
    public function gatherData()
    {
        $data = array_merge(View::getShared(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    /**
     * Add a view instance to the view data.
     *
     * @param  string $key
     * @param  string $view
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function nest($key, $view, array $data = array())
    {
        return $this->with($key, View::make($view, $data));
    }

    /**
     * Determine if a piece of data is bound.
     *
     * @param  string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     *
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Set a piece of data on the view.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Unset a piece of data from the view.
     *
     * @param  string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}

