<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\Http;
use Pop\Http\Client;
use PHPUnit\Framework\TestCase;

class WriterHttpTest extends TestCase
{

    public function testConstructor()
    {
        $writer = new Http(new Client('http://localhost/', ['method' => 'POST']));
        $this->assertInstanceOf('Pop\Log\Writer\Http', $writer);
        $this->assertInstanceOf('Pop\Http\Client', $writer->getClient());
    }

    public function testSend()
    {
        $writer = new Http(new Client('http://localhost/', ['method' => 'POST']));
        $writer->writeLog(3, 'Something went wrong.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'ERROR'
        ]);
        $this->assertInstanceOf('Pop\Log\Writer\Http', $writer);
    }

}
