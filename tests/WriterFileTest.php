<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\File;

class WriterFileTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $writer = new File(__DIR__ . '/tmp/test.log');
        $this->assertInstanceOf('Pop\Log\Writer\File', $writer);
        $this->assertFileExists(__DIR__ . '/tmp/test.log');
        unlink(__DIR__ . '/tmp/test.log');
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
        $writer = new File(__DIR__ . '/tmp/test.log', [
            'csv' => 'text/csv',
            'txt' => 'text/plain'
        ]);
    }

    public function testCsv()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.csv');
        $writer->writeLog(5, 'This is a CSV test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.csv');
        $this->assertContains('This is a CSV test.', file_get_contents(__DIR__ . '/tmp/test.csv'));
        unlink(__DIR__ . '/tmp/test.csv');
    }

    public function testTsv()
    {
        $writer = new File(__DIR__ . '/tmp/test.tsv');
        $writer->writeLog(5, 'This is a TSV test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.tsv');
        $this->assertContains('This is a TSV test.', file_get_contents(__DIR__ . '/tmp/test.tsv'));
        unlink(__DIR__ . '/tmp/test.tsv');
    }

    public function testXml()
    {
        $writer = new File(__DIR__ . '/tmp/test.xml');
        $writer->writeLog(5, 'This is an XML test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.xml');
        $this->assertContains('This is an XML test.', file_get_contents(__DIR__ . '/tmp/test.xml'));
        unlink(__DIR__ . '/tmp/test.xml');
    }

}
