<?php

namespace Pop\Log\Test;

use Pop\Log\Writer;
use Pop\Mail;

class WriterMailTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), 'nobody@localhost');
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testLog()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), 'nobody@localhost', [
            'headers' => [
                'Reply-To' => 'noreply@localhost'
            ]
        ]);

        $writer->writeLog(5, 'This is a mail test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);
    }

}
