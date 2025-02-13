<?php

namespace Pop\Csv\Test;

use Pop\Csv\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{

    public function testLoadFile()
    {
        $csv = Csv::loadFile(__DIR__ . '/tmp/data.csv');
        $this->assertEquals('testuser1', $csv->getData()[0]['username']);
    }

    public function testLoadString()
    {
        $csv = Csv::loadString(file_get_contents(__DIR__ . '/tmp/data.csv'));
        $this->assertEquals('testuser1', $csv->getData()[0]['username']);
    }

    public function testLoadData()
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
        $csv = Csv::loadData($data);
        $this->assertInstanceOf('Pop\Csv\Csv', $csv);
    }

    public function testGetDataFromFile()
    {
        $data = Csv::getDataFromFile(__DIR__ . '/tmp/data.csv');
        $this->assertEquals('testuser1', $data[0]['username']);
    }

    public function testWriteDataToFile()
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

        Csv::writeDataToFile($data, __DIR__ . '/tmp/test.csv');
        $this->assertFileExists(__DIR__ . '/tmp/test.csv');

        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
    }

    public function testWriteTemplateToFile()
    {
        $data = [
            [
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

        Csv::writeTemplateToFile($data, __DIR__ . '/tmp/template.csv');
        $this->assertFileExists(__DIR__ . '/tmp/template.csv');

        if (file_exists(__DIR__ . '/tmp/template.csv')) {
            unlink(__DIR__ . '/tmp/template.csv');
        }
    }

    public function testSetters()
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
        $csv = new Csv();
        $csv->setString(file_get_contents(__DIR__ . '/tmp/data.csv'));
        $csv->setData($data);
        $this->assertStringContainsString('testuser1', $csv->getString());
        $this->assertEquals('Bob', $csv->getData()[0]['first_name']);
    }

    public function testGetters()
    {
        $csv = new Csv(__DIR__ . '/tmp/data.csv');
        $csv->unserialize();
        $this->assertEquals('testuser1', $csv->getData()[0]['username']);
        $this->assertStringContainsString('testuser1', $csv->getString());
        $this->assertTrue($csv->isSerialized());
        $this->assertTrue($csv->isUnserialized());
    }

    public function testUnserializeAndSerialize()
    {
        $data1 = new Csv(__DIR__ . '/tmp/data.csv');
        $data2 = new Csv(file_get_contents(__DIR__ . '/tmp/data.csv'));
        $data1Ary = $data1->unserialize();
        $data2Ary = $data2->unserialize();
        $this->assertEquals('testuser1', $data1Ary[0]['username']);
        $this->assertEquals('testuser1', $data2Ary[0]['username']);
        $string = new Csv($data1Ary);
        $this->assertStringContainsString('testuser1,testuser1@test.com', $string->serialize());
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
        $this->assertStringContainsString('Bob,"Smith, III"', (string)$string);
    }

    public function testExclude()
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
        $string = new Csv($data, ['exclude' => 'first_name']);
        $csvString = $string->serialize();
        $this->assertStringNotContainsString('Bob', $csvString);
    }

    public function testInclude()
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
        $string = new Csv($data, ['include' => 'first_name']);
        $csvString = $string->serialize();
        $this->assertStringContainsString('Bob', $csvString);
    }

    public function testMap()
    {
        $data = [
            [
                'username' => 'bobsmith',
                'country'  => ['id' => 1, 'code' => 'US'],
            ],
            [
                'username' => 'janesmith',
                'country'  => ['id' => 2, 'code' => 'FR'],
            ]
        ];
        $string = new Csv($data);
        $csvString = $string->serialize(['map' => ['country' => 'code']]);
        $this->assertStringContainsString('username,country', $csvString);
        $this->assertStringContainsString('bobsmith,US', $csvString);
        $this->assertStringContainsString('janesmith,FR', $csvString);
    }

    public function testColumns()
    {
        $data = [
            [
                'username' => 'bobsmith',
                'roles'    => [['id' => 1, 'role' => 'Admin'], ['id' => 2, 'role' => 'Editor']],
            ],
            [
                'username' => 'janesmith',
                'roles'    => [['id' => 2, 'role' => 'Editor'], ['id' => 3, 'role' => 'Staff']],
            ]
        ];
        $string = new Csv($data, ['columns' => ['roles' => 'role']]);
        $csvString = $string->serialize();
        $this->assertStringContainsString('username,roles', $csvString);
        $this->assertStringContainsString('bobsmith,"Admin,Editor"', $csvString);
        $this->assertStringContainsString('janesmith,"Editor,Staff"', $csvString);
    }

    public function testNewline()
    {
        $data = [
            [
                'first_name' => 'Bob',
                'last_name'  => 'Smith',
                'notes'      => "Hello What's up?\nHow are you doing?\nI'm doing fine!"
            ]
        ];
        $string    = new Csv($data, ['newline' => false]);
        $csvString = $string->serialize();

        $data = new Csv($csvString);
        $value = $data->unserialize(['map' => ['country' => 'code']]);
        $this->assertEquals(1, count($value));
        $this->assertEquals("Hello What's up? How are you doing? I'm doing fine!", $value[0]['notes']);
    }

    public function testLimit()
    {
        $data = [
            [
                'first_name' => 'Bob',
                'last_name'  => 'Smith'
            ]
        ];
        $string    = new Csv($data, ['limit' => 2]);
        $csvString = $string->serialize();

        $data = new Csv($csvString);
        $value = $data->unserialize();
        $this->assertEquals(1, count($value));
        $this->assertEquals('Bo', $value[0]['first_name']);
        $this->assertEquals('Sm', $value[0]['last_name']);
    }

    public function testIsValid()
    {
        $this->assertTrue(Csv::isValid(file_get_contents(__DIR__ . '/tmp/data.csv')));
    }

    public function testWriteToFile()
    {
        $data = [
            [
                'first_name' => 'Bob',
                'last_name'  => 'Smith'
            ],
            [
                'first_name' => 'Jane',
                'last_name'  => 'Smith'
            ]
        ];
        $string = new Csv($data);
        $string->writeToFile(__DIR__ . '/tmp/test.csv');
        $this->assertFileExists(__DIR__ . '/tmp/test.csv');
    }

    public function testAppendDataToFileExistsException()
    {
        $this->expectException('Pop\Csv\Exception');
        $data = [
            [
                'first_name' => 'John'
            ]
        ];

        Csv::appendDataToFile(__DIR__ . '/tmp/bad.csv', $data);
    }


    public function testAppendDataToFileHeadersException()
    {
        $this->expectException('Pop\Csv\Exception');
        $data = [
            [
                'first_name' => 'John'
            ]
        ];

        Csv::appendDataToFile(__DIR__ . '/tmp/test.csv', $data);
    }

    public function testAppendToFileExistsException()
    {
        $this->expectException('Pop\Csv\Exception');
        $data = [
            [
                'first_name' => 'John',
                'last_name'  => 'Smith'
            ]
        ];

        Csv::appendRowToFile(__DIR__ . '/tmp/bad.csv', $data);
    }

    public function testAppend()
    {
        $data = [
            [
                'first_name' => 'John',
                'last_name'  => 'Smith'
            ]
        ];

        $csv = new Csv();
        $csv->appendData(__DIR__ . '/tmp/test.csv', $data);
        $csv = Csv::loadFile(__DIR__ . '/tmp/test.csv');
        $this->assertEquals(3, count($csv->getData()));

    }

    public function testAppendRow()
    {
        $data = [
            'first_name' => 'John',
            'last_name'  => 'Smith'
        ];

        $csv = new Csv();
        $csv->appendRow(__DIR__ . '/tmp/test.csv', $data);
        $csv = Csv::loadFile(__DIR__ . '/tmp/test.csv');
        $this->assertEquals(4, count($csv->getData()));

    }

    public function testAppendToFile()
    {
        $data = [
            [
                'first_name' => 'John',
                'last_name'  => 'Smith'
            ]
        ];

        Csv::appendDataToFile(__DIR__ . '/tmp/test.csv', $data);
        $csv = Csv::loadFile(__DIR__ . '/tmp/test.csv');
        $this->assertEquals(5, count($csv->getData()));

        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
    }

    public function testWriteBlankFileException()
    {
        $this->expectException('Pop\Csv\Exception');
        $csv = new Csv();
        $csv->writeBlankFile('test.csv');
    }

    public function testOutputBlankFileException()
    {
        $this->expectException('Pop\Csv\Exception');
        $csv = new Csv();
        $csv->outputBlankFileToHttp();
    }

    #[runInSeparateProcess]
    public function testOutputToHttp()
    {
        $data = new Csv([
            ['foo' => 'bar']
        ]);
        ob_start();
        $data->outputToHttp('test.csv', false);
        $result = ob_get_clean();
        $this->assertStringContainsString('foo', $result);
    }

    #[runInSeparateProcess]
    public function testOutputToHttpData()
    {
        ob_start();
        Csv::outputDataToHttp([['foo' => 'bar']], [], 'test.csv', false);
        $result = ob_get_clean();
        $this->assertStringContainsString('foo', $result);
    }

    #[runInSeparateProcess]
    public function testOutputTemplateToHttp()
    {
        ob_start();
        Csv::outputTemplateToHttp([['foo' => 'bar']], 'template.csv', false);
        $result = ob_get_clean();
        $this->assertStringContainsString('foo', $result);
    }

}
