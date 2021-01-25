# HelloBootstrapTable

**This Bundle provides *simple* [bootstrap-table](https://github.com/wenzhixin/bootstrap-table) configuration for your Doctrine Entities.** 

Highly inspired by [SgDatatablesBundle](https://github.com/stwe/DatatablesBundle).

**The project is currently still under development. It can not be excluded that configuration changes.**



## When should I not use HelloBootstrapTable

HelloBootstrapTable is designed for simple tables that are strongly bound to the entities. If you are creating highly customized tables with many formatters and a lot of client-side programming, HelloBootstrapTable is not suitable for that. However, you can of course use HelloBootstrapTable alongside your complex tables.



## Overview

1. [Features](#features)
2. [Installation](#installation)
3. [Your First Table](#your-first-table)
4. [Columns](#columns)
5. [Table Props Configuration](#table-props)
6. [Persistence Options Configuration](#persistence-options)


## Features

* Table Configuration in PHP
* Filtering*
* Sorting*
* Pagination*
* Column Types: [TextColumn](#textcolumn), [BooleanColumn](#booleancolumn), [DateTimeColumn](#datetimecolumn), [ActionColumn](#actioncolumn), [HiddenColumn](#hiddencolumn)

*server-side

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download this bundle:

``` bash
$ composer require hello-sebastian/hello-bootstrap-table-bundle
```



### Step 2: Enable the Bundle (without flex)

Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

``` php
// config/bundles.php

return [
    // ...
    HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTableBundle::class => ['all' => true],
];
```


### Step 3: Assetic Configuration

#### Install the web assets

``` bash
# if possible, make absolute symlinks (best practice) in public/ if not, make a hard copy

$ php bin/console assets:install --symlink
```

``` bash
# make a hard copy of assets in public/

$ php bin/console assets:install
```

#### Add Assets into your base.html.twig

**CSS**:

``` html
<!-- Bootstap and FontAwesome CSS dependency -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

<!-- bootstrap-table CSS with all used extensions -->
<link rel="stylesheet" href="{{ asset('bundles/hellobootstraptable/bootstrap-table.css') }}">
```

**JavaScript**:

```html
<!-- jQuery and Bootstrap JS dependency -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- bootstrap-table JS with all used extensions -->
<script src="{{ asset('bundles/hellobootstraptable/bootstrap-table.js') }}"></script>
```

You can also use other CSS frameworks. See the bootstrap-table documentation for more information.



## Your First Table

### Step 1: Create a Table class


``` php
// src/HelloTable/UserTable.php

<?php

namespace App\HelloTable;

use App\Entity\User; // your entity class ...
use HelloSebastian\HelloBootstrapTableBundle\Columns\ColumnBuilder;
use HelloSebastian\HelloBootstrapTableBundle\Columns\TextColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\DateTimeColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\HiddenColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\ActionColumn;
use HelloSebastian\HelloBootstrapTableBundle\Columns\BooleanColumn;
use HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTable;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTable extends HelloBootstrapTable
{
  
    protected function buildColumns(ColumnBuilder $builder, $options)
    {
        $builder
            ->add("id", HiddenColumn::class)
            ->add('username', TextColumn::class)
            ->add('email', TextColumn::class, array(
                'title' => 'E-Mail',
                'visible' => false
            ))
            ->add('firstName', TextColumn::class, array(
                'title' => 'First name'
            ))
            ->add('lastName', TextColumn::class, array(
                'title' => 'Last name'
            ))
            ->add('createdAt', DateTimeColumn::class, array(
                'title' => 'Created at'
            ))
            ->add('department.name', TextColumn::class, array(
                'title' => 'Department',
                'emptyData' => 'No Department',
            ))
            ->add("isActive", BooleanColumn::class, array(
                'title' => 'is active',
                'trueLabel' => 'yes',
                'falseLabel' => 'no'
            ))
            ->add('department.costCentre.name', TextColumn::class, array(
                'title' => 'Cost Centre',
                'data' => function (User $user) {
                    return "#" . $user->getDepartment()->getCostCentre()->getName();
                }
            ))
            ->add("actions", ActionColumn::class, array(
                'title' => 'Actions',
                'width' => 150,
                'buttons' => array(
                    array(
                        'displayName' => 'open',
                        'routeName' => 'show_user',
                        'classNames' => 'btn btn-sm btn-success mr-1'
                    ),
                    array(
                        'displayName' => 'edit',
                        'routeName' => 'edit_user',
                        'classNames' => 'btn btn-sm btn-warning'
                    )
                )
            ));
    }

  
    protected function getEntityClass()
    {
        return User::class;
    }

  	//optional override of functions ...
  
    public function configureTableDataset(OptionsResolver $resolver)
    {
        parent::configureTableDataset($resolver);

        $resolver->setDefaults(array(
            'locale' => 'de-DE'
        ));
    }

    public function configureTableOptions(OptionsResolver $resolver)
    {
        parent::configureTableOptions($resolver);

        $resolver->setDefaults(array(
            'bulkUrl' => $this->router->generate('bulk'),
            'bulkActions' => array(
                'edit' => 'Edit',
                'delete' => 'Delete'
            )
        ));
    }
}
```


### Step 2: In the Controller

``` php
// src/Controller/UserController.php

// ...
use HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTableFactory;
// ...

/**
 * @Route("/", name="default")
 */
public function index(Request $request, HelloBootstrapTableFactory $tableFactory) : Response
{
    $table = $tableFactory->create(UserTable::class);

    $table->handleRequest($request);
    if ($table->isCallback()) {
        return $table->getResponse();
    }

    return $this->render('index.html.twig', array(
        'table' => $table->createView()
    ));
}
```

### Step 3: Add table in Template

``` html
{% extends 'base.html.twig' %}

{% block body %}
    {{ hello_bootstrap_table_render(table) }}
{% endblock %}
```

The Twig function will render a `table` with all attributes configured.

## Columns

### TextColumn

Represents column with text.

#### Options

| Option     | Type           | Default            | Description                                                  |
| ---------- | -------------- | ------------------ | ------------------------------------------------------------ |
| title      | string / null  | null               | Set colum title. If no value is set, the specified attribute name is taken. |
| field      | string / null  | null               | Set internal field name for bootstrap-table. If no value is set, the specified attribute name is taken. |
| width      | integer / null | null               | column width in px                                           |
| formatter  | string         | "defaultFormatter" | JavaScript function name for formatter. (see [formatter](https://bootstrap-table.com/docs/api/column-options/#formatter)) |
| filterable | bool           | true               | enable / disable filtering for this column                   |
| sortable   | bool           | true               | enable / disable sortable for this column                    |
| switchable | bool           | true               | enable / disable interactive hide and show of column.        |
| visible    | bool           | true               | show / hide column                                           |
| emptyData  | string         | ""                 | default value if attribute from entity is null               |
| sort       | Closure / null | null               | custom sort query callback (see example)                     |
| filter     | Closure / null | null               | custom filter query callback (see example)                   |
| data       | Closure / null | null               | custom data callback (see example)                           |

#### Example

```php
->add('username', TextColumn::class, array(
    'title' => 'Username',
  	'emptyData' => "No Username found.",
  
    //optional overrides ...
  	'data' => function (User $user) { //entity from getEntityClass
        //you can return what ever you want ...  
        return $user->getId() . " " . $user->getUsername();
    },
  	'sortQuery' => function (QueryBuilder $qb, $direction) {
        $qb->addOrderBy('username', $direction);
    }
))
```



### BooleanColumn

Represents column with boolean values.

#### Options

All options of TextColumn.

**And**:

| Option     | Type   | Default | Description            |
| ---------- | ------ | ------- | ---------------------- |
| trueLabel  | string | "True"  | label for true values  |
| falseLabel | string | "False" | label for false values |

#### Example

```php
->add('isActive', BooleanColumn::class, array(
    'title' => 'is active',
    'trueLabel' => 'yes',
    'falseLabel' => 'no'
))
```



### DateTimeColumn

Represents column with DateType values.

#### Options

All Options of TextColumn

**And:**

| Option | Type   | Default       | Description            |
| ------ | ------ | ------------- | ---------------------- |
| format | string | "Y-m-d H:i:s" | DateTime format string |

#### Example

```php
->add('createdAt', DateTimeColumn::class, array(
    'title' => 'Created at',
    'format' => 'd.m.Y'
))
```



### HiddenColumn

Represents column that are not visible in the table. Can used for data which are required for other columns.

#### Options

All Options of TextColumn.

`filterable`, `sortable`, `visible` and `switchable` are disabled by default.

#### Example

```php
->add("id", HiddenColumn::class)
```

ID is used for bulk actions and must therefore be sent along, but should not be visible in the table.



### ActionColumn

Represents column for action buttons (show / edit / remove ...).

#### Options

All Options of TextColumn

`sortable`,  `filterable` and `switchable` are disable by default.

`formatter` is set to `defaultActionFormatter`.

**And:**

| Option  | Type  | Default | Description                              |
| ------- | ----- | ------- | ---------------------------------------- |
| buttons | array | []      | array of buttons configuration as array. |

#### Example

```php
->add(null, ActionColumn::class, array(
    'title' => 'Actions',
    'width' => 120, //optional
    'buttons' => array(
        array(
            'displayName' => 'show',
            'routeName' => 'show_user',
            'additionalClassNames' => 'btn-success'
        ),
        array(
            'displayName' => 'edit',
            'routeName' => 'edit_user',
            'additionalClassNames' => 'btn-success'
       )
  	)
))
```

#### ActionButtons

| Option               | Type   | Default     | Description                                                  |
| -------------------- | ------ | ----------- | ------------------------------------------------------------ |
| displayName          | string | ""          | label of button in table                                     |
| routeName            | string | ""          | route name                                                   |
| routeParams          | array  | array("id") | Array of property value names for the route parameters. By default is `id` set. |
| classNames           | string | ""          | CSS class names which added directly to the `a` element. Overrides default class names from YAML config. |
| additionalClassNames | string | ""          | You can set default class names in YAML config. Then you can add additional class names to the button without override the default config. |



## Configuration


### Dataset Table Options

Table Dataset are provided directly to the `bootstrap-table` as data-attributes and are a collection of setting options for the table.

#### Options

| Option                     | Type   | Default                            |
| -------------------------- | ------ | ---------------------------------- |
| pagination                 | bool   | true                               |
| search                     | bool   | true                               |
| show-columns               | bool   | true                               |
| show-footer                | bool   | true                               |
| show-refresh               | bool   | true                               |
| toolbar                    | string | "#toolbar"                         |
| page-list                  | string | "[10, 25, 50, 100, 200, 500, All]" |
| page-size                  | int    | 25                                 |
| sort-reset                 | bool   | true                               |
| pagination-V-Align         | string | "both"                             |
| undefined-text             | string | ""                                 |
| locale                     | string | "en-US"                            |
| click-to-select            | bool   | true                               |
| show-jump-to               | bool   | true                               |
| show-export                | bool   | true                               |
| export-types               | string | "['csv', 'txt'', 'excel']"         |
| export-options             | array  | see under table*                   |
| sticky-header              | bool   | true                               |
| sticky-header-offset-left  | int    | 0                                  |
| sticky-header-offset-right | int    | 0                                  |
| sticky-header-offset-y     | int    | 0                                  |

`export-options`:

```php
array(
    'fileName' => (new \DateTime('now'))->format('Y-m-d_H-i-s') . '_export',
    'ignoreColumn' => array("checkbox", "actions"),
    'csvSeparator' => ';'
)
```

#### Examples

Inside from Table class:

``` php
// src/HelloTable/UserTable.php

class UserTable extends HelloBootstrapTable
{
    ...

    protected function configureTableDataset(OptionsResolver $resolver)
    {
        parent::configureTableProps($resolver);
    
        $resolver->setDefaults(array(
            'locale' => 'de-DE'
        ));
    }
}
```


Outside from Table class:

``` php
// src/Controller/UserController.php

public function index(Request $request, HelloBootstrapTableFactory $tableFactory) : Response
{
    $table = $tableFactory->create(UserTable::class);

  	// other options will be merged.
    $table->setTableDataset(array(
        'locale' => 'de-DE'
    ));

    ...
}
```


In the `configureTableDataset` method, you can specify custom data that can be provided directly to the `bootstrap-table`.

### Table Options

All options that should not be provided directly as data-attributes of the table are managed here.

#### Options

With the Persistence Options you can set which settings (filtering, sorting, current page, ...) should be stored in the cookies. By default, all of them are activated.

| Option                     | Type   | Default           |
| -------------------------- | ------ | ----------------- |
| enableCheckbox             | bool   | true              |
| bulkUrl                    | string | ""                |
| bulkActionSelectClassNames | string | "form-control"    |
| bulkActions                | array  | [ ]               |
| bulkButtonName             | string | "Okay"            |
| bulkButtonClassNames       | string | "btn btn-primary" |


#### Examples

Inside from Table class:

``` php
// src/HelloTable/UserTable.php

class UserTable extends HelloBootstrapTable
{
    ...

    protected function configureTableOptions(OptionsResolver $resolver)
    {
        parent::configureTableOptions($resolver);

        $resolver->setDefaults(array(
            'bulkUrl' => $this->router->generate('bulk'), //router are provided by HelloBootstrapTable
          	// actions are display as select field
          	// each option: value => display name
            'bulkActions' => array(
                'edit' => 'Edit',
                'delete' => 'Delete'
            )
        ));
    }
}
```


Outside from Table class:

``` php
// src/Controller/UserController.php

public function index(Request $request, HelloBootstrapTableFactory $tableFactory) : Response
{
    $table = $tableFactory->create(UserTable::class);

    $table->setTableOptions(array(
        'enableCheckbox' => false
    ));

    ...
}
```



## ToDo's
* Documentation
* Cookie Extension
* More Examples
* Tests
  * Unit Tests
