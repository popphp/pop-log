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
        $this->assertInstanceOf('Pop\Mail\Mailer', $writer->getMailer());
        $this->assertIsArray($writer->getEmails());
        $this->assertIsArray($writer->getOptions());
    }

    public function testConstructor2()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), ['nobody1@localhost', 'nobody2@localhost'] );
        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testAddOptions()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Sendmail()), 'nobody@localhost');
        $writer->addOptions([
            'subject' => 'Subject',
            'cc'      => 'nobody2@localhost'
        ]);
        $this->assertCount(2, $writer->getOptions());
    }

    public function testLog()
    {
        $writer = new Writer\Mail(new Mail\Mailer(new Mail\Transport\Mailgun(['api_url' => 'http://localhost/', 'api_key' => 'API_KEY'])), 'nobody@localhost', [
            'headers' => [
                'Reply-To' => 'noreply@localhost'
            ]
        ]);

        $writer->writeLog(\Psr\Log\LogLevel::NOTICE, 'This is a mail test.', [
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

        $writer->writeLog(\Psr\Log\LogLevel::NOTICE, 'This is a mail test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertInstanceOf('Pop\Log\Writer\Mail', $writer);
    }

    public function testWriteLogSanitizesSubjectAndBody()
    {
        $transport = new class implements \Pop\Mail\Transport\TransportInterface {
            public ?\Pop\Mail\Message $captured = null;
            public function send(\Pop\Mail\Message $message): mixed
            {
                $this->captured = $message;
                return null;
            }
        };

        $writer = new Writer\Mail(new Mail\Mailer($transport), 'nobody@localhost');
        $writer->writeLog(\Psr\Log\LogLevel::NOTICE, "Injected\r\nsecond line", [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => "Bad\r\nName"
        ]);

        $this->assertNotNull($transport->captured);

        $subject = $transport->captured->getSubject();
        $this->assertStringNotContainsString("\r", $subject);
        $this->assertStringNotContainsString("\n", $subject);
        $this->assertStringContainsString('Bad  Name', $subject);

        $body = rtrim($transport->captured->getParts()[0]->getBody()->getContent(), "\r\n");
        $this->assertStringNotContainsString("\r", $body);
        $this->assertStringNotContainsString("\n", $body);
        $this->assertStringContainsString('Bad  Name', $body);
        $this->assertStringContainsString('Injected  second line', $body);
    }

    public function testWriteLogSanitizesContextValue()
    {
        $transport = new class implements \Pop\Mail\Transport\TransportInterface {
            public ?\Pop\Mail\Message $captured = null;
            public function send(\Pop\Mail\Message $message): mixed
            {
                $this->captured = $message;
                return null;
            }
        };

        $writer = new Writer\Mail(new Mail\Mailer($transport), 'nobody@localhost');
        $writer->writeLog(\Psr\Log\LogLevel::NOTICE, 'Login failed', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'username'  => "bob\r\nADMIN GRANTED ROOT;"
        ]);

        $this->assertNotNull($transport->captured);

        $body = rtrim($transport->captured->getParts()[0]->getBody()->getContent(), "\r\n");
        $this->assertStringNotContainsString("\r", $body);
        $this->assertStringNotContainsString("\n", $body);
    }

}
