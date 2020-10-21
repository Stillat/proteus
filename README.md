This library provides utilities and features for parsing, updating, and writing Laravel style PHP configuration files.
This project is very much an early-prototype and work-in-progress, there are definitely scenarios and edge cases that have
not been fully worked through and tested.

## Basic Usage

The `ConfigWriter` facade will automatically load and manage your existing Laravel configuration files, and dramatically simplifies the usage of the configuration writer:

```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;

// Change the default locale to 'fr' and save to disk:
ConfigWriter::write('app.locale', 'fr');

// Add a new nested entry:
ConfigWriter::write('app.new.entry', 'new-value');

// And then update that new entry:
ConfigWriter::write('app.new.entry', 'updated-value');

```



```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;


ConfigWriter::writeMany('app', [
    'locale' => 'fr',
    'timezone' => 'Europe/Paris'
]);


```

If you only want the document contents (without writing to disk), you may use the `preview` and `previewMany` counterparts:


```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;

$document = ConfigWriter::preview('app.locale', 'fr');

$document = ConfigWriter::previewMany('app', [
    'locale' => 'fr',
    'timezone' => 'Europe/Paris'
]);


```

## Preventing Changes to Configuration Items

You may use the `guard` method to prevent changes to specific configuration entries:

```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::guard('app.key');

```

If a change is detected for `app.key`, an instance of `GuardedConfigurationMutationException` will be thrown.

You may also restrict changes on entire configuration namespaces:


```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::guard('app.*');

```

Or to just a sub-section of the configuration:


```php
<?php

Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::guard('app.providers*');

```

## Intermediate Usage

Given the following input configuration file:

```php
return [

    'key' => env('APP_KEY'),

];
```

We could do something like the following:

```php
use Stillat\Proteus\ConfigUpdater;

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
* Simple `Config` facade extension

## Road Map

There will undoubtedly be changes required overtime, and if you find something not working, please open an issue with
the input you are supplying and the expected results. Bonus points if you add a test case for it :)

This package is not yet available through packagist until things are tested/validated a bit more, but will be eventually.

## License

MIT License. See LICENSE.MD
