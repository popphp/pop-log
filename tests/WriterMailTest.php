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

    public function testSend()
    {
        $writer = new Mail('nobody@localhost', [
            'headers' => [
                'Reply-To' => 'noreply@localhost'
            ]
        ]);
        $writer->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'priority'  => 5,
            'name'      => 'NOTICE',
            'message'   => 'This is a mail test.'
        ]);
    }

}
