# use BootPress\Validator\Component as Validator;

[![Packagist][badge-version]][link-packagist]
[![License MIT][badge-license]](LICENSE.md)
[![HHVM Tested][badge-hhvm]][link-travis]
[![PHP 7 Supported][badge-php]][link-travis]
[![Build Status][badge-travis]][link-travis]
[![Code Climate][badge-code-climate]][link-code-climate]
[![Test Coverage][badge-coverage]][link-coverage]

Form validation that is designed to simplify and work seamlessly with [Jörn's jQuery Validation Plugin](https://jqueryvalidation.org/).  All of the validation routines are designed to match (as closely as possible) those provided by Jörn, so that the browser and server validation routines are in sync with one another.

## Installation

Add the following to your ``composer.json`` file.

``` bash
{
    "require": {
        "bootpress/validator": "^1.0"
    }
}
```

## Example Usage

```php
use BootPress\Validator\Component as Validator;

$validator = new Validator($_POST);
```

The first thing you need to do is give us an array of values to validate against.  In this case you have given us the ``$_POST`` vars.  Now you can set the rules and filters for each field.

```php
// Require a name value.
$validator->set('name', 'required');

// Require an email, and make sure it looks like one as well.
$validator->set('email', 'required|email');

// Set multiple fields at once
$validator->set(array(
    'password' => 'required|alphaNumeric|minLength[5]|noWhiteSpace', // Using a pipe separated string.
    'confirm' => array('required', 'matches[password]'), // Using an array of rules and filters
));

// Set and create a custom required message for this one field
$validator->set('field', array('required' => 'Do this or else.')

// Change the default required error message for all fields
$validator->errors['required'] = 'Why I Oughta!';
```

Field names can be an array by adding brackets to the end ie. '**name[]**'.  They can also be multi-dimensional arrays such as '**name[first]**', or '**name[players][]**', or '**name[parent][child]**', etc.  The important thing to remember is that you must always use the exact name given here when referencing them in other methods.

Rules and filters are either '**|**' (single pipe) delimited, or you can make them an array.  Custom messages can be specified by making it an ``array($rule => $message, ...)``.  Parameters are comma-delimited, and placed within '**[]**' two brackets.  The available options are:

- '**remote[rule]**' - Set ``$validator->rules['rule'] = function($value){}`` to determine the validity of a submitted value.  The function should return a boolean true or false.
- '**default**' - A default value if the field is empty, or not even set.
- '**required**' - This field must have a value, and cannot be empty.
- '**equalTo[field]**' - Must match the same value as contained in the other form field.
- '**notEqualTo[field]**' - Must NOT match the same value as contained in the other form field.
- Numbers:
  - '**number**' - Must be a valid decimal number, positive or negative, integer or float, commas okay.  Defaults to 0.
  - '**integer**' - Must be a postive or negative integer number, no commas.  Defaults to 0.
  - '**digits**' - Must be a positive integer number, no commas.  Defaults to 0.
  - '**min[number]**' - Must be greater than or equal to [number].
  - '**max[number]**' - Must be less than or equal to [number].
  - '**range[min, max]**' - Must be greater than or equal to [min], and less than or equal to [max].
- Strings:
  - '**alphaNumeric**' - Alpha (a-z), numeric (0-9), and underscore (_) characters only.
  - '**minLength[integer]**' - String length must be greater than or equal to [integer].
  - '**maxLength[integer]**' - String length must be less than or equal to [integer].
  - '**rangeLength[minLength, maxLength]**' - String length must be greater than or equal to [minLength], and less than or equal to [maxLength].
  - '**minWords[integer]**' - Number of words must be greater than or equal to [integer].
  - '**maxWords[integer]**' - Number of words must be less than or equal to [integer].
  - '**rangeWords[minWords, maxWords]**' - Number of words must be greater than or equal to [minWords], and less than or equal to [maxWords].
  - '**pattern[regex]**' - Must match the supplied [ECMA Javascript](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp) compatible [regex].
  - '**date**' - Must be a valid looking date.  No particular format is enforced.
  - '**email**' - Must be a valid looking email.
  - '**url**' - Must be a valid looking url.
  - '**ipv4**' - Must be a valid looking ipv4 address.
  - '**ipv6**' - Must be a valid looking ipv6 address.
  - '**inList[1,2,3]**' - Must be one of a comma-separated list of acceptable values.
  - '**noWhiteSpace**' - Must contain no white space.
- Filters:
  - '**singleSpace**' - Removes any doubled-up whitespace so that you only have single spaces between words.
  - '**trueFalse**' - Returns a **1** (true) or **0** (false) integer.
  - '**yesNo**' - Returns a '**Y**' or '**N**' value.

To see if the ``$_POST`` array you gave us meets all of your requirements:

```php
if ($vars = $validator->certified()) {
    // Process $vars
} else {
    // The form was either not submitted, or there were errors.
}
```

The ``$vars`` returned are all ``trim()``ed and filtered, ready for you to process as you see fit.  From here, the best thing to do is use our [BootPress Form Component](https://packagist.org/packages/bootpress/form), but if you have any better ideas then you can determine whether or not the ``$validator->required('field')``, get the submitted ``$validator->value('field')``, check if there was a ``$validator->error('field')``, get the data-rule-... ``$validator->rules('field')`` attributes, and the data-msg-... ``$validator->messages('field')`` attributes, find the ``$validator->id('field')`` we assigned, and set the ``$validator->jquery('#form')`` javascript when creating your form and fields.

All of the above is just assuming you are using this component to validate submitted form data, but it is equally well suited to validate anything on the side as well.  The static methods we provide (and use ourselves) are:

```php
Validator::number(1.345); // true - this is a number
Validator::number('string'); // false

Validator::integer(1000); // true
Validator::integer(1.345); // false - must be a whole number

Validator::digits(1000); // true
Validator::digits(1.345); // false - no periods allowed

Validator::min(5, 3); // true - 5 is greater than 3
Validator::min(3, 5); // false - 3 is less than 5

Validator::max(5, 3); // false - 5 is greater than 3
Validator::max(3, 5); // true - 3 is less than 5

Validator::range(5, array(2, 7)); // true
Validator::range(5, array(6, 7)); // false

Validator::alphaNumeric('abc123'); // true
Validator::alphaNumeric('abc-xyz'); // false

Validator::minLength('string', 2); // true
Validator::minLength('string', 7); // false

Validator::maxLength('string', 7); // true
Validator::maxLength('string', 2); // false

Validator::rangeLength('string', array(2, 6)); // true
Validator::rangeLength('string', array(7, 15)); // false
Validator::rangeLength(array(1, 2), array(2, 4)); // true - there are between 2 and 4 elements in array(1, 2)
Validator::rangeLength(array(1, 2), array(3, 5)); // false - 2 elements is outside the range of 3 and 5

Validator::minWords('one two three', 1); // true
Validator::minWords('one two three', 5); // false

Validator::maxWords('one two three', 5); // true
Validator::maxWords('one two three', 1); // false

Validator::rangeWords('one two three', array(1, 3)); // true
Validator::rangeWords('one two three', array(0, 2)); // false

// Allows phone numbers with optional country code, optional special characters and whitespace
$phone_number = '/^([+]?\d{1,2}[-\s]?|)\d{3}[-\s]?\d{3}[-\s]?\d{4}$/';
Validator::pattern('907-555-0145', $phone_number); // true
Validator::pattern('555-0145', $phone_number); // false

Validator::date('2015-12-31'); // true
Validator::date('infinite'); // false

Validator::email('email@example.com'); // true
Validator::email('email@example..com'); // false

Validator::url('http://example.com'); // true
Validator::url('example.com'); // false

Validator::ipv4('175.16.254.1'); // true
Validator::ipv4('2001:0db8:0000:0000:0000:ff00:0042:8329'); // false

Validator::ipv6('2001:0db8:0000:0000:0000:ff00:0042:8329'); // true
Validator::ipv6('175.16.254.1'); // false

Validator::inList('2', array(1, 2, 3)); // true
Validator::inList(7, array(1, 2, 3)); // false

Validator::noWhiteSpace('whitespace'); // true
Validator::noWhitespace('white space'); // false

Validator::singleSpace('single     space'); // 'single space'
Validator::singleSpace('single space'); // 'single space'

Validator::trueFalse(101)); // 1
Validator::trueFalse('true')); // 1
Validator::trueFalse('n')); // 0
Validator::trueFalse(0)); // 0

Validator::yesNo(101)); // 'Y'
Validator::yesNo('true')); // 'Y'
Validator::yesNo('n')); // 'N'
Validator::yesNo(0)); // 'N'
```

The value you want to validate always comes first, and any parameters come second - lest you be confused.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[badge-version]: https://img.shields.io/packagist/v/bootpress/validator.svg?style=flat-square&label=Packagist
[badge-license]: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/badge/HHVM-Tested-8892bf.svg?style=flat-square
[badge-php]: https://img.shields.io/badge/PHP%207-Supported-8892bf.svg?style=flat-square
[badge-travis]: https://img.shields.io/travis/Kylob/Validator/master.svg?style=flat-square
[badge-code-climate]: https://img.shields.io/codeclimate/github/Kylob/Validator.svg?style=flat-square
[badge-coverage]: https://img.shields.io/codeclimate/coverage/github/Kylob/Validator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bootpress/validator
[link-travis]: https://travis-ci.org/Kylob/Validator
[link-code-climate]: https://codeclimate.com/github/Kylob/Validator
[link-coverage]: https://codeclimate.com/github/Kylob/Validator/coverage
