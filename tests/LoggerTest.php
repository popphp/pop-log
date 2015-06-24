<?php

namespace Pop\Log\Test;

use Pop\Log\Logger;
use Pop\Log\Writer;

class LoggerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/test.log'));
        $this->assertInstanceOf('Pop\Log\Logger', $logger);
        $this->assertEquals(1, count($logger->getWriters()));
        $this->assertFileExists(__DIR__ . '/test.log');
    }

    public function testSetTimestamp()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/test.log'));
        $logger->setTimestamp('m/d/Y');
        $this->assertEquals('m/d/Y', $logger->getTimestamp());
    }

    public function testLog()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/test.log'));
        $logger->log(Logger::ALERT, 'This is an alert.')
               ->emerg('This is an emergency.')
               ->alert('This is an alert #2.')
               ->crit('This is a critical warning.')
               ->err('This is an error.')
               ->warn('This is a warning.')
               ->notice('This is a notice.')
               ->info('This is an info.')
               ->debug('This is a debug.');

        $log = file_get_contents(__DIR__ . '/test.log');

        $this->assertContains('This is an alert', $log);
        $this->assertContains('This is an emergency.', $log);
        $this->assertContains('This is an alert #2.', $log);
        $this->assertContains('This is a critical warning.', $log);
        $this->assertContains('This is an error.', $log);
        $this->assertContains('This is a warning.', $log);
        $this->assertContains('This is a notice.', $log);
        $this->assertContains('This is an info.', $log);
        $this->assertContains('This is a debug.', $log);

        unlink(__DIR__ . '/test.log');
    }

}
