# use BootPress\Bootstrap\v3\Component as Bootstrap;

[![Packagist][badge-version]][link-packagist]
[![License MIT][badge-license]](LICENSE.md)
[![HHVM Tested][badge-hhvm]][link-travis]
[![PHP 7 Supported][badge-php]][link-travis]
[![Build Status][badge-travis]][link-travis]
[![Code Climate][badge-code-climate]][link-code-climate]
[![Test Coverage][badge-coverage]][link-coverage]

The Bootstrap Component allows you to easily generate Bootstrap tables, navs, pagination, buttons, dropdowns, accordions, carousels ... you name it - all without touching a single div! Of course, if you like spelling it all out then there is really no need for this class. It is simply here to help make your code more readable, easy to update, and less buggy.

## Installation

Add the following to your ``composer.json`` file.

``` bash
{
    "require": {
        "bootpress/bootstrap": "^1.0"
    }
}
```

## Example Usage

```php
<?php

use BootPress\Bootstrap\v3\Component as Bootstrap;

$bp = new Bootstrap;

// Use in your BootPress Blog's Twig templates as well:
$blog = new \BootPress\Blog\Component();
$blog->theme->vars['bp'] = $bp;
```

One line of code is worth a thousand words, so instead of describing everything, it should be pretty self-explanatory what is going on.  The PHP and Twig examples will output the HTML.

## CSS

### [Grid system](http://getbootstrap.com/css/#grid)

PHP
```php
echo $bp->row('sm', array(
    $bp->col(3, 'left'),
    $bp->col(6, 'center'),
    $bp->col(3, 'right'),
));

echo $bp->row('sm', 'md', 'lg', array(
    $bp->col(12, '9 push-3', '10 push-2', 'content'),
    $bp->col('6 offset-3 clearfix', '3 pull-9', '2 pull-10', 'sidebar'),
));
```

Twig
```twig
{{ bp.row('sm', [
    bp.col(3, 'left'),
    bp.col(6, 'center'),
    bp.col(3, 'right'),
]) }}

{{  bp.row('sm', 'md', 'lg', [
    bp.col(12, '9 push-3', '10 push-2', 'content'),
    bp.col('6 offset-3 clearfix', '3 pull-9', '2 pull-10', 'sidebar'),
]) }}
```

HTML
```html
<div class="row">
    <div class="col-sm-3">left</div>
    <div class="col-sm-6">center</div>
    <div class="col-sm-3">right</div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-9 col-md-push-3 col-lg-10 col-lg-push-2">content</div>
    <div class="col-sm-6 col-sm-offset-3 clearfix col-md-3 col-md-pull-9 col-lg-2 col-lg-pull-10">sidebar</div>
</div>
```

### [Lists](http://getbootstrap.com/css/#type-lists)

PHP
```php
echo $bp->lister('ol', array(
    'Coffee',
    'Tea' => array(
        'Black tea',
        'Green tea',
    ),
    'Milk',
));

echo $bp->lister('ul list-inline', array(
    'Coffee',
    'Tea',
    'Milk',
));

echo $bp->lister('dl dl-horizontal', array(
    'Coffee' => array(
        'Black hot drink',
        'Caffeinated beverage',
    ),
    'Milk' => 'White cold drink',
));
```

Twig
```twig
{{ bp.lister('ol', [
    'Coffee',
    'Tea': [
        'Black tea',
        'Green tea',
    ],
    'Milk',
]) }}

{{ bp.lister('ul list-inline', [
    'Coffee',
    'Tea',
    'Milk',
]) }}

{{ bp.lister('dl dl-horizontal', [
    'Coffee': [
        'Black hot drink',
        'Caffeinated beverage',
    ],
    'Milk': 'White cold drink',
]) }}
```

HTML
```html
<ol>
    <li>Coffee</li>
    <li>Tea
        <ol>
            <li>Black tea</li>
            <li>Green tea</li>
        </ol>
    </li>
    <li>Milk</li>
</ol>

<ul class="list-inline">
    <li>Coffee</li>
    <li>Tea</li>
    <li>Milk</li>
</ul>

<dl class="dl-horizontal">
    <dt>Coffee</dt>
        <dd>Black hot drink</dd>
        <dd>Caffeinated beverage</dd>
    <dt>Milk</dt>
        <dd>White cold drink</dd>
</dl>

```
     
