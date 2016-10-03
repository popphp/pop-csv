<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @package    Pop_Data
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Csv
{

    /**
     * Parse the CSV string into a PHP array
     *
     * @param  string $string
     * @param  array  $options
     * @return array
     */
    public static function unserialize($string, array $options = [])
    {
        $delimiter = (isset($options['delimiter'])) ? $options['delimiter']    : ',';
        $enclosure = (isset($options['enclosure'])) ? $options['enclosure']    : '"';
        $escape    = (isset($options['escape']))    ? $options['escape']       : "\\";
        $fields    = (isset($options['fields']))    ? (bool)$options['fields'] : true;
        $lines     = preg_split("/((\r?\n)|(\r\n?))/", $string);
        $data      = [];
        $fieldKeys = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (!empty($line)) {
                if (($i == 0) && ($fields)) {
                    $fieldNames = str_getcsv($line, $delimiter, $enclosure, $escape);
                    foreach ($fieldNames as $name) {
                        $fieldKeys[] = trim($name);
                    }
                } else {
                    $values = str_getcsv($line, $delimiter, $enclosure, $escape);
                    foreach ($values as $key => $value) {
                        $values[$key] = stripslashes(trim($value));
                    }
                    if ((count($fieldKeys) > 0) && (count($fieldKeys) == count($values))) {
                        $data[] = array_combine($fieldKeys, $values);
                    } else {
                        $data[] = $values;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Convert the data into CSV format.
     *
     * @param  mixed $data
     * @param  array $options
     * @return string
     */
    public static function serialize($data, array $options = [])
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
        $delimiter = (isset($options['delimiter'])) ? $options['delimiter']    : ',';
        $enclosure = (isset($options['enclosure'])) ? $options['enclosure']    : '"';
        $escape    = (isset($options['escape']))    ? $options['escape']       : "\\";
        $fields    = (isset($options['fields']))    ? (bool)$options['fields'] : true;
        $csv       = '';

        if (is_array($data) && isset($data[0]) && (is_array($data[0]) || ($data[0] instanceof \ArrayObject)) && ($fields)) {
            $csv .= self::getFieldHeaders((array)$data[0], $delimiter, $omit);
        }

        // Initialize and clean the field values.
        foreach ($data as $value) {
            $csv .= self::serializeRow((array)$value, $omit, $delimiter, $enclosure, $escape);
        }

        return $csv;
    }

    /**
     * Serialize single row of data;
     *
     * @param  array  $value
     * @param  array  $omit
     * @param  string $delimiter
     * @param  string $enclosure
     * @param  string $escape
     * @return string
     */
    public static function serializeRow(array $value, array $omit = [], $delimiter = ',', $enclosure = '"', $escape = "\\")
    {
        $rowAry = [];
        foreach ($value as $key => $val) {
            if (!in_array($key, $omit)) {
                $val = str_replace(["\n", "\r"], [" ", " "], $val);
                if (strpos($val, $delimiter) !== false) {
                    if (strpos($val, $enclosure) !== false) {
                        $val = str_replace($enclosure, $escape . $enclosure, $val);
                    }
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

}
