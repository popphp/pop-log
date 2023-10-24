<?php

namespace Pop\Log\Test;

use Pop\Log\Writer;
use Pop\Mail;
use PHPUnit\Framework\TestCase;

class WriterMailTest extends TestCase
{

    public function testConstructor1()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), 'nobody@localhost');
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testConstructor2()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), ['nobody1@localhost', 'nobody2@localhost'] );
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testLog()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Mailgun(['api_url' => 'http://localhost/', 'api_key' => 'API_KEY'])), 'nobody@localhost', [
            'headers' => [
                'Reply-To' => 'noreply@localhost'
            ]
        ]);

        $writer->writeLog(5, 'This is a mail test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testLogWithOptions()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Mailgun(['api_url' => 'http://localhost/', 'api_key' => 'API_KEY'])), 'nobody@localhost', [
            'headers' => [
                'Reply-To'    => 'noreply@localhost',
                'CC'          => 'cc@localhost',
                'BCC'         => 'bcc@localhost',
                'From'        => 'from@localhost',
                'Sender'      => 'sender@localhost',
                'Return-Path' => 'return-path@localhost',
                'X-Priority'  => 1
            ]
        ]);

        $writer->writeLog(5, 'This is a mail test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

}