### [Tables](http://getbootstrap.com/css/#tables)

PHP
```php
echo $bp->table->open('class=responsive striped');
    echo $bp->table->head();
    echo $bp->table->cell('', 'One');
    echo $bp->table->row();
    echo $bp->table->cell('', 'Two');
    echo $bp->table->foot();
    echo $bp->table->cell('', 'Three');
echo $bp->table->close();
```

Twig
```twig
{{ bp.table.open('class=responsive striped') }}
    {{ bp.table.head() }}
    {{ bp.table.cell('', 'One') }}
    {{ bp.table.row() }}
    {{ bp.table.cell('', 'Two') }}
    {{ bp.table.foot() }}
    {{ bp.table.cell('', 'Three') }}
{{ bp.table.close() }}
```

HTML
```html
<table class="table table-responsive table-striped">
    <thead>
        <tr>
            <th>One</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Two</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>Three</td>
        </tr>
    </tfoot>
</table>
```

### [Forms](http://getbootstrap.com/css/#forms)

PHP
```php
$form = $bp->form('sign_in');

$form->menu('remember', array('Y' => 'Remember me'));

$form->validator->set(array(
    'email' => 'required|email',
    'password' => 'required|minLength[5]|noWhiteSpace',
    'remember' => 'yesNo',
));

if ($vars = $form->validator->certified()) {
    $form->message('info', 'Good job, you are doing great!');
    $form->eject();
}

$form->size('lg'); // oversize the inputs
$form->align('collapse'); // default is horizontal

echo $form->header();
echo $form->fieldset('Sign In', array(
    $form->field('Email address', $form->group($bp->icon('user'), '', $form->text('email'))),
    $form->field('Password', $form->group($bp->icon('lock'), '', $form->password('password'))),
    $form->field('', $form->checkbox('remember'),
    $form->submit(),
));
echo $form->close();
```

Twig
```twig
{% set form = bp.form('sign_in') %}

{{ form.menu('remember', ['Y':'Remember me']) }}

{{ form.validator.set([
    'email': 'required|email',
    'password': 'required|minLength[5]|noWhiteSpace',
    'remember': 'yesNo',
]) }}

{% set vars = form.validator.certified() %}
{% if vars %}
    {{ form.message('info', 'Good job, you are doing great!') }}
    {{ form.eject() }}
{% endif %}

{{ form.size('lg') }}
{{ form.align('collapse') }}

{{ form.header() }}
{{ form.fieldset('Sign In', [
    form.field('Email address', form.group(bp.icon('user'), '', form.text('email'))),
    form.field('Password', form.group(bp.icon('lock'), '', form.password('password'))),
    form.field('', form.checkbox('remember')),
    form.submit(),
]) }}
{{ form.close() }}
```

HTML
```html
<form name="sign_in" method="post" action="http://example.com?submitted=sign_in" accept-charset="utf-8" autocomplete="off">
    <fieldset><legend>Sign In</legend>
        <div class="form-group">
            <label class="input-lg" for="emailI">Email address</label>
            <p class="validation help-block" style="display:none;"></p>
            <div class="input-group input-group-lg">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </div>
                <input type="text" name="email" id="emailI" data-rule-required="true" data-rule-email="true" class="form-control input-lg">
            </div>
        </div>
        <div class="form-group">
            <label class="input-lg" for="passwordII">Password</label>
            <p class="validation help-block" style="display:none;"></p>
            <div class="input-group input-group-lg">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-lock"></span>
                </div>
                <input type="password" name="password" id="passwordII" data-rule-required="true" data-rule-minlength="5" data-rule-nowhitespace="true" class="form-control input-lg">
            </div>
        </div>
        <div class="form-group">
            <p class="validation help-block" style="display:none;"></p>
            <div class="checkbox input-lg">
                <label><input type="checkbox" name="remember" value="Y"> Remember me</label>
            </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg" data-loading-text="Submitting...">Submit</button>
        </div>
    </fieldset>
</form>
```

