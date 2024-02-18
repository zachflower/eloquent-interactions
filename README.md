# Eloquent Interactions

Eloquent Interactions manages application-specific business logic. It's an implementation of the command pattern in PHP for Laravel, and is inspired by the [ActiveInteraction](https://github.com/AaronLasseigne/active_interaction) library in Ruby.

[![Latest Stable Version](https://poser.pugx.org/zachflower/eloquent-interactions/version)](https://packagist.org/packages/zachflower/eloquent-interactions) [![CI](https://github.com/zachflower/eloquent-interactions/actions/workflows/main.yml/badge.svg)](https://github.com/zachflower/eloquent-interactions/actions/workflows/main.yml)

---

Eloquent Interactions gives you a place to put your business logic. It also helps you write safer code by validating that your inputs conform to your expectations, and provides a platform for creating discrete, easily testable code.

## Installation

To install Eloquent Interactions, require the library via [Composer](https://getcomposer.org/):

```
composer require zachflower/eloquent-interactions
```

Eloquent Interactions is built with Laravel 7.0+ in mind, and has tests currently validating compatibility with Laravel 7, 8, 9, and 10 on PHP 7.2.5 through 8.3. If you find any issues with your specific version of Laravel or PHP, please [open an issue](https://github.com/zachflower/eloquent-interactions/issues/new) and I will do my best to address it.

## Basic Usage

To get started with Eloquent Interactions, let's first create a new Interaction. Interactions typically live in the `app/Interactions` directory, but you are free to place them anywhere that can be auto-loaded according to your `composer.json` file. All Eloquent Interactions extend the `\ZachFlower\EloquentInteractions\Interaction` abstract class.

The easiest way to create an Interaction is using the `make:interaction` [Artisan command](https://laravel.com/docs/master/artisan):

```
php artisan make:interaction ConvertMetersToMiles
```

Now, let's take a look at the base Interaction that was created by the `make:interaction` command above:

```php
<?php

namespace App\Interactions;

use ZachFlower\EloquentInteractions\Interaction;

class ConvertMetersToMiles extends Interaction
{
    /**
     * Parameter validations
     *
     * @var array
     */
    public $validations = [
        //
    ];

    /**
     * Execute the interaction
     *
     * @return void
     */
    public function execute() {
        //
    }
}
```

Once generated, every interaction will require the following two components:

1. **Input Validations**. The class `$validations` property utilizes the built-in [Laravel validator](https://laravel.com/docs/master/validation) to define and validate the expected input of a given Interaction. Alternatively, the `$validations` property can be replaced with a `validations()` method.
2. **Business Logic**. The `execute()` method takes the provided input—after it passes validation, of course—and executes any necessary business logic on it. Each input you defined will be available. If any of the inputs are invalid, `execute()` won't be run.

Given that information, let's update the generated Interaction into something usable:

```php
<?php

namespace App\Interactions;

use ZachFlower\EloquentInteractions\Interaction;

class ConvertMetersToMiles extends Interaction
{
    /**
     * Parameter validations
     *
     * @var array
     */
    public $validations = [
        'meters' => 'required|numeric|min:0',
    ];

    /**
     * Execute the interaction
     *
     * @return void
     */
    public function execute() {
        return $this->meters * 0.000621371;
    }
}
```

To execute the Interaction, you can call the static `run()` method on the class. As the Interaction's `$validations` property defines the expected inputs, a simple key-value array should be passed to `run()` with the expected input. This method will return a new instance of the `\ZachFlower\EloquentInteractions\Outcome` class. To check the success of the outcome, a boolean `$valid` property will be set on the `Outcome` object, with `TRUE` meaning the input validation passed, and `FALSE` meaning it failed. If the validation failed, all validation errors will be stored in the `$errors` property on the `Outcome` object. If validation passes, the value returned from the `execute()` method will be stored in the `$result` property on the `Outcome` object.

```php
>>> $outcome = ConvertMetersToMiles::run(['meters' => 100]);
>>> $outcome->valid;
=> true
>>> $outcome->result;
=> 0.0621371

>>> $outcome = ConvertMetersToMiles::run(['meters' => 'one hundred']);
>>> $outcome->valid;
=> false
>>> $outcome->errors->toArray()
=> [
     "meters" => [
       "The meters field must be a number.",
     ],
   ]
```

If you would rather deal with error handling on your own, you can pass `TRUE` as a second parameter to the `run()` method. This, for lack of a better word, will execute the Interaction "dangerously," meaning that any defined errors will be thrown as exceptions of the type `\ZachFlower\EloquentInteractions\Exceptions\ValidationException` instead.

```php
>>> $outcome = App\Interactions\Utility\ConvertMetersToMiles::run(['meters' => 'one hundred'], TRUE);
Illuminate\Validation\ValidationException with message 'The given data failed to pass validation.'
>>> $outcome->errors->toArray();
=> [
     "meters" => [
       "The meters field must be a number.",
     ],
   ]
```

### Validations

Eloquent Interactions relies heavily on the build-in [Laravel validator](https://laravel.com/docs/master/validation). This means that any validation method available within a Laravel application will also be available to the Eloquent Interactions validator. That said, there is currently one custom validator (with more on the horizon) to better facilitate the backend-nature of Eloquent Interactions.

### Advanced Validators

If the built-in validators aren't powerful enough for your needs, you can use a `validations()` method in lieu of the `$validations` property. For example, let's say that we want to use a class to validate the `meters` parameter in the above examples. Our interaction would change to look something like this:


```php
<?php

namespace App\Interactions;

use ZachFlower\EloquentInteractions\Interaction;

class ConvertMetersToMiles extends Interaction
{
    /**
     * Execute the interaction
     *
     * @return void
     */
    public function execute() {
        return $this->meters * 0.000621371;
    }

    /**
     * Parameter validations
     *
     * @var array
     */
    public function validations()
    {
        return [
            'meters' => ['required', new MyMetersRule()],
        ];
    }
}
```

#### Objects

In some instances, it might be desireable to validate the **type** of an object. For example, if we wanted to validate that an input parameter is `User` model, the following validation rule may be used:

```php
public $validations = [
    'user' => 'required|object:App\Models\User'
];
```

In a nutshell, this validator checks the `instanceof` of an input parameter against the defined validation. This is especially useful when validating whether or not a provided object is a child of the defined validation object.

### Errors

In addition to the built-in validation errors, Eloquent Interactions also has support for custom validation errors directly within the `execute()` method. This can be accomplished by utilizing the Laravel validator's own `add()` method directly on its `errors()` method:

```php
public function execute() {
    $this->validator->errors()->add('entity', 'The entity object type is invalid.');
}
```

It is important to note that, while adding custom validation errors within the `execute()` method _will_ mark the `Outcome` as invalid and return the expected error messages, what it _won't_ do is halt Interaction execution, so any business logic in the `execute()` method will be executed as normal unless special steps are taken.

## Contributing

Please read through the [contributing guidelines](https://github.com/zachflower/eloquent-interactions/blob/master/CONTRIBUTING.md). Included are directions for opening issues, coding standards, and notes on development.

For personal support requests, please use [Gitter](https://gitter.im/eloquent-interactions/Lobby) to get help.

## Versioning

For transparency into the release cycle and in striving to maintain backward compatibility, Eloquent Interactions is maintained under [the Semantic Versioning guidelines](http://semver.org/). Sometimes I screw up, but I'll adhere to those rules whenever possible.

See [the Releases section of the GitHub project](https://github.com/zachflower/eloquent-interactions/releases) for changelogs for each release version of Eloquent Interactions.

## Support

The [issue tracker](https://github.com/zachflower/eloquent-interactions/issues) is the preferred channel for bug reports, feature requests and submitting pull requests.

## Copyright and License

Code and documentation copyright 2024 Zachary Flower. Code released under the [MIT license](https://github.com/zachflower/eloquent-interactions/blob/master/LICENSE.md).
