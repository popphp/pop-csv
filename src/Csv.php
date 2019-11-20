<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.5
 */
class Csv
{

    /**
     * CSV data in PHP
     * @var mixed
     */
    protected $data = null;

    /**
     * CSV string
     * @var string
     */
    protected $string = null;

    /**
     * Constructor
     *
     * Instantiate the Csv object.
     *
     * @param  mixed $data
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
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
     * @return self
     */
    public static function loadFile($file, array $options = [])
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
     * @return self
     */
    public static function loadString($string, array $options = [])
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
     * @return self
     */
    public static function loadData(array $data, array $options = [])
    {
        $csv = new self($data);
        $csv->serialize($options);
        return $csv;
    }

    /**
     * Load CSV file and get data
     *
     * @param  string $file
     * @param  array $options
     * @return array
     */
    public static function getDataFromFile($file, array $options = [])
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
    public static function writeDataToFile(array $data, $to, array $options = [])
    {
        $csv = new self($data);
        $csv->serialize($options);
        $csv->writeToFile($to);
    }

    /**
     * Write data to file
     *
     * @param  array   $data
     * @param  array   $options
     * @param  string  $filename
     * @param  boolean $forceDownload
     * @param  array   $headers
     * @return void
     */
    public static function outputDataToHttp(
        array $data, array $options = [], $filename = 'pop-data.csv', $forceDownload = true, array $headers = []
    )
    {
        $csv = new self($data);
        $csv->serialize($options);
        $csv->outputToHttp($filename, $forceDownload, $headers);
    }

    /**
     * Process CSV options
     *
     * @param  array $options
     * @return array
     */
    public static function processOptions(array $options)
    {
        $options['delimiter'] = (isset($options['delimiter'])) ? $options['delimiter']     : ',';
        $options['enclosure'] = (isset($options['enclosure'])) ? $options['enclosure']     : '"';
        $options['escape']    = (isset($options['escape']))    ? $options['escape']        : '"';
        $options['fields']    = (isset($options['fields']))    ? (bool)$options['fields']  : true;
        $options['newline']   = (isset($options['newline']))   ? (bool)$options['newline'] : true;
        $options['limit']     = (isset($options['limit']))     ? (int)$options['limit']    : 0;

        return $options;
    }

    /**
     * Serialize the data to a CSV string
     *
     * @param  array $options
     * @return string
     */
    public function serialize(array $options = [])
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
    public function unserialize(array $options = [])
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
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set string
     *
     * @param  string $string
     * @return Csv
     */
    public function setString($string)
    {
        $this->string = $string;
        return $this;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Check if data was serialized
     *
     * @return boolean
     */
    public function isSerialized()
    {
        return (null !== $this->string);
    }

    /**
     * Check if string was unserialized
     *
     * @return boolean
     */
    public function isUnserialized()
    {
        return (null !== $this->data);
    }

    /**
     * Output CSV string data to HTTP
     *
     * @param  string  $filename
     * @param  boolean $forceDownload
     * @param  array   $headers
     * @return void
     */
    public function outputToHttp($filename = 'pop-data.csv', $forceDownload = true, array $headers = [])
    {
        // Attempt to serialize data if it hasn't been done yet
        if ((null === $this->string) && (null !== $this->data)) {
            $this->serialize();
        }

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

        echo $this->string;
    }

    /**
     * Output CSV data to a file
     *
     * @param  string $to
     * @return void
     */
    public function writeToFile($to)
    {
        // Attempt to serialize data if it hasn't been done yet
        if ((null === $this->string) && (null !== $this->data)) {
            $this->serialize();
        }

        file_put_contents($to, $this->string);
    }

    /**
     * Append additional CSV data to a pre-existing file
     *
     * @param  string  $file
     * @param  array   $data
     * @param  array   $options
     * @param  boolean $validate
     * @return void
     */
    public static function appendDataToFile($file, $data, array $options = [], $validate = true)
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
     * @param  string  $file
     * @param  array   $row
     * @param  array   $options
     * @param  boolean $validate
     * @return void
     */
    public static function appendRowToFile($file, array $row, array $options = [], $validate = true)
    {
        if (!file_exists($file)) {
            throw new Exception("Error: The file '" . $file . "' does not exist.");
        }

        if ($validate) {
            $keys    = array_keys($row);
            $headers = array_map(
                function($value) { return str_replace('"', '', $value);}, explode(',', trim(fgets(fopen($file, 'r'))))
            );

            if ($keys != $headers) {
                throw new Exception("Error: The new data's columns do not match the CSV files columns.");
            }
        }

        $options = self::processOptions($options);
        $csvRow  = self::serializeRow(
            (array)$row, [], $options['delimiter'], $options['enclosure'],
            $options['escape'], $options['newline'], $options['limit']
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
    public static function serializeData($data, array $options = [])
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

        if (isset($options['omit'])) {
            $omit = (!is_array($options['omit'])) ? [$options['omit']] : $options['omit'];
        } else {
            $omit = [];
        }

        $options = self::processOptions($options);
        $csv     = '';

        if (is_array($data) && isset($data[0]) &&
            (is_array($data[0]) || ($data[0] instanceof \ArrayObject)) && ($options['fields'])) {
            $csv .= self::getFieldHeaders((array)$data[0], $options['delimiter'], $omit);
        }

        // Initialize and clean the field values.
        foreach ($data as $value) {
            $csv .= self::serializeRow(
                (array)$value, $omit, $options['delimiter'], $options['enclosure'],
                $options['escape'], $options['newline'], $options['limit']
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
    public static function unserializeString($string, array $options = [])
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
     * Serialize single row of data;
     *
     * @param  array   $value
     * @param  array   $omit
     * @param  string  $delimiter
     * @param  string  $enclosure
     * @param  string  $escape
     * @param  boolean $newline
     * @param  int     $limit
     * @return string
     */
    public static function serializeRow(
        array $value, array $omit = [], $delimiter = ',', $enclosure = '"', $escape = '"', $newline = true, $limit = 0
    )
    {
        $rowAry = [];
        foreach ($value as $key => $val) {
            if (!in_array($key, $omit)) {
                if (!$newline) {
                    $val = str_replace(["\n", "\r"], [" ", " "], $val);
                }
                if ((int)$limit > 0) {
                    $val = substr($val, 0, (int)$limit);
                }
                if (strpos($val, $enclosure) !== false) {
                    $val = str_replace($enclosure, $escape . $enclosure, $val);
                }
                if ((strpos($val, $delimiter) !== false) || (strpos($val, "\n") !== false) ||
                    (strpos($val, $escape . $enclosure) !== false)) {
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
     * @param  array  $omit
     * @return string
     */
    public static function getFieldHeaders($data, $delimiter = ',', array $omit = [])
    {
        $headers    = array_keys($data);
        $headersAry = [];
        foreach ($headers as $header) {
            if (!in_array($header, $omit)) {
                $headersAry[] = $header;
            }
        }
        return implode($delimiter, $headersAry) . PHP_EOL;
    }

    /**
     * Determine if the string is valid CSV
     *
     * @param  string $string
     * @return boolean
     */
    public static function isValid($string)
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
    public function __toString()
    {
        // Attempt to serialize data if it hasn't been done yet
        if ((null === $this->string) && (null !== $this->data)) {
            $this->serialize();
        }

        return $this->string;
    }

}