### [Buttons](http://getbootstrap.com/css/#buttons)

PHP
```php
echo $bp->button('primary', 'Primary');

echo $bp->button('lg success', 'Link', array('href'=>'#'));

echo $bp->button('link', 'Button');
```

Twig
```twig
{{ bp.button('primary', 'Primary') }}

{{ bp.button('lg success', 'Link', ['href':'#']) }}

{{ bp.button('link', 'Button') }}
```

HTML
```html
<button type="button" class="btn btn-primary">Primary</button>

<a href="#" class="btn btn-lg btn-success">Link</a>

<button type="button" class="btn btn-link">Button</button>
```
     
## Components

### [Button dropdowns](http://getbootstrap.com/components/#btn-dropdowns)

PHP
```php
echo $bp->button('default', 'Dropdown', array(
    'dropdown' => array(
        'Header',
        'Action' => '#',
        'Another action' => '#',
        'Active link' => '#', 
        '',
        'Separated link' => '#',
        'Disabled link' => '#',
    ),
    'active' => 'Active link',
    'disabled' => 'Disabled link',
));
```

Twig
```twig
{{ bp.button('default', 'Dropdown', [
    'dropdown': [
        'Header',
        'Action': '#',
        'Another action': '#',
        'Active link': '#',
        '',
        'Separated link': '#',
        'Disabled link': '#',
    ],
    'active': 'Active link',
    'disabled': 'Disabled link',
]) }}
```

HTML
```html
<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" id="dropdownI" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></button>
    <ul class="dropdown-menu" aria-labelledby="dropdownI">
        <li role="presentation" class="dropdown-header">Header</li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Another action</a></li>
        <li role="presentation" class="active"><a role="menuitem" tabindex="-1" href="#">Active link</a></li>
        <li role="presentation" class="divider"></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
        <li role="presentation" class="disabled"><a role="menuitem" tabindex="-1" href="#">Disabled link</a></li>
    </ul>
</div>
```

### [Button groups](http://getbootstrap.com/components/#btn-groups)

PHP
```php
echo $bp->group('', array(
    $bp->button('default', 'Left'),
    $bp->button('default', 'Middle'),
    $bp->button('default', array('split'=>'Right'), array(
        'dropdown' => array(
            'Works' => '#',
            'Here' => '#',
            'Too' => '#',
        ),
        'pull'=>'right',
    )),
));
```

Twig
```twig
{{ bp.group('', [
    bp.button('default', 'Left'),
    bp.button('default', 'Middle'),
    bp.button('default', ['split':'Right'], [
        'dropdown': [
            'Works': '#',
            'Here': '#',
            'Too': '#',
        ],
        'pull': 'right',
    ]),
]) }}
```

HTML
```html
<div class="btn-group" role="group">
    <button type="button" class="btn btn-default">Left</button>
    <button type="button" class="btn btn-default">Middle</button>
    <div class="btn-group">
        <button type="button" class="btn btn-default">Right</button>
        <button type="button" class="btn btn-default dropdown-toggle" id="dropdownI" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span> <span class="sr-only">Toggle Dropdown</span></button>
        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownI">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Works</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Here</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Too</a></li>
        </ul>
    </div>
</div>
```

### [Tabs](http://getbootstrap.com/components/#nav-tabs)

PHP
```php
echo $bp->tabs(array(
    'Nav' => '#',
    'Tabs' => '#',
    'Justified' => '#',
), array(
    'align' => 'justified',
    'active' => 1,
));
```

Twig
```twig
{{ bp.tabs([
    'Nav': '#',
    'Tabs': '#',
    'Justified': '#',
], [
    'align': 'justified',
    'active': 1,
]) }}
```

HTML
```html
<ul class="nav nav-tabs nav-justified">
    <li role="presentation" class="active"><a href="#">Nav</a></li>
    <li role="presentation"><a href="#">Tabs</a></li>
    <li role="presentation"><a href="#">Justified</a></li>
</ul>
```

