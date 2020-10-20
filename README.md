This library provides utilities and features for parsing, updating, and writing Laravel style PHP configuration files.
This project is very much an early-prototype and work-in-progress, there are definitely scenarios and edge cases that have
not been fully worked through and tested.

A Laravel `Config::` wrapper/integration is not yet available.

## Basic Usage

Given the following input configuration file:

```php
return [

    'key' => env('APP_KEY'),

];
```

We could do something like the following:

```php
use Stillat\WolfPack\ConfigUpdater;

$updater = new ConfigUpdater();
$updater->open('./path/to/config.php');
$updater->update([
    'key' => 'new-key',
    'new.deeply.nested.key' => [
        'hello',
        'world'
    ]
]);

$newConfigContents = $updater->getDocument();
```

After running, `$newConfigContents` would contain output similar to the following:

```php
<?php

return [

    'key' => env('APP_KEY', 'new-key'),
    'new' => [
        'deeply' => [
            'nested' => [
                'key' => [
                    'hello',
                    'world',
                ],
            ],
        ],
    ],

];
```

And yes, it did add the `new-key` value as the default value for the `env` call instead of replacing the lookup key :)

## Features

* Attempts to preserve most file formatting,
* Handles adding new simple keys,
* Handles adding new deeply nested keys,
* Allows for appending configuration files to an existing configuration array,
* Allows for overwriting configuration files in an existing configuration array,

## Road Map

There will undoubtedly be changes required overtime, and if you find something not working, please open an issue with
the input you are supplying and the expected results. Bonus points if you add a test case for it :)

This package is not yet available through packagist until things, but will be once things are validated it a bit more.
A nice `Config` wrapper for use within Laravel projects will also come at some point.

## License

MIT License. See LICENSE.MD
