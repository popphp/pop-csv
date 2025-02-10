<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Csv;

/**
 * CSV class
 *
 * @category   Pop
 * @package    Pop\Csv
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.1.1
 */
class Csv
{

    /**
     * CSV data in PHP
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * CSV string
     * @var ?string
     */
    protected ?string $string = null;

    /**
     * Constructor
     *
     * Instantiate the Csv object.
     *
     * @param  mixed $data
     */
    public function __construct(mixed $data = null)
    {
        if ($data !== null) {
            // If data is a file
            if (is_string($data) && (stripos($data, '.csv') !== false) && file_exists($data)) {
                $this->string = file_get_contents($data);
            // Else, if it's just data
            } else if (!is_string($data)) {
                $this->data = $data;
            // Else if it's a string or stream of data
            } else {
                $this->string = $data;
            }
        }
    }

    /**
     * Load CSV file
     *
     * @param  string $file
     * @param  array $options
     * @return Csv
     */
    public static function loadFile(string $file, array $options = []): Csv
    {
        $csv = new self($file);
        $csv->unserialize($options);
        return $csv;
    }

    /**
     * Load CSV string
     *
     * @param  string $string
     * @param  array $options
     * @return Csv
     */
    public static function loadString(string $string, array $options = []): Csv
    {
        $csv = new self($string);
        $csv->unserialize($options);
        return $csv;
    }

    /**
     * Load CSV data
     *
     * @param  array $data
     * @param  array $options
     * @return Csv
     */
    public static function loadData(array $data, array $options = []): Csv
    {
        $csv = new self($data);
        $csv->serialize($options);
        return $csv;
    }

    /**
     * Load CSV file and get data
     *
     * @param  string $file
     * @param  array  $options
     * @return array
     */
    public static function getDataFromFile(string $file, array $options = []): array
    {
        $csv = new self($file);
        return $csv->unserialize($options);
    }

    /**
     * Write data to file
     *
     * @param  array  $data
     * @param  string $to
     * @param  array  $options
     * @return void
     */
    public static function writeDataToFile(array $data, string $to, array $options = []): void
    {
        $csv = new self($data);
        $csv->serialize($options);
        $csv->writeToFile($to);
    }

    /**
     * Write data to file
     *
     * @param  array  $data
     * @param  string $to
     * @param  string $delimiter
     * @param  array  $exclude
     * @param  array  $include
     * @return void
     *@throws Exception
     */
    public static function writeTemplateToFile(
        array $data, string $to, string $delimiter = ',', array $exclude = [], array $include = []
    ): void
    {
        $csv = new self($data);
        $csv->writeBlankFile($to, $delimiter, $exclude, $include);
    }

    /**
     * Write data to file
     *
     * @param  array  $data
     * @param  array  $options
     * @param  string $filename
     * @param  bool   $forceDownload
     * @param  array  $headers
     * @return void
     */
    public static function outputDataToHttp(
        array $data, array $options = [], string $filename = 'pop-data.csv', bool $forceDownload = true, array $headers = []
    ): void
    {
        $csv = new self($data);
        $csv->serialize($options);
        $csv->outputToHttp($filename, $forceDownload, $headers);
    }

    /**
     * Write data to file
     *
     * @param  array  $data
     * @param  string $filename
     * @param  bool   $forceDownload
     * @param  array  $headers
     * @param  string $delimiter
     * @param  array  $exclude
     * @throws Exception
     * @return void
     */
    public static function outputTemplateToHttp(
        array $data, string $filename = 'pop-data-template.csv', bool $forceDownload = true,
        array $headers = [], string $delimiter = ',', array $exclude = []
    )
    {
        $csv = new self($data);
        $csv->outputBlankFileToHttp($filename, $forceDownload, $headers, $delimiter, $exclude);
    }

