pop-csv
=======

[![Build Status](https://travis-ci.org/popphp/pop-csv.svg?branch=master)](https://travis-ci.org/popphp/pop-csv)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-csv)](http://cc.popphp.org/pop-csv/)


OVERVIEW
--------
`pop-csv` provides a streamlined way to work with PHP data and the CSV format.
It is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-csv` using Composer.

    composer require popphp/pop-csv

BASIC USAGE
-----------

### Serialize Data

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

$csvString = $data->serialize();
```

The $csvString variable now contains:

    first_name,last_name
    Bob,Smith
    Jane,Smith

### Unserialize Data

You can either pass the data object a direct string of serialized data or a file containing a string of
serialized data. It will detect which one it is and parse it accordingly.

##### String

```php
$csv     = new Pop\Csv\Csv($csvString);
$phpData = $csv->unserialize();
```

### Write to File

```php
$phpData = [ ... ];

$data = new Pop\Csv\Csv($phpData);
$data->serialize();
$data->writeToFile('/path/to/file.csv');
```

### Output to HTTP

```php
$phpData = [ ... ];

$data = new Pop\Csv\Csv($phpData);
$data->serialize();
$data->outputToHttp();
```

##### Force download of file

```php
$phpData = [ ... ];

$data = new Pop\Csv\Csv($phpData);
$data->serialize('csv');
$data->outputToHttp('my-file.csv', true);
```

