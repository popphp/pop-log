<?php

namespace Pop\Log\Test;

use Pop\Log\Facility;
use Pop\Log\Writer\Syslog;
use Pop\Log\Writer\Exception;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;

class WriterSyslogTest extends TestCase
{

    protected $server;
    protected int $port;

    protected function setUp(): void
    {
        $this->server = stream_socket_server('udp://127.0.0.1:0', $errno, $errstr, STREAM_SERVER_BIND);
        stream_set_blocking($this->server, false);
        $name = stream_socket_get_name($this->server, false);
        $this->port = (int)substr($name, strrpos($name, ':') + 1);
    }

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            fclose($this->server);
        }
    }

    protected function readPacket(int $length = 2048): string
    {
        $packet   = '';
        $attempts = 0;
        while ($packet === '' && $attempts < 20) {
            $packet = (string)fread($this->server, $length);
            if ($packet === '') {
                usleep(5000);
            }
            $attempts++;
        }
        return $packet;
    }

    public function testConstructor()
    {
        $writer = new Syslog(Facility::LOCAL0, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $this->assertInstanceOf(Syslog::class, $writer);
        $this->assertEquals(Facility::LOCAL0, $writer->getFacility());
        $this->assertEquals('test-app', $writer->getTag());
        $this->assertEquals('test-host', $writer->getHostname());
    }

    public function testConstructorThrowsOnUnresolvableHost()
    {
        $this->expectException(Exception::class);
        new Syslog(Facility::USER, 'this-host-does-not-resolve.invalid', 514);
    }

    public function testWriteLogBuildsRfc3164Packet()
    {
        $writer = new Syslog(Facility::LOCAL0, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $writer->writeLog(LogLevel::ERROR, 'Something broke');

        $packet = $this->readPacket();

        // PRI = facility(LOCAL0=16) * 8 + severity(error=3) = 131
        $this->assertStringStartsWith('<131>', $packet);
        $this->assertStringContainsString('test-host test-app: Something broke', $packet);
        // RFC-3164 HEADER timestamp shape: "Mmm dd hh:mm:ss" (space-padded day)
        $this->assertMatchesRegularExpression('/<131>[A-Z][a-z]{2} [ 0-9][0-9] \d{2}:\d{2}:\d{2} /', $packet);
    }

    public function testWriteLogIncludesPidWhenEnabled()
    {
        $writer = new Syslog(Facility::USER, '127.0.0.1', $this->port, 'test-app', 'test-host', true);
        $writer->writeLog(LogLevel::INFO, 'A message');

        $packet = $this->readPacket();
        $this->assertStringContainsString('test-app[' . getmypid() . ']: A message', $packet);
    }

    public function testWriteLogRespectsLogLimit()
    {
        $writer = new Syslog(Facility::USER, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $writer->setLogLimit(LogLevel::ERROR);
        $writer->writeLog(LogLevel::NOTICE, 'Should not send');

        $packet = $this->readPacket();
        $this->assertEmpty($packet);
    }

    public function testWriteLogTruncatesOversizedPacket()
    {
        $writer = new Syslog(Facility::USER, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $writer->writeLog(LogLevel::INFO, str_repeat('x', 2000));

        $packet = $this->readPacket();
        $this->assertLessThanOrEqual(1024, strlen($packet));
    }

    public function testWriteLogAppendsContext()
    {
        $writer = new Syslog(Facility::USER, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $writer->writeLog(LogLevel::INFO, 'A message', ['foo' => 'bar']);

        $packet = $this->readPacket();
        $this->assertStringContainsString('A message foo=bar;', $packet);
    }

    public function testWriteLogStripsCrLfFromMessage()
    {
        $writer = new Syslog(Facility::USER, '127.0.0.1', $this->port, 'test-app', 'test-host', false);
        $writer->writeLog(LogLevel::INFO, "Injected\r\nsecond line");

        $packet = $this->readPacket();

        // Strip the "<PRI>" prefix; the message body itself must be single-line, i.e. contain no raw CR/LF bytes
        $body = substr($packet, strpos($packet, '>') + 1);
        $this->assertStringNotContainsString("\n", $body);
        $this->assertStringNotContainsString("\r", $body);
        $this->assertStringContainsString('Injected  second line', $packet);
    }

    public function testConstructorStripsWhitespaceFromTagAndHostname()
    {
        $writer = new Syslog(Facility::LOCAL0, '127.0.0.1', $this->port, 'test app', 'test host', false);

        $this->assertEquals('testapp', $writer->getTag());
        $this->assertEquals('testhost', $writer->getHostname());
    }

}