    /**
     * Process CSV options
     *
     * @param  array $options
     * @return array
     */
    public static function processOptions(array $options): array
    {
        $options['delimiter'] = (isset($options['delimiter'])) ? $options['delimiter']     : ',';
        $options['enclosure'] = (isset($options['enclosure'])) ? $options['enclosure']     : '"';
        $options['escape']    = (isset($options['escape']))    ? $options['escape']        : '"';
        $options['fields']    = (isset($options['fields']))    ? (bool)$options['fields']  : true;
        $options['newline']   = (isset($options['newline']))   ? (bool)$options['newline'] : true;
        $options['limit']     = (isset($options['limit']))     ? (int)$options['limit']    : 0;
        $options['map']       = (isset($options['map']))       ? $options['map']           : [];
        $options['columns']   = (isset($options['columns']))   ? $options['columns']       : [];

        return $options;
    }

    /**
     * Serialize the data to a CSV string
     *
     * @param  array $options
     * @return string
     */
    public function serialize(array $options = []): string
    {
        $this->string = self::serializeData($this->data, $options);
        return $this->string;
    }

    /**
     * Unserialize the string to data
     *
     * @param  array  $options
     * @return mixed
     */
    public function unserialize(array $options = []): mixed
    {
        $this->data = self::unserializeString($this->string, $options);
        return $this->data;
    }

