pop-csv
=======

[![Build Status](https://github.com/popphp/pop-csv/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-csv/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-csv)](http://cc.popphp.org/pop-csv/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Options](#options)
* [Output CSV](#output-csv)
* [Append Data](#append-data)

Overview
--------
`pop-csv` provides a streamlined way to work with PHP data and the CSV format.

It is a component of the [Pop PHP Framework](https://www.popphp.org/).

Install
-------

Install `pop-csv` using Composer.

    composer require popphp/pop-csv

Or, require it in your composer.json file

    "require": {
        "popphp/pop-csv" : "^4.2.0"
    }

[Top](#pop-csv)

Quickstart
----------

### Create a CSV string

```php
$phpData = [
    [
        'first_name' => 'Bob',
        'last_name'  => 'Smith'
    ],
    [
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

$data      = new Pop\Csv\Csv($phpData);
$csvString = $data->serialize();
```

The $csvString variable now contains:

    first_name,last_name
    Bob,Smith
    Jane,Smith

### Create data from a CSV string 

You can either pass the data object a direct string of serialized data or a file containing a string of
serialized data. It will detect which one it is and parse it accordingly.

```php
$csv     = new Pop\Csv\Csv($csvString);
$phpData = $csv->unserialize();
```

[Top](#pop-csv)

Options
-------

Where serializing or unserializing CSV data, there are a set of options available to tailor the process:

```php
$options = [
    'exclude'   => ['id'],    // An array of fields to exclude
    'include'   => ['email'], // An array of fields to explicitly include, omitting all others
    'delimiter' => ',',       // Delimiter defaults to ',' - could be "\t" or something else
    'enclosure' => '"',       // Default string enclosure, i.e. "my data","other data"
    'escape'    => '"',       // String character to escape in the data, i.e. "my ""data"" here"
    'fields'    => true,      // Include the field names in the first row 
    'newline'   => true,      // Allow newlines in a data cell. Set as false to trim them
    'limit'     => 0,         // Character limit of a data cell. 0 means no limit
    'map'       => [],        // Array key of a single array value to map to the data cell value
    'columns'   => [],        // Array key of a multidimensional array value to map and join into the data cell value
];
```

**Map/Columns Example**

```text
$users = [
    'id'       => 1,
    'username' => 'testuser',
    'country'  => [
        'name' => 'United States',
        'code' => 'US'
    ],
    'roles'    => [
        ['id' => 1, 'name' => 'Admin'],
        ['id' => 2, 'name' => 'Staff'],
    ]
];
```

```text
$options = [
    'map' => [
        'country' => 'code'
    ],
    'columns' => [
        'roles' => 'name'
    ]
]
```

Pass the options array to constructor method:

```php
$data      = new Pop\Csv\Csv($users, $options);
$csvString = $data->serialize();
echo $csvString;
```

The above will output the following CSV data:

```text
id,username,country,roles
1,testuser,US,"Admin,Staff"
```

[Top](#pop-csv)

Output CSV
----------

### Write to File

```php
$phpData = [
    [
        'first_name' => 'Bob',
        'last_name'  => 'Smith'
    ],
    [
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

$data = new Pop\Csv\Csv($phpData);
$data->writeToFile('/path/to/file.csv');
```

### Output to HTTP

```php
$phpData = [
    [
        'first_name' => 'Bob',
        'last_name'  => 'Smith'
    ],
    [
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

$data = new Pop\Csv\Csv($phpData);
$data->outputToHttp('my-file.csv');
```

##### Force download of file

Pass a `true` boolean as the second parameter, which forces `attachment` for the `Content-Disposition` header. 

```php
$data->outputToHttp('my-file.csv', true);
```

##### Additional HTTP headers

Additional HTTP headers can be passed to the third parameter:

```php
$data->outputToHttp('my-file.csv', false, ['X-Header' => 'some-header-value']);
```

[Top](#pop-csv)

Append Data
-----------

In the case of working with large data sets, you can append CSV data to an existing file on disk.
This prevents loading large amounts of data into memory that may exceed the PHP environment's limits.

```php
us Pop\Csv\Csv;

$phpData = [
    [
        'first_name' => 'Bob',
        'last_name'  => 'Smith'
    ],
    [
        'first_name' => 'Jane',
        'last_name'  => 'Smith'
    ]
];

Csv::appendDataToFile('my-file.csv', $data);
```

[Top](#pop-csv)


