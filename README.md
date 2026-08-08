pop-csv
=======

[![Build Status](https://github.com/popphp/pop-csv/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-csv/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-csv)](http://cc.popphp.org/pop-csv/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Loading Shortcuts](#loading-shortcuts)
* [Options](#options)
* [Output CSV](#output-csv)
* [Blank Templates](#blank-templates)
* [Append Data](#append-data)
* [Read Large Files](#read-large-files)
* [Validating a CSV String](#validating-a-csv-string)
* [Errors](#errors)

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
        "popphp/pop-csv" : "^4.3.0"
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

How the constructor's first argument is interpreted:

- A non-string value (an array, `ArrayObject`, etc.) is treated as **PHP data**.
- A string is treated as a **file path** only if it contains `.csv` or `.tsv` (case-insensitive) *and*
  a file actually exists at that path — its contents are read in immediately.
- Any other string is treated as **raw CSV/TSV text** to be parsed later.

### Accessors and state

A `Csv` object holds two properties: the PHP array (`data`) and the CSV text (`string`). You can read or
set either directly, and check which one(s) have been populated:

```php
$csv = new Pop\Csv\Csv($phpData);

$csv->getData();          // the PHP array, or null if it hasn't been set/unserialized
$csv->getString();        // the CSV string, or null if it hasn't been set/serialized
$csv->setData($otherData);
$csv->setString($otherCsvString);

$csv->isUnserialized();   // true once $data is populated
$csv->isSerialized();     // true once $string is populated
```

`writeToFile()`, `outputToHttp()`, and casting the object to a string all auto-serialize `$data` into
`$string` if it hasn't happened yet, so `echo`-ing a `Csv` object built from data works without an explicit
`serialize()` call:

```php
$csv = new Pop\Csv\Csv($phpData);
echo $csv; // same as echo $csv->serialize();
```

[Top](#pop-csv)

Loading Shortcuts
------------------

Static methods are available for the common "load and immediately use" and "build and immediately output"
patterns, so you don't need to instantiate a `Csv` object and call a second method yourself:

```php
use Pop\Csv\Csv;

$csv     = Csv::loadFile('/path/to/file.csv');        // new Csv($file) + unserialize(), returns the Csv object
$csv     = Csv::loadString($csvString);               // same, from a raw CSV string
$csv     = Csv::loadData($phpData);                   // new Csv($data) + serialize(), returns the Csv object
$phpData = Csv::getDataFromFile('/path/to/file.csv'); // like loadFile(), but returns the array directly

Csv::writeDataToFile($phpData, '/path/to/file.csv');  // build + serialize + writeToFile in one call
Csv::outputDataToHttp($phpData, null, 'my-file.csv'); // build + serialize + outputToHttp in one call
```

All of these accept the same `?array $options` as the constructor.

[Top](#pop-csv)

Options
-------

Where serializing or unserializing CSV data, there are a set of options available to tailor the process:

```php
$options = [
    'exclude'        => ['id'],    // An array of fields to exclude
    'include'        => ['email'], // An array of fields to explicitly include, omitting all others
    'delimiter'      => ',',       // Delimiter defaults to ',' - could be "\t" or something else
    'enclosure'      => '"',       // Default string enclosure, i.e. "my data","other data"
    'escape'         => '"',       // String character to escape in the data, i.e. "my ""data"" here"
    'fields'         => true,      // Include the field names in the first row 
    'newline'        => true,      // Allow newlines in a data cell. Set as false to trim them
    'limit'          => 0,         // Character limit of a data cell. 0 means no limit
    'map'            => [],        // Array key of a single array value to map to the data cell value
    'columns'        => [],        // Array key of a multidimensional array value to map and join into the data cell value
    'escapeFormulas' => false,     // Guard against CSV/Excel formula injection by prefixing risky cells with a single quote
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

**Guard Against Formula Injection**

If any of the data being serialized may have originated from user input, opening the resulting CSV in a
spreadsheet application (Excel, Google Sheets, LibreOffice) can execute formulas hidden in cells that start
with `=`, `+`, `-` or `@`. Set the `escapeFormulas` option to `true` to neutralize this by prefixing any
non-numeric cell that starts with one of those characters with a single quote:

```php
$options = ['escapeFormulas' => true];
$data    = [['note' => '=cmd|"/c calc"!A1']];
$csv     = new Pop\Csv\Csv($data, $options);
echo $csv->serialize(); // note\n'=cmd|"/c calc"!A1\n
```

This is disabled by default to preserve existing output for data that isn't user-controlled.

**TSV (and Other Delimiters)**

Set `delimiter` to `"\t"` to work with tab-separated data instead of comma-separated. A file path ending in
`.tsv` is auto-detected by the constructor exactly like `.csv` is:

```php
$csv = new Pop\Csv\Csv($phpData, ['delimiter' => "\t"]);
$csv->writeToFile('/path/to/file.tsv');

$loaded  = Csv::loadFile('/path/to/file.tsv', ['delimiter' => "\t"]);
$phpData = $loaded->getData();
```

The `delimiter` option isn't limited to `,` and `\t` — any single-character delimiter works the same way.

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

Blank Templates
----------------

If you need to hand someone a blank CSV containing only the header row (e.g. as a template they'll fill in
and re-upload), use the `*Blank*`/`*Template*` methods instead of the regular output methods. The header row
is derived from the keys of the first data row, so you still need at least one row of representative data —
you can't generate a template purely from a list of column names.

```php
use Pop\Csv\Csv;

$phpData = [
    ['first_name' => 'Bob', 'last_name' => 'Smith']
];

// Write just the header row to a file
$csv = new Pop\Csv\Csv($phpData);
$csv->writeBlankFile('/path/to/template.csv');

// Or output it straight to HTTP as a download
$csv->outputBlankFileToHttp('template.csv');

// One-liner static equivalents that don't require constructing a Csv object first
Csv::writeTemplateToFile($phpData, '/path/to/template.csv');
Csv::outputTemplateToHttp($phpData, 'template.csv');
```

All four accept the same trailing `$delimiter`, `$exclude`, and `$include` parameters as their regular
counterparts. Calling `writeBlankFile()`/`outputBlankFileToHttp()` before any data has been set throws
`Pop\Csv\Exception` — see [Errors](#errors).

[Top](#pop-csv)

Append Data
-----------

In the case of working with large data sets, you can append CSV data to an existing file on disk.
This prevents loading large amounts of data into memory that may exceed the PHP environment's limits.
The target file must already exist — appending is for adding to a file you've already created, not
creating one from scratch.

### Append multiple rows

```php
use Pop\Csv\Csv;

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

Csv::appendDataToFile('my-file.csv', $phpData);
```

### Append a single row

```php
Csv::appendRowToFile('my-file.csv', ['first_name' => 'John', 'last_name' => 'Smith']);
```

Both are also available as instance methods (`appendData()`/`appendRow()`), which use the `Csv` object's
own options instead of a fresh `$options` array:

```php
$csv = new Pop\Csv\Csv(null, ['delimiter' => "\t"]);
$csv->appendRow('my-file.tsv', ['first_name' => 'John', 'last_name' => 'Smith']);
```

### Column validation

By default, every append call re-reads the target file's header row and compares it against the new row's
keys (`array_keys($row)`), throwing `Pop\Csv\Exception` on a mismatch (including order). Pass `false` as the
last argument to skip this check — for example, if you're confident the shape matches and want to avoid the
extra read per row, or the file has no header row at all:

```php
Csv::appendRowToFile('my-file.csv', $row, [], false);
```

[Top](#pop-csv)

Read Large Files
----------------

Reading a CSV file with `Csv::getDataFromFile()`, `loadFile()` or `unserialize()` loads the entire result
into memory as a PHP array. For very large files, use `Csv::readRowsFromFile()` instead, which returns a
generator and yields one row at a time without ever holding the full file in memory:

```php
foreach (Csv::readRowsFromFile('my-large-file.csv') as $row) {
    // $row is processed one at a time; memory usage stays flat
    // regardless of how many rows the file has
}
```

It accepts the same options as the other read methods (`delimiter`, `enclosure`, `escape`, `fields`, etc.),
and throws `Pop\Csv\Exception` if the file doesn't exist.

### Counting rows without loading the file

If you just need a row count (e.g. for a progress bar before an import), `Csv::getRowCountFromFile()`
streams the file the same way, without building any array of the data at all:

```php
$rowCount = Csv::getRowCountFromFile('my-large-file.csv', ['headers' => true]);
```

Options: `headers` (bool, subtract 1 from the count if the file has a header row, default `false`),
`skip_blank` (bool, skip blank lines, default `true`), plus `delimiter`/`enclosure`/`escape`/`length`.

[Top](#pop-csv)

Validating a CSV String
------------------------

`Csv::isValid()` is a lightweight sanity check for a string you suspect might be CSV data — useful before
attempting to parse something from an untrusted or unknown source:

```php
if (Csv::isValid($string)) {
    $phpData = Csv::loadString($string)->getData();
}
```

It returns `false` for an empty string or for a string where the data rows don't all have the same number
of columns as the first row. It does not validate character encoding or confirm the actual delimiter/
enclosure characters in use — it's a quick structural check, not a full CSV parser/validator.

[Top](#pop-csv)

Errors
------

All of the following throw `Pop\Csv\Exception` (which extends PHP's built-in `\Exception`):

- `writeBlankFile()` / `outputBlankFileToHttp()` — called before any data has been set on the `Csv` object.
- `appendDataToFile()` / `appendRowToFile()` (and the instance equivalents `appendData()`/`appendRow()`) —
  the target file doesn't exist.
- `appendRowToFile()` / `appendRow()` — `$validate` is `true` (the default) and the row's keys don't match
  the target file's existing header row.
- `readRowsFromFile()` — the target file doesn't exist.

```php
try {
    Csv::appendRowToFile('my-file.csv', $row);
} catch (Pop\Csv\Exception $e) {
    // handle a missing file or a column mismatch
}
```

[Top](#pop-csv)