    /**
     * Set data
     *
     * @param  array $data
     * @return Csv
     */
    public function setData(array $data): Csv
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set string
     *
     * @param  string $string
     * @return Csv
     */
    public function setString(string $string): Csv
    {
        $this->string = $string;
        return $this;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * Check if data was serialized
     *
     * @return bool
     */
    public function isSerialized(): bool
    {
        return ($this->string !== null);
    }

    /**
     * Check if string was unserialized
     *
     * @return bool
     */
    public function isUnserialized(): bool
    {
        return ($this->data !== null);
    }

    /**
     * Output CSV string data to HTTP
     *
     * @param  string $filename
     * @param  bool   $forceDownload
     * @param  array  $headers
     * @return void
     */
    public function outputToHttp(string $filename = 'pop-data.csv', bool $forceDownload = true, array $headers = []): void
    {
        // Attempt to serialize data if it hasn't been done yet
        if (($this->string === null) && ($this->data !== null)) {
            $this->serialize();
        }

        $this->prepareHttp($filename, $forceDownload, $headers);

        echo $this->string;
    }

    /**
     * Output CSV headers only in a blank file to HTTP
     *
     * @param  string $filename
     * @param  bool   $forceDownload
     * @param  array  $headers
     * @param  string $delimiter
     * @param  array  $exclude
     * @throws Exception
     * @return void
     */
    public function outputBlankFileToHttp(
        string $filename = 'pop-data.csv', bool $forceDownload = true, array $headers = [], string $delimiter = ',', array $exclude = []
    ): void
    {
        // Attempt to serialize data if it hasn't been done yet
        if (($this->string === null) && ($this->data !== null) && isset($this->data[0])) {
            $fieldHeaders = self::getFieldHeaders($this->data[0], $delimiter, $exclude);
        } else {
            throw new Exception('Error: The data has not been set.');
        }

        $this->prepareHttp($filename, $forceDownload, $headers);
        echo $fieldHeaders;
    }

    /**
     * Prepare output to HTTP
     *
     * @param  string $filename
     * @param  bool   $forceDownload
     * @param  array  $headers
     * @return void
     */
    public function prepareHttp(string $filename = 'pop-data.csv', bool $forceDownload = true, array $headers = []): void
    {
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/csv';
        }
        if (!isset($headers['Content-Disposition'])) {
            $headers['Content-Disposition'] = (($forceDownload) ? 'attachment; ' : null) . 'filename=' . $filename;
        }

        // Send the headers and output the file
        if (!headers_sent()) {
            header('HTTP/1.1 200 OK');
            foreach ($headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
    }

    /**
     * Output CSV data to a file
     *
     * @param  string $to
     * @return void
     */
    public function writeToFile(string $to): void
    {
        // Attempt to serialize data if it hasn't been done yet
        if (($this->string === null) && ($this->data !== null)) {
            $this->serialize();
        }

        file_put_contents($to, $this->string);
    }

    /**
     * Output CSV headers only to a blank file
     *
     * @param  string $to
     * @param  string $delimiter
     * @param  array  $exclude
     * @param  array  $include
     * @return void
     *@throws Exception
     */
    public function writeBlankFile(string $to, string $delimiter = ',', array $exclude = [], array $include = []): void
    {
        // Attempt to get field headers and output file
        if (($this->string === null) && ($this->data !== null) && isset($this->data[0])) {
            file_put_contents($to, self::getFieldHeaders($this->data[0], $delimiter, $exclude, $include));
        } else {
            throw new Exception('Error: The data has not been set.');
        }
    }

    /**
     * Append additional CSV data to a pre-existing file
     *
     * @param  string $file
     * @param  array  $data
     * @param  array  $options
     * @param  bool   $validate
     * @throws Exception
     * @return void
     */
    public static function appendDataToFile(string $file, array $data, array $options = [], bool $validate = true): void
    {
        if (!file_exists($file)) {
            throw new Exception("Error: The file '" . $file . "' does not exist.");
        }

        foreach ($data as $row) {
            self::appendRowToFile($file, $row, $options, $validate);
        }
    }

    /**
     * Append additional CSV row of data to a pre-existing file
     *
     * @param  string $file
     * @param  array  $row
     * @param  array  $options
     * @param  bool   $validate
     * @throws Exception
     * @return void
     */
    public static function appendRowToFile(string $file, array $row, array $options = [], bool $validate = true): void
    {
        if (!file_exists($file)) {
            throw new Exception("Error: The file '" . $file . "' does not exist.");
        }

        if ($validate) {
            $keys    = array_keys($row);
            $headers = array_map(
                function($value) { return str_replace('"', '', $value); }, explode(',', trim(fgets(fopen($file, 'r'))))
            );

            if ($keys != $headers) {
                throw new Exception("Error: The new data's columns do not match the CSV files columns.");
            }
        }

        $options = self::processOptions($options);
        $csvRow  = self::serializeRow(
            (array)$row, [], [], $options['delimiter'], $options['enclosure'], $options['escape'],
            $options['newline'], $options['limit'], $options['map'], $options['columns']
        );

        file_put_contents($file, $csvRow, FILE_APPEND);
    }

    /**
     * Convert the data into CSV format.
     *
     * @param  mixed $data
     * @param  array $options
     * @return string
     */
    public static function serializeData(mixed $data, array $options = []): string
    {
        $keys    = array_keys($data);
        $isAssoc = false;

        foreach ($keys as $key) {
            if (!is_numeric($key)) {
                $isAssoc = true;
            }
        }

        if ($isAssoc) {
            $newData = [];
            foreach ($data as $key => $value) {
                $newData = array_merge($newData, $value);
            }
            $data = $newData;
        }

        if (isset($options['exclude'])) {
            $exclude = (!is_array($options['exclude'])) ? [$options['exclude']] : $options['exclude'];
        } else {
            $exclude = [];
        }

        if (isset($options['include'])) {
            $include = (!is_array($options['include'])) ? [$options['include']] : $options['include'];
        } else {
            $include = [];
        }

        $options  = self::processOptions($options);
        $csv      = '';
        $firstKey = array_keys($data)[0];

        if (is_array($data) && isset($data[$firstKey]) &&
            (is_array($data[$firstKey]) || ($data[$firstKey] instanceof \ArrayObject)) && ($options['fields'])) {
            $csv .= self::getFieldHeaders((array)$data[$firstKey], $options['delimiter'], $exclude, $include);
        }

        // Initialize and clean the field values.
        foreach ($data as $value) {
            $csv .= self::serializeRow(
                (array)$value, $exclude, $include, $options['delimiter'], $options['enclosure'], $options['escape'],
                $options['newline'], $options['limit'], $options['map'], $options['columns']
            );
        }

        return $csv;
    }

    /**
     * Parse the CSV string into a PHP array
     *
     * @param  string $string
     * @param  array  $options
     * @return array
     */
    public static function unserializeString(string $string, array $options = []): array
    {
        $options   = self::processOptions($options);
        $lines     = preg_split("/((\r?\n)|(\r\n?))/", $string);
        $data      = [];
        $fieldKeys = [];

        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pop-csv-tmp-' . time() . '.csv';
        file_put_contents($tempFile, $string);

        if ($options['fields']) {
            $fieldNames = str_getcsv($lines[0], $options['delimiter'], $options['enclosure'], $options['escape']);
            foreach ($fieldNames as $name) {
                $fieldKeys[] = trim($name);
            }
        }

        if (($handle = fopen($tempFile, 'r')) !== false) {
            while (($dataFields = fgetcsv($handle, 1000, $options['delimiter'], $options['enclosure'], $options['escape'])) !== false) {
                if (($options['fields']) && (count($dataFields) == count($fieldKeys)) && ($dataFields != $fieldKeys)) {
                    $d = [];
                    foreach ($dataFields as $i => $value) {
                        $d[$fieldKeys[$i]] = $value;
                    }
                    $data[] = $d;
                } else if ($dataFields != $fieldKeys) {
                    $data[] = $dataFields;
                }
            }
            fclose($handle);
            unlink($tempFile);
        }

        return $data;
    }

    /**
     * Serialize single row of data
     *
     * @param  array  $value
     * @param  array  $exclude
     * @param  array  $include
     * @param  string $delimiter
     * @param  string $enclosure
     * @param  string $escape
     * @param  bool   $newline
     * @param  int    $limit
     * @param  array  $map
     * @param  array  $columns
     * @return string
     */
    public static function serializeRow(
        array $value, array $exclude = [], array $include = [], string $delimiter = ',', string $enclosure = '"',
        string $escape = '"', bool $newline = true, int $limit = 0, array $map = [], array $columns = []
    ): string
    {
        $rowAry = [];
        foreach ($value as $key => $val) {
            if (!in_array($key, $exclude) && (empty($include) || in_array($key, $include))) {
                if (!$newline) {
                    $val = str_replace(["\n", "\r"], [" ", " "], $val);
                }
                if ((int)$limit > 0) {
                    $val = substr($val, 0, (int)$limit);
                }

                // Handle array map/column
                if (is_array($val)) {
                    if (!empty($val) && isset($map[$key]) && isset($val[$map[$key]])) {
                        $val = $val[$map[$key]];
                    } else if (!empty($val) && isset($columns[$key]) && isset($val[0]) && isset($val[0][$columns[$key]])) {
                        $val = implode(',', array_column($val, $columns[$key]));
                    } else {
                        $val = null;
                    }
                }

                if (str_contains($val, $enclosure)) {
                    $val = str_replace($enclosure, $escape . $enclosure, $val);
                }
                if ((str_contains($val, $delimiter)) || (str_contains($val, "\n")) ||
                    (str_contains($val, $escape . $enclosure))) {
                    $val = $enclosure . $val . $enclosure;
                }

                $rowAry[] = $val;
            }
        }

        return implode($delimiter, $rowAry) . "\n";
    }

    /**
     * Get field headers
     *
     * @param  mixed  $data
     * @param  string $delimiter
     * @param  array  $exclude
     * @param  array  $include
     * @return string
     */
    public static function getFieldHeaders(mixed $data, string $delimiter = ',', array $exclude = [], array $include = []): string
    {
        $headers    = array_keys($data);
        $headersAry = [];
        foreach ($headers as $header) {
            if (!in_array($header, $exclude) && (empty($include) || in_array($header, $include))) {
                $headersAry[] = $header;
            }
        }
        return implode($delimiter, $headersAry) . PHP_EOL;
    }

    /**
     * Determine if the string is valid CSV
     *
     * @param  string $string
     * @return bool
     */
    public static function isValid(string $string): bool
    {
        $lines  = preg_split("/((\r?\n)|(\r\n?))/", $string);
        $fields = [];
        if (isset($lines[0])) {
            $fields = str_getcsv($lines[0]);
        }
        return (count($fields) > 0);
    }

    /**
     * Render CSV string data to string
     *
     * @return string
     */
    public function __toString(): string
    {
        // Attempt to serialize data if it hasn't been done yet
        if (($this->string === null) && ($this->data !== null)) {
            $this->serialize();
        }

        return $this->string;
    }

}
