# HelloBootstrapTableBundle

**This Bundle provides *simple* [bootstrap-table](https://github.com/wenzhixin/bootstrap-table) configuration for your Doctrine Entities.** 

Used bootstrap-table version 1.18.3.

Inspired by [SgDatatablesBundle](https://github.com/stwe/DatatablesBundle).

## Overview

1. [Features](#features)
2. [Installation](#installation)
3. [Your First Table](#your-first-table)
4. [Columns](#columns)
   1. [TextColumn](#textcolumn)
   2. [BooleanColumn](#booleancolumn)
   3. [DateTimeColumn](#datetimecolumn)
   4. [HiddenColumn](#hiddencolumn)
   5. [ActionColumn](#actioncolumn)
5. [Filters](#filters)
   1. [TextFilter](#textfilter)
   2. [ChoiceFilter](#choicefilter)
   3. [BooleanChoiceFilter](#booleanchoicefilter)
6. [Configuration](#configuration)
   1. [Table Dataset Options](#table-dataset-options)
   2. [Table Options](#table-options)
7. [Common Use-Cases](#common-use-cases)
   1. [Custom Doctrine Queries](#custom-doctrine-queries)
   2. [Detail View](#detail-view)
8. [Contributing](#contributing)

## Features

* Create bootstrap-tables in PHP
* Twig render function
* global filtering (server side)
* column based filtering (advanced search)
* column sorting (server side)
* Pagination (service side)
* different column types
* bootstrap-table extensions
  * [sticky-header](https://bootstrap-table.com/docs/extensions/sticky-header/)
  * [export](https://bootstrap-table.com/docs/extensions/export/)
  * [page-jump-to](https://bootstrap-table.com/docs/extensions/page-jump-to/)
  * [toolbar](https://bootstrap-table.com/docs/extensions/toolbar/) with [advanced-search](https://bootstrap-table.com/docs/extensions/toolbar/#advancedsearch)



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

#### Add Assets into your base.html.twig

**CSS**:

``` html
<!-- Bootstap and FontAwesome CSS dependency -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

<!-- bootstrap-table CSS with all used extensions -->
{{ hello_bootstrap_table_css() }}
```

**JavaScript**:

```html
<!-- jQuery and Bootstrap JS dependency -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- bootstrap-table JS with all used extensions -->
{{ hello_bootstrap_table_js() }}
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
                'buttons' => array( //see ActionButton for more examples.
                    array(
                        'displayName' => 'open',
                        'routeName' => 'show_user',
                        'classNames' => 'btn btn-xs' 
                        'additionalClassNames' => 'btn-success mr-1'
                    ),
                    array(
                        'displayName' => 'edit',
                        'routeName' => 'edit_user',
                        'classNames' => 'btn btn-xs btn-warning'
                    )
                )
            ));
    }

    protected function getEntityClass()
    {
        return User::class;
    }
}
```


### Step 2: In the Controller

``` php
// src/Controller/UserController.php

use HelloSebastian\HelloBootstrapTableBundle\HelloBootstrapTableFactory;
use App\HelloTable\UserTable;
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

``` twig
{# index.html.twig #}

{% extends 'base.html.twig' %}

{% block body %}
    {{ hello_bootstrap_table_render(table) }}
{% endblock %}
```

The Twig function will render a `table` with all attributes configured.



## Columns

### TextColumn

Represents column with text. With formatter you can create complex columns.

#### Options

| Option          | Type           | Default                        | Description                                                  |
| --------------- | -------------- | ------------------------------ | ------------------------------------------------------------ |
| title           | string / null  | null                           | Set column title. If no value is set, the specified attribute name is taken. |
| field           | string / null  | null                           | Set internal field name for bootstrap-table. If no value is set, the specified attribute name is taken. |
| width           | integer / null | null                           | column width in px                                           |
| widthUnit       | string         | "px"                           | Unit of width.                                               |
| class           | string / null  | null                           | The column class name.                                       |
| formatter       | string / null  | null                           | JavaScript function name for formatter. (see [formatter](https://bootstrap-table.com/docs/api/column-options/#formatter)) |
| footerFormatter | string / null  | null                           | JavaScript function name for footer formatter.               |
| searchable      | bool           | true                           | enable / disable filtering for this column                   |
| sortable        | bool           | true                           | enable / disable sortable for this column                    |
| switchable      | bool           | true                           | enable / disable interactive hide and show of column.        |
| visible         | bool           | true                           | show / hide column                                           |
| emptyData       | string         | ""                             | default value if attribute from entity is null               |
| sort            | Closure / null | null                           | custom sort query callback (see example)                     |
| filter          | Closure / null | null                           | custom filter query callback (see example)                   |
| data            | Closure / null | null                           | custom data callback (see example)                           |
| align           | string / null  | null                           | Indicate how to align the column data. `'left'`, `'right'`, `'center'` can be used. |
| halign          | string / null  | null                           | Indicate how to align the table header. `'left'`, `'right'`, `'center'` can be used. |
| valign          | string / null  | null                           | Indicate how to align the cell data. `'top'`, `'middle'`, `'bottom'` can be used. |
| falign          | string / null  | null                           | Indicate how to align the table footer. `'left'`, `'right'`, `'center'` can be used. |
| filter          | array          | `[TextFilter::class, array()]` | Set filter to column (see [Filters](#filters))               |
| filterControl   | string         | "input"                        | render text field in column header                           |

#### Example

```php
//use statements for search and sort option
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

->add('username', TextColumn::class, array(
    'title' => 'Username',
    'emptyData' => "No Username found.",
    
    //optional overrides ...
    'data' => function (User $user) { //entity from getEntityClass
        //you can return what ever you want ...  
        return $user->getId() . " " . $user->getUsername();
    },

    'sort' => function (QueryBuilder $qb, $direction) { //execute if user sort this column
        $qb->addOrderBy('username', $direction);
    },

    'search' => function (Composite $composite, QueryBuilder $qb, $dql, $search, $key) {
      	//first add condition to $composite
        //don't forget the '?' before $key
        $composite->add($qb->expr()->like($dql, '?' . $key));
      
      	//then bind search to query
        $qb->setParameter($key, '%' . $search . '%');
    }
))
```

**search** Option:

The search option seems a bit complicated at first, but it allows full control over the query in the column.

| Paramenter name        | Description                                                  |
| ---------------------- | ------------------------------------------------------------ |
| `Composite $composite` | In the global search all columns are connected as or. In the advanced search all columns are combined with an and-connection. With `$composite` more parts can be added to the query. |
| `QueryBuilder $qb`     | `$qb` holds the use QueryBuilder. It is the same instance as can be queried with `getQueryBuilder()` in the table class. |
| `$dql`                 | `$dql` represents the "path" to the variable in the query (e.g. `user.username` or in case of a JOIN `costCentre.name`) |
| `$search`              | The search in the type of a string.                          |
| `$key`                 | The index of the columns already gone through. The index is used for parameter binding to the query. |



### BooleanColumn

Represents column with boolean values.

#### Options

All options of TextColumn.

`advancedSearchType` is set to `checkbox` by default.

`filter` is set to `array(BooelanChoiceFilter::class, array())` by default.

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

`searchable`, `sortable`, `visible` and `switchable` are disabled by default.

#### Example

```php
->add("id", HiddenColumn::class)
```



### ActionColumn

Represents column for action buttons (show / edit / remove ...).

#### Options

All Options of TextColumn

`sortable`,  `searchable` and `switchable` are disable by default.

`formatter` is set to `defaultActionFormatter`. `cellStyle` is set to `defaultActionCellStyle`.

**And:**

| Option  | Type  | Default | Description                              |
| ------- | ----- | ------- | ---------------------------------------- |
| buttons | array | [ ]     | array of buttons configuration as array. |

#### Example

```php
->add("actions", ActionColumn::class, array( // key "actions" can be chosen freely.
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
            // 'classNames' => 'btn btn-xs' (see below for more information)
            'additionalClassNames' => 'btn-warning'
       )
  	)
))
```

#### ActionButtons

| Option               | Type   | Default     | Description                                                  |
| -------------------- | ------ | ----------- | ------------------------------------------------------------ |
| displayName          | string | ""          | label of button                                              |
| routeName            | string | ""          | route name                                                   |
| routeParams          | array  | array("id") | Array of property value names for the route parameters. By default is `id` set. |
| classNames           | string | ""          | CSS class names which added directly to the `a` element. Overrides default class names from YAML config. |
| additionalClassNames | string | ""          | You can set default class names in YAML config. Then you can add additional class names to the button without override the default config. |

#### YAML Example

```yaml
# config/packages/hello_table.yaml

hello_bootstrap_table:
    action_button_options:
        classNames: 'btn btn-xs'
```

YAML config options are set to all buttons. If you want override global options from YAML config use `classNames` option.

## Filters

Filters can be used to generate predefined queries. In addition, different input fields for the filters are displayed (currently only under the Advanced Search).

### TextFilter

With the TextFilter you can filter by text within the column. TextFilter is set by default to all columns.

#### Options

| Option                  | Type           | Default                                | Description                                         |
| ----------------------- | -------------- | -------------------------------------- | --------------------------------------------------- |
| advSearchFieldFormatter | string         | "defaultAdvSearchTextField"            | Set JavaScript function name to format input field. |
| placeholder             | string /  null | `title` option from column with " ..." | Set HTML placeholder for input field.               |

If you want to change `advSearchFieldFormatter`, you also need to create a JavaScript function with the same name in the `window` scope. As an example here is the default function:

```javascript
//value can be undefined
window.defaultAdvSearchTextField = function (field, filterOptions, value) {
    let val = value || "";
    return `<input type="text" value="${val}" class="form-control" name="${field}" placeholder="${filterOptions.placeholder}" id="${field}">`;
};
```

#### Example

```php
use HelloSebastian\HelloBootstrapTableBundle\Filters\TextFilter;

->add('firstName', TextColumn::class, array(
    'title' => 'First name',
    'filter' => array(TextFilter::class, array(
        'placeholder' => 'Enter first name ...'
    ))
))
```

### ChoiceFilter

With the ChoiceFilter you can create a `select` input field.

#### Options

All Options from TextFilter.

`advSearchFieldFormatter` is set to `defaultAdvSearchChoiceField`.

**And**:

| Option  | Type  | Default | Description                                                  |
| ------- | ----- | ------- | ------------------------------------------------------------ |
| choices | array | [ ]     | Key - Values pair of choices. Key: `value` attribute of `select` field; Value: display name of options in `select` field. |

#### Example

```php
use HelloSebastian\HelloBootstrapTableBundle\Filters\ChoiceFilter;

->add('department.name', TextColumn::class, array(
    'title' => 'Department',
    'filter' => array(ChoiceFilter::class, array(
        'choices' => array(
            'null' => 'All', //null is special key word. If null is set Query Builder skip this column.
            'IT' => 'IT',
            'Sales' => 'Sales'
        )
    ))
))
```

### BooleanChoiceFilter

BooleanChoiceFilter is a special `ChoiceFilter` with default choices and query expression. The expression is optimized for boolean values.

#### Options

All Options from ChoiceFilter.

If you use BooleanChoiceFilter inside a BooleanColumn, the `trueLabel` and `falseLabel` options from BooleanColumn are taken for `true` and `false` for the `choices` option by default.

If not `choices` is set to:

```php
"choices" => array(
    "null" => "All",
    "true" => "True",   // key must be "true", if you want allow true
    "false" => "False"  // key must be "false", if you want allow false
)
```

#### Example

```php
->add("isActive", BooleanColumn::class, array(
    'title' => 'is active',
  	'filter' => array(BooleanChoiceFilter::class, array( // only if you want to override the choices
        'choices' => array(
            "null" => "Alle",   // instead of "all"
            "true" => "Ja",     // instead of "yes"
            "false" => "Nein"   // instead of "no"
        )
    )),
    'trueLabel' => 'yes',
    'falseLabel' => 'no'
))
```



## Configuration


### Table Dataset Options

Table Dataset are provided directly to the `bootstrap-table` as data-attributes and are a collection of setting options for the table.

#### Options

| Option                     | Type   | Default                                                     |
| -------------------------- | ------ | ----------------------------------------------------------- |
| pagination                 | bool   | true                                                        |
| search                     | bool   | true                                                        |
| show-columns               | bool   | true                                                        |
| show-footer                | bool   | true                                                        |
| filter-control             | bool   | false                                                       |
| show-refresh               | bool   | true                                                        |
| toolbar                    | string | "#toolbar"                                                  |
| page-list                  | string | "[10, 25, 50, 100, 200, 500, All]"                          |
| page-size                  | int    | 25                                                          |
| sort-reset                 | bool   | true                                                        |
| pagination-V-Align         | string | "both"                                                      |
| undefined-text             | string | ""                                                          |
| locale                     | string | "en-US"                                                     |
| advanced-search            | bool   | true                                                        |
| id-table                   | string | class name of table with counter. (`$this->getTableName()`) |
| click-to-select            | bool   | true                                                        |
| show-jump-to               | bool   | true                                                        |
| show-export                | bool   | true                                                        |
| export-types               | string | "['csv', 'txt'', 'excel']"                                  |
| export-options             | array  | see under table*                                            |
| detail-view                | bool   | false                                                       |
| detail-formatter           | string | ""                                                          |
| detail-view-align          | string | ""                                                          |
| detail-view-icon           | bool   | true                                                        |
| detail-view-by-click       | bool   | false                                                       |
| sticky-header              | bool   | true                                                        |
| sticky-header-offset-left  | int    | 0                                                           |
| sticky-header-offset-right | int    | 0                                                           |
| sticky-header-offset-y     | int    | 0                                                           |

`export-options`:

```php
array(
    'fileName' => (new \DateTime('now'))->format('Y-m-d_H-i-s') . '_export',
    'ignoreColumn' => array("checkbox", "actions"),
    'csvSeparator' => ';'
)
```

#### Examples

**Inside from Table class:**

``` php
// src/HelloTable/UserTable.php

class UserTable extends HelloBootstrapTable
{
    ...
      
    protected function buildColumns(ColumnBuilder $builder, $options)
    {
        $this->setTableDataset(array(
            'locale' => 'de-DE'
        ));
      
      	// ... $builder->add()
    }
}
```

**Outside from Table class:**

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

**YAML config:**

YAML config options are set to all tables. If you want override global options from YAML config use `setTableDataset` method in or outside from `HelloBootstrapTable`.

```yaml
# config/packages/hello_table.yaml

hello_bootstrap_table:
    table_dataset_options:
        locale: 'de-DE' # see Table Dataset Options
```



### Table Options

All options that should not be provided directly as data-attributes of the table are managed here.

#### Options

| Option                     | Type   | Default                         |
| -------------------------- | ------ | ------------------------------- |
| tableClassNames            | string | "table table-stripped table-sm" |
| enableCheckbox             | bool   | false                           |
| bulkIdentifier             | string | "id"                            |
| bulkUrl                    | string | ""                              |
| bulkActionSelectClassNames | string | "form-control"                  |
| bulkActions                | array  | [ ]                             |
| bulkButtonName             | string | "Okay"                          |
| bulkButtonClassNames       | string | "btn btn-primary"               |


#### Examples

**Inside from Table class:**

``` php
// src/HelloTable/UserTable.php

class UserTable extends HelloBootstrapTable
{
    ...

    protected function buildColumns(ColumnBuilder $builder, $options)
    {
        $this->setTableOptions(array(
            'enableCheckbox' => false
        ));
      
      	// ... $builder->add()
    }
}
```

**Outside from Table class:**

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

**YAML config:**

YAML config options are set to all tables. If you want override global options from YAML config use `setTableDataset` method in or outside from `HelloBootstrapTable`.

```yaml
# config/packages/hello_table.yaml

hello_bootstrap_table:
    table_options:
        enableCheckbox: false # see Table Options
```



## Common Use-Cases

### Custom Doctrine Queries

Sometimes you don't want to display all the data in a database table. For this you can "prefilter" the Doctrine query.

#### Example

```php
/**
  * @Route("/", name="default")
  */
public function index(Request $request, HelloBootstrapTableFactory $tableFactory)
{
    //first create a instance of your table
    $table = $tableFactory->create(TestTable::class);

    //then you can access the QueryBuilder from the table
    $table->getQueryBuilder()
        ->andWhere('department.name = :departmentName')
        ->setParameter('departmentName', 'IT');

    $table->handleRequest($request);
    if ($table->isCallback()) {
      	return $table->getResponse();
    }

    return $this->render("index.html.twig", array(
      	"table" => $table->createView()
    ));
}
```

### Detail View

You can expand rows in bootstrap-table. This option is called "detail view" and can be enabled in the datasets (by default this is disabled). For displaying the content of detail-view a formatter is needed (also to be specified in datasets). In the formatter you have access to the data of the table row. For complex representations Twig can also be used. See the example below.

```php
 protected function buildColumns(ColumnBuilder $builder, $options)
 {
     //enable detail-view and set formatter
     $this->setTableDataset(array(
         'detail-view' => true,
         'detail-formatter' => 'detailViewFormatter'
     ));

     $builder
       // other columns ...
       
       // detailView is not a database field and can be named as you like.
       // but the column should not displayed in the table (HiddenColumn)
       ->add('detailView', HiddenColumn::class, array(
           // override data callback (as attribute you can access the entity that you specified in getEntityClass())
           'data' => function (User $user) {
              // now you can return everthing you want (twig render included)
              // twig is provided by HelloBootstrapTable
              return $this->twig->render('user/detail_view.html.twig', array(
                 'user' => $user
              ));
           }
       ));
}
```

To display `detailView` as content of the expanded table row a formatter function must be created and `detailView` must be returned. Remember to create the formatter before calling `{{ hello_bootstrap_table_js() }}`.

```javascript
// index   => index of row inside table
// row     => data object of the row
// element => row DOM element
window.detailViewFormatter = function (index, row, element) {
    // detailView matched with the name from add('detailView', HiddenColumn::class). If you use a different name you must changed it here too.
    return row.detailView;
};
```

Alternative you can of course create your HTML with JavaScript inside the formatter.



## Contributing

Contributions are **welcome** and will be credited.