### [Pills](http://getbootstrap.com/components/#nav-pills)

PHP
```php
echo $bp->pills(array(
    'Home ' . $bp->badge(42) => '#',
    'Profile' . $bp->badge(0) => '#',
    'Messages' . $bp->badge(3) => array(
        'New! ' . $bp->badge(1) => '#',
        'Read ' => '#',
        'Trashed ' => '#',
        '',
        'Spam ' . $bp->badge(2) => '#',
    ),
), array(
    'active' => 'Home',
));
```

Twig
```twig
{{ bp.pills([
    'Home ' ~ bp.badge(42): '#',
    'Profile ' ~ bp.badge(0): '#',
    'Messages ' ~ bp.badge(3): [
        'New! ' ~ bp.badge(1): '#',
        'Read ': '#',
        'Trashed ': '#',
        '',
        'Spam ' ~ bp.badge(2): '#',
    ],
], [
    'active': 'Home',
]) }}
```

HTML
```html
<ul class="nav nav-pills">
    <li role="presentation" class="active"><a href="#">Home <span class="badge">42</span></a></li>
    <li role="presentation"><a href="#">Profile <span class="badge"></span></a></li>
    <li class="dropdown"><a id="dropdownI" data-target="#" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Messages <span class="badge">3</span> <span class="caret"></span></a> 
        <ul class="dropdown-menu" aria-labelledby="dropdownI">
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">New! <span class="badge">1</span></a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Read </a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Trashed </a></li>
            <li role="presentation" class="divider"></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Spam <span class="badge">2</span></a></li>
        </ul>
    </li>
</ul>
```

### [Navbar](http://getbootstrap.com/components/#navbar)

PHP
```php
echo $bp->navbar->open(array('Website' => 'http://example.com'));
    
    echo $bp->navbar->menu(array(
        'Home' => '#',
        'Work' => '#',
        'Dropdown' => array(
            'Action' => '#',
            'More' => '#',
        ),
    ), array(
        'active' => 'Home',
    ));

    echo $bp->navbar->button('primary', 'Sign In', array(
        'pull' => 'right',
    ));

    echo $bp->navbar->search('http://example.com', array(
        'button' => false,
    ));
    
echo $bp->navbar->close();
```

Twig
```twig
{{ bp.navbar.open(['Website':'http://example.com']) }}

    {{ bp.navbar.menu([
        'Home': '#',
        'Work': '#',
        'Dropdown': [
            'Action': '#',
            'More': '#',
        ],
    ], [
        'active': 'Home',
    ]) }}

    {{ bp.navbar.button('primary', 'Sign In', [
        'pull': 'right',
    ]) }}

    {{ bp.navbar.search('http://example.com', [
        'button': false,
    ]) }}

{{ bp.navbar.close() }}
```

HTML
```html
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbarI">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            
            <a class="navbar-brand" href="http://example.com">Website</a>
            
        </div>
        <div class="collapse navbar-collapse" id="navbarI">
        
            <ul class="nav navbar-nav">
                <li role="presentation" class="active"><a href="#">Home</a></li>
                <li role="presentation"><a href="#">Work</a></li>
                <li class="dropdown">
                    <a id="dropdownII" data-target="#" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownII">
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">More</a></li>
                    </ul>
                </li>
            </ul>
            
            <button type="button" class="btn btn-primary navbar-btn navbar-right">Sign In</button>
            
            <form name="search" method="get" action="http://example.com" accept-charset="utf-8" autocomplete="off" role="search" class="navbar-form navbar-right">
                <input type="text" class="form-control" placeholder="Search" name="search" id="searchIII" data-rule-required="true">
            </form>
            
        </div>
    </div>
</nav>
```

### [Breadcrumbs](http://getbootstrap.com/components/#breadcrumbs)

PHP
```php
$bp->breadcrumbs(array(
    'Home' => '#',
    'Library' => '#',
    'Data' => '#',
));
```

