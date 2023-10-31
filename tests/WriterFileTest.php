<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\File;
use PHPUnit\Framework\TestCase;

class WriterFileTest extends TestCase
{

    public function testConstructor()
    {
        $writer = new File(__DIR__ . '/tmp/test.log');
        $this->assertInstanceOf('Pop\Log\Writer\File', $writer);
        $this->assertFileExists(__DIR__ . '/tmp/test.log');
        $this->assertEquals(__DIR__ . '/tmp/test.log', $writer->getFile());
        $this->assertEquals('log', $writer->getType());
        unlink(__DIR__ . '/tmp/test.log');
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
        $this->assertStringContainsString('This is a CSV test.', file_get_contents(__DIR__ . '/tmp/test.csv'));
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
        $this->assertStringContainsString('This is a TSV test.', file_get_contents(__DIR__ . '/tmp/test.tsv'));
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
        $this->assertStringContainsString('This is an XML test.', file_get_contents(__DIR__ . '/tmp/test.xml'));
        unlink(__DIR__ . '/tmp/test.xml');
    }

    public function testXmlWithContext()
    {
        $writer = new File(__DIR__ . '/tmp/test.xml');
        $writer->writeLog(5, 'This is an XML test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'foo' => 'bar'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.xml');
        $this->assertStringContainsString('This is an XML test.', file_get_contents(__DIR__ . '/tmp/test.xml'));
        $this->assertStringContainsString('foo=bar;', file_get_contents(__DIR__ . '/tmp/test.xml'));
        unlink(__DIR__ . '/tmp/test.xml');
    }

    public function testJson()
    {
        $writer = new File(__DIR__ . '/tmp/test.json');
        $writer->writeLog(5, 'This is an JSON test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.json');
        $this->assertStringContainsString('This is an JSON test.', file_get_contents(__DIR__ . '/tmp/test.json'));
        unlink(__DIR__ . '/tmp/test.json');
    }

    public function testSetLogLimitException()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $this->expectException('InvalidArgumentException');
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(8);
    }

    public function testIsWithinLogLimitException()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $this->expectException('InvalidArgumentException');
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(1);
        $this->assertFalse($writer->isWithinLogLimit(8));
    }

    public function testLogLimit1()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(1);
        $this->assertTrue($writer->hasLogLimit());
        $this->assertEquals(1, $writer->getLogLimit());
        $this->assertTrue($writer->isWithinLogLimit(1));
        $this->assertFalse($writer->isWithinLogLimit(3));
        unlink(__DIR__ . '/tmp/test.log');
    }

}
