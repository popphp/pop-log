<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\Stream;
use Pop\Log\Writer\Exception;
use Pop\Log\Formatter\Line;
use Pop\Log\Formatter\NdJson;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;

class WriterStreamTest extends TestCase
{

    public function testConstructorWithStringOpensAndOwnsStream()
    {
        $writer = new Stream('php://memory');
        $this->assertInstanceOf(Stream::class, $writer);
        $this->assertTrue(is_resource($writer->getStream()));
    }

    public function testConstructorWithResourceDoesNotOwnStream()
    {
        $resource = fopen('php://memory', 'a');
        $writer   = new Stream($resource);
        $this->assertSame($resource, $writer->getStream());
        unset($writer);
        $this->assertTrue(is_resource($resource));
        fclose($resource);
    }

    public function testConstructorWithStringClosesOwnedStreamOnDestruct()
    {
        $writer   = new Stream('php://memory');
        $resource = $writer->getStream();
        unset($writer);
        $this->assertFalse(is_resource($resource));
    }

    public function testConstructorWithInvalidInputThrowsException()
    {
        $this->expectException(Exception::class);
        new Stream(12345);
    }

    public function testConstructorWithUnopenableStringThrowsException()
    {
        $this->expectException(Exception::class);
        new Stream('/nonexistent/directory/that/does/not/exist/file.log');
    }

    public function testConstructorWithNonStreamResourceThrowsException()
    {
        $context = stream_context_create();
        $this->expectException(Exception::class);
        new Stream($context);
    }

    public function testWriteLogAfterCallerClosesResourceThrowsException()
    {
        $resource = fopen('php://memory', 'a');
        $writer   = new Stream($resource);
        fclose($resource);

        $this->expectException(Exception::class);
        $writer->writeLog(LogLevel::NOTICE, 'This should fail.', [
            'timestamp' => '2026-08-10 12:00:00',
            'name'      => 'NOTICE'
        ]);
    }

    public function testWriteLogToReadOnlyStreamThrowsException()
    {
        $resource = fopen(__FILE__, 'r');
        $writer   = new Stream($resource);

        $this->expectException(Exception::class);
        $writer->writeLog(LogLevel::NOTICE, 'This should fail.', [
            'timestamp' => '2026-08-10 12:00:00',
            'name'      => 'NOTICE'
        ]);

        fclose($resource);
    }

    public function testDefaultFormatterIsNdJson()
    {
        $writer = new Stream('php://memory');
        $this->assertInstanceOf(NdJson::class, $writer->getFormatter());
    }

    public function testExplicitFormatterOverridesDefault()
    {
        $writer = new Stream('php://memory', new Line());
        $this->assertInstanceOf(Line::class, $writer->getFormatter());
    }

    public function testWriteLogWithDefaultNdJsonFormatter()
    {
        $writer = new Stream('php://memory');
        $writer->writeLog(LogLevel::NOTICE, 'This is a stream test.', [
            'timestamp' => '2026-08-10 12:00:00',
            'name'      => 'NOTICE'
        ]);

        $resource = $writer->getStream();
        rewind($resource);
        $contents = stream_get_contents($resource);

        $decoded = json_decode(trim($contents), true);
        $this->assertEquals('notice', $decoded['level']);
        $this->assertEquals('This is a stream test.', $decoded['message']);
        $this->assertEquals('NOTICE', $decoded['name']);
    }

    public function testWriteLogWithExplicitLineFormatter()
    {
        $writer = new Stream('php://memory', new Line());
        $writer->writeLog(LogLevel::NOTICE, 'This is a line test.', [
            'timestamp' => '2026-08-10 12:00:00',
            'name'      => 'NOTICE'
        ]);

        $resource = $writer->getStream();
        rewind($resource);
        $contents = stream_get_contents($resource);

        $this->assertStringContainsString('This is a line test.', $contents);
        $this->assertStringContainsString("\tnotice\t", $contents);
    }

    public function testWriteLogRespectsLogLimit()
    {
        $writer = new Stream('php://memory');
        $writer->setLogLimit(LogLevel::ERROR);
        $writer->writeLog(LogLevel::DEBUG, 'This should not be written.', [
            'timestamp' => '2026-08-10 12:00:00',
            'name'      => 'DEBUG'
        ]);

        $resource = $writer->getStream();
        rewind($resource);
        $contents = stream_get_contents($resource);

        $this->assertEquals('', $contents);
    }

    public function testStdoutFactoryPointsAtPhpStdout()
    {
        $writer = Stream::stdout();
        $this->assertInstanceOf(Stream::class, $writer);
        $meta = stream_get_meta_data($writer->getStream());
        $this->assertEquals('php://stdout', $meta['uri']);
    }

    public function testStderrFactoryPointsAtPhpStderr()
    {
        $writer = Stream::stderr();
        $this->assertInstanceOf(Stream::class, $writer);
        $meta = stream_get_meta_data($writer->getStream());
        $this->assertEquals('php://stderr', $meta['uri']);
    }

    public function testStdoutFactoryAcceptsExplicitFormatter()
    {
        $writer = Stream::stdout(new Line());
        $this->assertInstanceOf(Line::class, $writer->getFormatter());
    }
}