Twig
```twig
{{ bp.breadcrumbs([
    'Home': '#',
    'Library': '#',
    'Data': '#',
]) }}
```

HTML
```html
<ul class="breadcrumb">
    <li><a href="#">Home</a></li>
    <li><a href="#">Library</a></li>
    <li class="active">Data</li>
</ul>
```

### [Pagination](http://getbootstrap.com/components/#pagination)

PHP
```php
$records = range(1, 100);

if (!$bp->pagination->set('page', 10, 'http://example.com')) {
    $bp->pagination->total(count($records));
}

echo $pagination->links();

echo $pagination->pager();
```

Twig
```twig
{% set records = range(1, 100) %}

{% if not bp.pagination.set('page', '10', 'http://example.com') %}
    {{ bp.pagination.total(records|length) }}
{% endif %}

{{ bp.pagination.links() }}

{{ bp.pagination.pager() }}
```

HTML
```html
<ul class="pagination">
	<li class="active"><span>1</span></li>
	<li><a href="http://example.com?page=2of10">2</a></li>
	<li><a href="http://example.com?page=3of10">3</a></li>
	<li><a href="http://example.com?page=4of10">4</a></li>
	<li><a href="http://example.com?page=5of10">5</a></li>
	<li><a href="http://example.com?page=6of10">6</a></li>
	<li><a href="http://example.com?page=7of10">7</a></li>
	<li class="disabled"><span>&hellip;</span></li>
	<li><a href="http://example.com?page=10of10">10</a></li>
	<li><a href="http://example.com?page=2of10">&raquo;</a></li>
</ul>

<ul class="pager">
    <li class="next"><a href="http://example.com?page=2of10">Next &raquo;</a></li>
</ul>
```

### [Labels](http://getbootstrap.com/components/#labels)

PHP
```php
echo $bp->label('default', 'New');
```

Twig
```twig
{{ bp.label('default', 'New') }}
```

HTML
```html
<span class="label label-default">New</span>
```

### [Badges](http://getbootstrap.com/components/#badges)

PHP
```php
echo $bp->badge(13, 'right');
```

Twig
```twig
{{ bp.badge(13, 'right') }}
```

HTML
```html
<span class="badge pull-right">13</span>
```

### [Alerts](http://getbootstrap.com/components/#alerts)

PHP
```php
echo $bp->alert('info', '<h3>Heads up!</h3> This alert needs your attention, but it\'s not <a href="#">super important</a>.');

echo $bp->alert('danger', '<h3>Oh snap!</h3> Change a few things up and <a href="#">try submitting again</a>.', false);
```

Twig
```twig
{{ bp.alert('info', '<h3>Heads up!</h3> This alert needs your attention, but it\'s not <a href="#">super important</a>.') }}

{{ bp.alert('danger', '<h3>Oh snap!</h3> Change a few things up and <a href="#">try submitting again</a>.', false) }}
```

HTML
```html
<div class="alert alert-info alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    <h3 class="alert-heading">Heads up!</h3> This alert needs your attention, but it's not <a href="#" class="alert-link">super important</a>.
</div>

<div class="alert alert-danger" role="alert">
    <h3 class="alert-heading">Oh snap!</h3> Change a few things up and <a href="#" class="alert-link">try submitting again</a>.
</div>
```

### [Progress bars](http://getbootstrap.com/components/#progress)

PHP
```php
echo $bp->progress(60, 'info', 'display');

echo $bp->progress(array(25, 25, 25, 25), array('', 'warning', 'success', 'danger striped'));
```

Twig
```twig
{{ bp.progress(60, 'info', 'display') }}

{{ bp.progress([25, 25, 25, 25], ['', 'warning', 'success', 'danger striped']) }}
```

HTML
```html
<div class="progress">
    <div class="progress-bar progress-bar-info" style="width:60%;" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">60%</div>
</div>

<div class="progress">
    <div class="progress-bar" style="width:25%;" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <span class="sr-only">25% Complete</span>
    </div>
    <div class="progress-bar progress-bar-warning" style="width:25%;" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <span class="sr-only">25% Complete</span>
    </div>
    <div class="progress-bar progress-bar-success" style="width:25%;" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <span class="sr-only">25% Complete</span>
    </div>
    <div class="progress-bar progress-bar-danger progress-bar-striped" style="width:25%;" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
        <span class="sr-only">25% Complete</span>
    </div>
</div>
```

