<?php

namespace Pop\Log\Test;

use Pop\Log\Logger;
use Pop\Log\Writer;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{

    public function testConstructor()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->assertInstanceOf('Pop\Log\Logger', $logger);
        $this->assertEquals(1, count($logger->getWriters()));
        $this->assertFileExists(__DIR__ . '/tmp/test.log');
    }

    public function testAddWriters()
    {
        $db     = \Pop\Db\Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $logger = new Logger();
        $logger->addWriters([
            new Writer\File(__DIR__ . '/tmp/test.log'),
            new Writer\Db($db, 'logs')
        ]);
        $this->assertEquals(2, count($logger->getWriters()));
    }

    public function testSetTimestamp()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->setTimestampFormat('m/d/Y');
        $this->assertEquals('m/d/Y', $logger->getTimestampFormat());
    }

    public function testGetLevel()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->assertEquals('EMERGENCY', $logger->getLevel(Logger::EMERGENCY));
    }

    public function testLog()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->log(Logger::ALERT, 'This is an alert.')
               ->emergency('This is an emergency.')
               ->alert('This is an alert #2.')
               ->critical('This is a critical warning.')
               ->error('This is an error.')
               ->warning('This is a warning.')
               ->notice('This is a notice.')
               ->info('This is an info.')
               ->debug('This is a debug.');

        $log = file_get_contents(__DIR__ . '/tmp/test.log');

        $this->assertContains('This is an alert', $log);
        $this->assertContains('This is an emergency.', $log);
        $this->assertContains('This is an alert #2.', $log);
        $this->assertContains('This is a critical warning.', $log);
        $this->assertContains('This is an error.', $log);
        $this->assertContains('This is a warning.', $log);
        $this->assertContains('This is a notice.', $log);
        $this->assertContains('This is an info.', $log);
        $this->assertContains('This is a debug.', $log);

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testTextContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => 'bar']);
        $this->assertContains('foo=bar;', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testJsonContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => 'bar', 'format' => 'json']);
        $this->assertContains('{', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testArrayAndObjectContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => [123], 'bar' => new \StdClass()]);
        $this->assertContains('foo=[Array]', file_get_contents(__DIR__ . '/tmp/test.log'));
        $this->assertContains('bar=[Object]', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

}
