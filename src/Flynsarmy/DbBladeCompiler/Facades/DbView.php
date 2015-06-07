<?php namespace Flynsarmy\DbBladeCompiler\Facades;

use Illuminate\Support\Facades\Facade;

class DbView extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
        {
        return 'dbview';
        }

}