### [Media object](http://getbootstrap.com/components/#media)

PHP
```php
echo $bp->media(array(
    'Image',
    '<h1>Parent</h1> <p>Paragraph</p>',
    '<img src="parent.jpg" alt="Family Photo">',
    array(
        'Image',
        '<h2>1st Child</h2>',
        array(
            'Image',
            '<h3>1st Grandchild</h3>',
        ),
    ),
    array(
        'Image',
        '<h2>2nd Child</h2>',
    ),
), array(
    'class' => 'spoiled',
    'Image',
    '<h1>Sibling</h1> <a href="#">Link</a>',
    '<img src="sibling.jpg" alt="Family Photo">',
));
```

Twig
```twig
{{ bp.media([
    'Image',
    '<h1>Parent</h1> <p>Paragraph</p>',
    '<img src="parent.jpg" alt="Family Photo">',
    [
        'Image',
        '<h2>1st Child</h2>',
        [
            'Image',
            '<h3>1st Grandchild</h3>',
        ],
    ],
    [
        'Image',
        '<h2>2nd Child</h2>',
    ],
], [
    'class': 'spoiled',
    'Image',
    '<h1>Sibling</h1> <a href="#">Link</a>',
    '<img src="sibling.jpg" alt="Family Photo">',
]) }}
```

HTML
```html
<div class="media">
    <div class="media-left">Image</div>
    <div class="media-body">
        <h1 class="media-heading">Parent</h1>
        <p>Paragraph</p>
        <div class="media">
            <div class="media-left">Image</div>
            <div class="media-body">
                <h2 class="media-heading">1st Child</h2>
                <div class="media">
                    <div class="media-left">Image</div>
                    <div class="media-body">
                        <h3 class="media-heading">1st Grandchild</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="media">
            <div class="media-left">Image</div>
            <div class="media-body">
                <h2 class="media-heading">2nd Child</h2>
            </div>
        </div>
    </div>
    <div class="media-right">
        <img src="parent.jpg" alt="Family Photo" class="media-object">
    </div>
</div>
<div class="media spoiled">
    <div class="media-left">Image</div>
    <div class="media-body">
        <h1 class="media-heading">Sibling</h1>
        <a href="#">Link</a>
    </div>
    <div class="media-right">
        <img src="sibling.jpg" alt="Family Photo" class="media-object">
    </div>
</div>
```

### [List group](http://getbootstrap.com/components/#list-group)

PHP
```php
$bp->listGroup(array(
    'Basic',
    'List',
    $bp->badge(1) . ' Group',
));

$bp->listGroup(array(
    'Linked' => '#',
    'List' => '#',
    'Group ' . $bp->badge(2) => '#',
), 'Linked');

$bp->listGroup(array(
    '<h4>Custom</h4> <p>List</p>' => '#',
    $bp->badge(3) . ' <h4>Group</h4> <p>Linked</p>' => '#',
), 1);
```

Twig
```twig
{{ bp.listGroup([
    'Basic',
    'List',
    bp.badge(1) ~ ' Group',
]) }}

{{ bp.listGroup([
    'Linked': '#',
    'List': '#',
    'Group ' ~ bp.badge(2): '#',
]) }}

{{ bp.listGroup([
    '<h4>Custom</h4> <p>List</p>': '#',
    bp.badge(3) ~ ' <h4>Group</h4> <p>Linked</p>': '#',
], 1) }}
```

