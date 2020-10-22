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

You may also write many configuration items at once:

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

For more control to remove, replace, and even merge array values with existing configuration values, we can use the `edit` helper method. This helper method expects a configuration namespace, and returns access to a convenient wrapper to perform a variety of configuration changes.

In the following example, we will start an edit instance for the `app` configuration namespace, modify a few values, and save the results to a `$document` variable:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

$document = ConfigWriter::edit('app')
    ->set('locale', 'fr')
    ->set('timezone', 'Europe/Paris')
    ->preview();
```

We can save ourselves some keystrokes by supplying a key/value pair to the `set` method:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

$document = ConfigWriter::edit('app')
    ->set([
        'locale' => 'fr',
        'timezone' => 'Europe/Paris'  
    ])->preview();
```

To save the changes instead of assigning them to a value, we can call `save` instead of `preview`:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::edit('app')
    ->set([
        'locale' => 'fr',
        'timezone' => 'Europe/Paris'  
    ])->save();
```

### Removing Existing Configuration Values

You may use the `remove` method to remove an existing configuration item:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::edit('app')-remove('locale')->save();
```

This method will also remove the configuration key from the configuration file - not just the value!

### Replacing an Existing Configuration Value

You may use the `replace` method to completely replace an existing configuration item:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::edit('app')-replace('providers', [
    // The new list of providers.
])->save();
```

### Merging Array Configuration Items

You may use the `merge` method to add new values to an existing configuration item. For example, the following would add the `SomeProvider` and `SomeOtherProvider` class providers to the list of application providers:

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::edit('app')-merge('providers', [
    SomeProvider::class,
    SomeOtherProvider::class
])->save();
```

The `merge` method will make sure there are no duplicates in the resulting configuration values.

### Performing Multiple Actions

You may perform multiple actions at once by chaining them. Chained actions are performed in the order they are specified.

```php
<?php

use Stillat\Proteus\Support\Facades\ConfigWriter;

ConfigWriter::edit('app')
    ->set([
        'locale' => 'fr',
        'timezone' => 'Europe/Paris'  
    ])->merge('providers', [
        SomeProvider::class,
        SomeOtherProvider::class
    ])->set('fallback_locale', 'fr')->save();
````


## Advanced Usage

Given the following input configuration file:

```php
return [

    'key' => env('APP_KEY'),

];
```

We can manually create a configuration updater and apply our changes manually:

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
* Simple `ConfigWriter` facade

## Road Map

There will undoubtedly be changes required overtime, and if you find something not working, please open an issue with
the input you are supplying and the expected results. Bonus points if you add a test case for it :)

This package is not yet available through packagist until things are tested/validated a bit more, but will be eventually.

## License

MIT License. See LICENSE.MD
