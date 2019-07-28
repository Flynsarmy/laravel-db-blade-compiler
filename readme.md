## DB Blade Compiler


### Render Blade templates from Eloquent Model Fields

This package generates and returns a compiled view from a blade-syntax field in your Eloquent model.


### Installation (Laravel v < 5)

Require this package in your composer.json and run composer update (or run `composer require flynsarmy/db-blade-compiler:1.*` directly):

    "flynsarmy/db-blade-compiler": "1.*"

After updating composer, add the ServiceProvider to the providers array in app/config/app.php

    'Flynsarmy\DbBladeCompiler\DbBladeCompilerServiceProvider',

and the Facade to the aliases array in the same file

    'DbView'          => 'Flynsarmy\DbBladeCompiler\Facades\DbView',

You can also optionally publish the config-file

    php artisan config:publish flynsarmy/db-blade-compiler


### Installation (Laravel 5.x)

Require this package in your composer.json and run composer update (or run `composer require flynsarmy/db-blade-compiler:2.*` directly):

    "flynsarmy/db-blade-compiler": "*"

The DbBladeCompilerServiceProvider is auto-discovered and registered by default, but if you want to register it yourself:  
add the ServiceProvider to the providers array in app/config/app.php

    'Flynsarmy\DbBladeCompiler\DbBladeCompilerServiceProvider',

and the DbView facade is also auto-discovered, but if you want to add it manually:  
add the Facade to the aliases array in config/app.php

    'DbView'          => 'Flynsarmy\DbBladeCompiler\Facades\DbView',

You have to also publish the config-file

    php artisan vendor:publish --provider="Flynsarmy\DbBladeCompiler\DbBladeCompilerServiceProvider"


### Usage

This package offers a `DbView` facade with the same syntax as `View` but accepts a Model instance instead of path to view.

    $template = Template::first();
    return DbView::make($template)->with(['foo' => 'Bar'])->render();

Because you're passing a model to `DbView::make()`, db-blade-compiler needs to know which field to compile. By default this is `content` however you can set the field used with either of the following methods:

    return DbView::make($template, ['foo' => 'Bar'], [], 'excerpt')->render();
    return DbView::make($template)->field('excerpt')->with(['foo' => 'Bar'])->render();

You may set the default column used in the package config.
You can enable using cache in compiling view from a blade-syntax field in your Eloquent model operation by enabling cache config in package config. By default this option is disabled.


### License

db-blade-compiler is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