HTML
```html
<ul class="list-group">
    <li class="list-group-item">Basic</li>
    <li class="list-group-item">List</li>
    <li class="list-group-item"><span class="badge">1</span> Group</li>
</ul>

<div class="list-group">
    <a class="list-group-item" href="#">Linked</a>
    <a class="list-group-item" href="#">List</a>
    <a class="list-group-item" href="#">Group <span class="badge">2</span></a>
</div>

<div class="list-group">
    <a class="list-group-item active" href="#">
        <h4 class="list-group-item-heading">Custom</h4>
        <p class="list-group-item-text">List</p>
    </a>
    <a class="list-group-item" href="#">
        <span class="badge">3</span>
        <h4 class="list-group-item-heading">Group</h4>
        <p class="list-group-item-text">Linked</p>
    </a>
</div>
```

### [Panels](http://getbootstrap.com/components/#panels)

PHP
```php
echo $bp->panel('primary', array(
    'header' => '<h3>Title</h3>',
    'body' => 'Content',
    'footer' => '<a href="#">Link</a>',
));

echo $bp->panel('default', array(
    'header': 'List group',
    $bp->listGroup(array(
        'One',
        'Two',
        'Three',
    )),
));
```

Twig
```twig
{{ bp.panel('primary', [
    'header': '<h3>Title</h3>',
    'body': 'Content',
    'footer': '<a href="#">Link</a>',
]) }}

{{ bp.panel('default', [
    'header': 'List group',
    bp.listGroup([
        'One',
        'Two',
        'Three',
    ]),
]) }}
```

HTML
```html
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Title</h3>
    </div>
    <div class="panel-body">Content</div>
    <div class="panel-footer">
        <a href="#">Link</a>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">List group</div>
    <ul class="list-group">
        <li class="list-group-item">One</li>
        <li class="list-group-item">Two</li>
        <li class="list-group-item">Three</li>
    </ul>
</div>
```

## Javascript

### [Togglable tabs](http://getbootstrap.com/javascript/#tabs)

PHP
```php
echo $bp->toggle('tabs', array(
    'Home' => 'One',
    'Profile' => 'Two',
    'Dropdown' => array(
        'This' => 'Three',
        'That' => 'Four',
    ),
), array(
    'active' => 'This',
    'fade',
));
```

Twig
```twig
{{ bp.toggle('tabs', [
    'Home': 'One',
    'Profile': 'Two',
    'Dropdown': [
        'This': 'Three',
        'That': 'Four',
    ],
], [
    'active': 'This',
    'fade',
]) }}
```

HTML
```html
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#tabsI" aria-controls="tabsI" role="tab" data-toggle="tab">Home</a></li>
    <li role="presentation"><a href="#tabsII" aria-controls="tabsII" role="tab" data-toggle="tab">Profile</a></li>
    <li class="dropdown active">
        <a id="dropdownV" data-target="#" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Dropdown <span class="caret"></span></a>
        <ul class="dropdown-menu" aria-labelledby="dropdownV">
            <li role="presentation" class="active"><a role="menuitem" tabindex="-1" href="#tabsIII" data-toggle="tab">This</a></li>
            <li role="presentation"><a role="menuitem" tabindex="-1" href="#tabsIV" data-toggle="tab">That</a></li>
        </ul>
    </li>
</ul>
<div class="tab-content">
    <div role="tabpanel" class="tab-pane fade" id="tabsI">One</div>
    <div role="tabpanel" class="tab-pane fade" id="tabsII">Two</div>
    <div role="tabpanel" class="tab-pane fade in active" id="tabsIII">Three</div>
    <div role="tabpanel" class="tab-pane fade" id="tabsIV">Four</div>
</div>
```

### [Accordion](http://getbootstrap.com/javascript/#collapse-example-accordion)

PHP
```php
echo $bp->accordion('info', array(
    '<h4>Group Item #1</h4>' => 'One',
    '<h4>Group Item #2</h4>' => 'Two',
    '<h4>Group Item #3</h4>' => 'Three',
), 2);
```

Twig
```twig
{{ bp.accordion('info', [
    '<h4>Group Item #1</h4>': 'One',
    '<h4>Group Item #2</h4>': 'Two',
    '<h4>Group Item #3</h4>': 'Three',
], 2) }}
```

