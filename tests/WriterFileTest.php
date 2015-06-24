<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\File;

class WriterFileTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $writer = new File(__DIR__ . '/test.log');
        $this->assertInstanceOf('Pop\Log\Writer\File', $writer);
        $this->assertFileExists(__DIR__ . '/test.log');
        unlink(__DIR__ . '/test.log');
    }

    public function testConstructorSetAllowedTypes()
    {
        $writer = new File(__DIR__ . '/test.txt', [
            'csv' => 'text/csv',
            'txt' => 'text/plain'
        ]);
        $this->assertInstanceOf('Pop\Log\Writer\File', $writer);
        $this->assertFileExists(__DIR__ . '/test.txt');
        unlink(__DIR__ . '/test.txt');
    }

    public function testConstructorTypeException()
    {
        $this->setExpectedException('Pop\Log\Writer\Exception');
        $writer = new File(__DIR__ . '/test.log', [
            'csv' => 'text/csv',
            'txt' => 'text/plain'
        ]);
    }

    public function testCsv()
    {
        if (file_exists(__DIR__ . '/test.log')) {
            unlink(__DIR__ . '/test.log');
        }
        $writer = new File(__DIR__ . '/test.csv');
        $writer->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'priority'  => 5,
            'name'      => 'NOTICE',
            'message'   => 'This is a CSV test.'
        ]);

        $this->assertFileExists(__DIR__ . '/test.csv');
        $this->assertContains('This is a CSV test.', file_get_contents(__DIR__ . '/test.csv'));
        unlink(__DIR__ . '/test.csv');
    }

    public function testTsv()
    {
        $writer = new File(__DIR__ . '/test.tsv');
        $writer->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'priority'  => 5,
            'name'      => 'NOTICE',
            'message'   => 'This is a TSV test.'
        ]);

        $this->assertFileExists(__DIR__ . '/test.tsv');
        $this->assertContains('This is a TSV test.', file_get_contents(__DIR__ . '/test.tsv'));
        unlink(__DIR__ . '/test.tsv');
    }

    public function testXml()
    {
        $writer = new File(__DIR__ . '/test.xml');
        $writer->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'priority'  => 5,
            'name'      => 'NOTICE',
            'message'   => 'This is an XML test.'
        ]);

        $this->assertFileExists(__DIR__ . '/test.xml');
        $this->assertContains('This is an XML test.', file_get_contents(__DIR__ . '/test.xml'));
        unlink(__DIR__ . '/test.xml');
    }

}
