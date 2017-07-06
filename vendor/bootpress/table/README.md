# use BootPress\Table\Component as Table;

[![Packagist][badge-version]][link-packagist]
[![License MIT][badge-license]](LICENSE.md)
[![HHVM Tested][badge-hhvm]][link-travis]
[![PHP 7 Supported][badge-php]][link-travis]
[![Build Status][badge-travis]][link-travis]
[![Code Climate][badge-code-climate]][link-code-climate]
[![Test Coverage][badge-coverage]][link-coverage]

Create HTML tables that are easy to visualize and see what is going on.

## Installation

Add the following to your ``composer.json`` file.

``` bash
{
    "require": {
        "bootpress/table": "^1.0"
    }
}
```

## A Simple Example

```php
use BootPress\Table\Component as Table;

$table = new Table;

$html = $table->open();
$html .= $table->row();
$html .= $table->cell('', 'One');
$html .= $table->cell('', 'Two');
$html .= $table->cell('', 'Three');
$html .= $table->close();

echo $html;
```

That will give you three cells in a row:

<table>
    <tbody>
        <tr>
            <td>One</td>
            <td>Two</td>
            <td>Three</td>
        </tr>
    </tbody>
</table>

Or in other words:

```html
<table>
    <tbody>
        <tr>
            <td>One</td>
            <td>Two</td>
            <td>Three</td>
        </tr>
    </tbody>
</table>
```

## Colspan and Rowspan

Notice that we use a syntax for attributes that keeps it compact, yet readable.  Basically, every attribute is separated by a '**|**' (single pipe), and we drop the quotes.

```php
$html = $table->open('border=1|class=special');
$html .= $table->row();
$html .= $table->cell('rowspan=2', 'Two Rows');
$html .= $table->cell('', 'One');
$html .= $table->cell('', 'Two');
$html .= $table->row();
$html .= $table->cell('colspan=2', 'Buckle my shoe');
$html .= $table->close();

echo $html;
```

<table border="1" class="special">
    <tbody>
        <tr>
            <td rowspan="2">Two Rows</td>
            <td>One</td>
            <td>Two</td>
        </tr><tr>
            <td colspan="2">Buckle my shoe</td>
        </tr>
    </tbody>
</table>

```html
<table border="1" class="special">
    <tbody>
        <tr>
            <td rowspan="2">Two Rows</td>
            <td>One</td>
            <td>Two</td>
        </tr><tr>
            <td colspan="2">Buckle my shoe</td>
        </tr>
    </tbody>
</table>
```

## Caption, Header, and Footer

It is not necessary to pass a cells content to the method.  It will still be wrapped appropriately.

```php
$html = $table->open('border=1', 'Caption');
$html .= $table->head();
$html .= $table->cell('colspan=2') . 'Header';
$html .= $table->row();
$html .= $table->cell() . 'Three';
$html .= $table->cell() . 'Four';
$html .= $table->foot();
$html .= $table->cell('colspan=2') . 'Shut the door';
$html .= $table->close();

echo $html;
```

<table border="1">
    <caption>Caption</caption>
    <thead>
        <tr>
            <th colspan="2">Header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Three</td>
            <td>Four</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"> Shut the door</td>
        </tr>
    </tfoot>
</table>

```html
<table border="1">
    <caption>Caption</caption>
    <thead>
        <tr>
            <th colspan="2">Header</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Three</td>
            <td>Four</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"> Shut the door</td>
        </tr>
    </tfoot>
</table>
```

## Nested Table

When you ``$table->close()``, everything is reset and you can make another ``$table->open()`` without any problems.  For nesting tables though, you'll need to create another instance of the class.

```php
$t1 = new Table;
$t2 = new Table;

$html = $t1->open();
$html .= $t1->row();
$html .= $t1->cell('', 'Five');
$html .= $t1->cell() . 'Six';
$html .= $t1->cell();
    $html .= $t2->open('border=1');
    $html .= $t2->row();
    $html .= $t2->cell('', 'Pick');
    $html .= $t2->cell('', 'Up');
    $html .= $t2->cell('', 'Sticks');
    $html .= $t2->close();
$html .= $t1->close();
```

<table>
    <tbody>
        <tr>
            <td>Five</td>
            <td>Six</td>
            <td>
                <table border="1">
                    <tbody>
                        <tr>
                            <td>Pick</td>
                            <td>Up</td>
                            <td>Sticks</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>

```html
<table>
    <tbody>
        <tr>
            <td>Five</td>
            <td>Six</td>
            <td>
                <table border="1">
                    <tbody>
                        <tr>
                            <td>Pick</td>
                            <td>Up</td>
                            <td>Sticks</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[badge-version]: https://img.shields.io/packagist/v/bootpress/table.svg?style=flat-square&label=Packagist
[badge-license]: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/badge/HHVM-Tested-8892bf.svg?style=flat-square
[badge-php]: https://img.shields.io/badge/PHP%207-Supported-8892bf.svg?style=flat-square
[badge-travis]: https://img.shields.io/travis/Kylob/Table/master.svg?style=flat-square
[badge-code-climate]: https://img.shields.io/codeclimate/github/Kylob/Table.svg?style=flat-square
[badge-coverage]: https://img.shields.io/codeclimate/coverage/github/Kylob/Table.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bootpress/table
[link-travis]: https://travis-ci.org/Kylob/Table
[link-code-climate]: https://codeclimate.com/github/Kylob/Table
[link-coverage]: https://codeclimate.com/github/Kylob/Table/coverage