HTML
```html
<div class="panel-group" id="accordionI" role="tablist" aria-multiselectable="true">
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingII">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordionI" href="#collapseIII" aria-expanded="false" aria-controls="collapseIII">Group Item #1</a>
            </h4>
        </div>
        <div id="collapseIII" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingII">
            <div class="panel-body">One</div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingIV">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordionI" href="#collapseV" aria-expanded="true" aria-controls="collapseV">Group Item #2</a>
            </h4>
        </div>
        <div id="collapseV" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingIV">
            <div class="panel-body">Two</div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingVI">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordionI" href="#collapseVII" aria-expanded="false" aria-controls="collapseVII">Group Item #3</a>
            </h4>
        </div>
        <div id="collapseVII" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingVI">
            <div class="panel-body">Three</div>
        </div>
    </div>
</div>
```

### [Carousel](http://getbootstrap.com/javascript/#carousel)
 
PHP
```php
echo '<div style="width:500px; height:300px; margin:20px auto;">';
echo $bp->carousel(array(
    '<img src="http://lorempixel.com/500/300/food/1/" width="500" height="300">',
    '<img src="http://lorempixel.com/500/300/food/2/" width="500" height="300">' => '<p>Caption</p>',
    '<img src="http://lorempixel.com/500/300/food/3/" width="500" height="300">' => '<h3>Header</h3>',
), array(
    'interval' => 3000,
));
echo '</div>';
```

Twig
```twig
<div style="width:500px; height:300px; margin:20px auto;">
{{ bp.carousel([
    '<img src="http://lorempixel.com/500/300/food/1/" width="500" height="300">',
    '<img src="http://lorempixel.com/500/300/food/2/" width="500" height="300">': '<p>Caption</p>',
    '<img src="http://lorempixel.com/500/300/food/3/" width="500" height="300">': '<h3>Header</h3>',
], [
    'interval': 3000,
]) }}
</div>
```

HTML
```html
<div style="width:500px; height:300px; margin:20px auto;">
    <div id="carouselI" class="carousel slide" data-ride="carousel" data-interval="3000">
        <ol class="carousel-indicators">
            <li data-target="#carouselI" data-slide-to="0" class="active"></li>
            <li data-target="#carouselI" data-slide-to="1"></li>
            <li data-target="#carouselI" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner" role="listbox">
            <div class="item active">
                <img src="http://lorempixel.com/500/300/food/1/" width="500" height="300">
            </div>
            <div class="item">
                <img src="http://lorempixel.com/500/300/food/2/" width="500" height="300">
                <div class="carousel-caption">
                    <p>Caption</p>
                </div>
            </div>
            <div class="item">
                <img src="http://lorempixel.com/500/300/food/3/" width="500" height="300">
                <div class="carousel-caption">
                    <h3>Header</h3>
                </div>
            </div>
        </div>
        <a class="left carousel-control" href="#carouselI" role="button" data-slide="prev">
            <span aria-hidden="true" class="glyphicon glyphicon-chevron-left"></span> <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#carouselI" role="button" data-slide="next">
            <span aria-hidden="true" class="glyphicon glyphicon-chevron-right"></span> <span class="sr-only">Next</span>
        </a>
    </div>
</div>
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[badge-version]: https://img.shields.io/packagist/v/bootpress/bootstrap.svg?style=flat-square&label=Packagist
[badge-license]: https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/badge/HHVM-Tested-8892bf.svg?style=flat-square
[badge-php]: https://img.shields.io/badge/PHP%207-Supported-8892bf.svg?style=flat-square
[badge-travis]: https://img.shields.io/travis/Kylob/Bootstrap/master.svg?style=flat-square
[badge-code-climate]: https://img.shields.io/codeclimate/github/Kylob/Bootstrap.svg?style=flat-square
[badge-coverage]: https://img.shields.io/codeclimate/coverage/github/Kylob/Bootstrap.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bootpress/bootstrap
[link-travis]: https://travis-ci.org/Kylob/Bootstrap
[link-code-climate]: https://codeclimate.com/github/Kylob/Bootstrap
[link-coverage]: https://codeclimate.com/github/Kylob/Bootstrap/coverage
