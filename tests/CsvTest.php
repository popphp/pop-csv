<?php

namespace Pop\Data\Test;

use Pop\Csv\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{

    public function testUnserializeAndSerialize()
    {
        $data1 = new Csv(__DIR__ . '/tmp/data.csv');
        $data2 = new Csv(file_get_contents(__DIR__ . '/tmp/data.csv'));
        $data1Ary = $data1->unserialize();
        $data2Ary = $data2->unserialize();
        $this->assertEquals('testuser1', $data1Ary[0]['username']);
        $this->assertEquals('testuser1', $data2Ary[0]['username']);
        $string = new Csv($data1Ary);
        $this->assertContains('testuser1,testuser1@test.com', $string->serialize());
    }

    public function testSerializeWithAssociativeArray()
    {
        $data = [
            'my_table' => [
                [
                    'first_name' => 'Bob',
                    'last_name'  => 'Smith, III'
                ],
                [
                    'first_name' => 'Jane',
                    'last_name'  => 'Smith "Janey"'
                ],
                [
                    'first_name' => 'Jim',
                    'last_name'  => 'Smith, Jr. "Junior"'
                ]
            ]
        ];
        $string = new Csv($data);
        $this->assertContains('Bob,"Smith, III"', (string)$string);
    }

    public function testOmit()
    {
        $data = [
            [
                'first_name' => 'Bob',
                'last_name'  => 'Smith, III'
            ],
            [
                'first_name' => 'Jane',
                'last_name'  => 'Smith "Janey"'
            ],
            [
                'first_name' => 'Jim',
                'last_name'  => 'Smith, Jr. "Junior"'
            ]
        ];
        $string = new Csv($data);
        $csvString = $string->serialize(['omit' => 'first_name']);
        $this->assertNotContains('Bob', $csvString);
    }

    public function testIsValid()
    {
        $this->assertTrue(Csv::isValid(file_get_contents(__DIR__ . '/tmp/data.csv')));
    }

    public function testWriteToFile()
    {
        $data = [
            'my_table' => [
                [
                    'first_name' => 'Bob',
                    'last_name'  => 'Smith'
                ],
                [
                    'first_name' => 'Jane',
                    'last_name'  => 'Smith'
                ]
            ]
        ];
        $string = new Csv($data);
        $string->writeToFile(__DIR__ . '/tmp/test.csv');
        $this->assertFileExists(__DIR__ . '/tmp/test.csv');

        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testOutputToHttp()
    {
        $data = new Csv([
            ['foo' => 'bar']
        ]);
        ob_start();
        $data->outputToHttp('test.csv', false);
        $result = ob_get_clean();
        $this->assertContains('foo', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOutputToHttps()
    {
        $_SERVER['SERVER_PORT'] = 443;
        $data = new Csv([
            ['foo' => 'bar']
        ]);
        ob_start();
        $data->outputToHttp('test.csv', false);
        $result = ob_get_clean();
        $this->assertContains('foo', $result);
    }

}