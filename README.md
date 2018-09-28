# Solarium compilation target for RulerZ [![Build Status](https://travis-ci.org/rulerz-php/solarium.svg?branch=master)](https://travis-ci.org/rulerz-php/solarium)

Solarium compilation target for [RulerZ](https://github.com/K-Phoen/rulerz).

Usage
-----

[solarium/solarium](https://github.com/solariumphp/solarium) is one of the
targets supported by RulerZ. It allows the engine to query an Solr server.

This cookbook will show you how to retrieve objects using solarium/solarium and
RulerZ.

Here is a summary of what you will have to do:

 * [configure solarium](#configure-solarium);
 * [configure RulerZ](#configure-rulerz);
 * [filter your target](#filter-your-target).

## Configure solarium

This subject won't be directly treated here. You can either follow the [official
documentation](https://solarium.readthedocs.io/en/stable/)
or use a bundle/module/whatever the framework you're using promotes.

## Configure RulerZ

Once solarium/solarium is installed and configured we can the RulerZ engine:

```php
$rulerz = new RulerZ(
    $compiler, [
        new \RulerZ\Solarium\Target\Solarium(), // this line is Solarium-specific
        // other compilation targets...
    ]
);
```

The only Solarium-related configuration is the `Solarium` target being added to
the list of the known compilation targets.

## Filter your target

Now that both solarium/solarium and RulerZ are ready, you can use them to retrieve
data.

The `Solarium` instance that we previously injected into the RulerZ engine only
knows how to use `Solarium\Client` objects, so the first step is to create one:

```php
$config = [
    'endpoint' => [
        'localhost' => [
            'host' => '127.0.0.1',
            'port' => 8983,
            'path' => '/solr/',
        ]
    ]
];
$client = new \Solarium\Client($config);
```

And as usual, we call RulerZ with our target (the `\Solarium\Client` object) and
our rule.
RulerZ will build the right executor for the given target and use it to filter
the data, or in our case to retrieve data from Solr.

```php
$rule  = 'gender = :gender and points > :points';
$parameters = [
    'points' => 30,
    'gender' => 'M',
];

var_dump(
    iterator_to_array($rulerz->filter($client, $rule, $parameters))
);
```

License
-------

This library is under the [MIT](LICENSE) license.
