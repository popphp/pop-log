<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\Mail;

class WriterMailTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $writer = new Mail('nobody@localhost');
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testConstructor2()
    {
        $writer = new Mail([
            'Test Person' => 'nobody@localhost'
        ]);
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testLog()
    {
        $writer = new Mail('nobody@localhost', [
            'headers' => [
                'Reply-To' => 'noreply@localhost'
            ]
        ]);
        $writer->writeLog(5, 'This is a mail test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);
    }

    public function testCustomLog()
    {
        $writer = new Mail('nobody@localhost', ['headers' => ['From' => 'noreply@localhost']]);
        $writer->writeCustomLog('This is a custom log test.');
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

}
