<?php

namespace Pop\Log\Test;

use Pop\Log\Formatter\Csv;
use Pop\Log\Formatter\Tsv;
use Pop\Log\Formatter\Line;
use Pop\Log\Formatter\NdJson;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{

    protected array $context = [
        'timestamp' => '2026-01-01 00:00:00',
        'name'      => 'INFO',
        'foo'       => 'bar'
    ];

    public function testLineFormat()
    {
        $formatter = new Line();
        $result = $formatter->format('info', 'Hello', $this->context);
        $this->assertEquals("2026-01-01 00:00:00\tinfo\tINFO\tHello\tfoo=bar;", $result);
    }

    public function testCsvFormat()
    {
        $formatter = new Csv();
        $result = $formatter->format('info', 'Hello', $this->context);
        $this->assertEquals('2026-01-01 00:00:00,info,INFO,"Hello",foo=bar;', $result);
    }

    public function testCsvFormatEscapesQuotesInMessage()
    {
        $formatter = new Csv();
        $result = $formatter->format('info', 'He said "hi"', $this->context);
        $this->assertEquals('2026-01-01 00:00:00,info,INFO,"He said \"hi\"",foo=bar;', $result);
    }

    public function testTsvFormat()
    {
        $formatter = new Tsv();
        $result = $formatter->format('info', 'Hello', $this->context);
        $this->assertEquals("2026-01-01 00:00:00\tinfo\tINFO\t\"Hello\"\tfoo=bar;", $result);
    }

    public function testNdJsonFormat()
    {
        $formatter = new NdJson();
        $result = $formatter->format('info', 'Hello', $this->context);
        $decoded = json_decode($result, true);

        $this->assertEquals('2026-01-01 00:00:00', $decoded['timestamp']);
        $this->assertEquals('info', $decoded['level']);
        $this->assertEquals('INFO', $decoded['name']);
        $this->assertEquals('Hello', $decoded['message']);
        $this->assertEquals(['foo' => 'bar'], $decoded['context']);
    }

    public function testNdJsonFormatIsSingleLineValidJson()
    {
        $formatter = new NdJson();
        $result = $formatter->format('info', 'Hello', $this->context);
        $this->assertStringNotContainsString("\n", $result);
        $this->assertNotNull(json_decode($result));
    }

    public function testNdJsonFormatPreservesMultiLineMessage()
    {
        $formatter = new NdJson();
        $message   = "Exception: boom\r\n#0 /app/index.php(10): foo()\r\n#1 {main}";
        $result    = $formatter->format('error', $message, $this->context);

        $this->assertStringNotContainsString("\n", $result);
        $decoded = json_decode($result, true);
        $this->assertNotNull($decoded);
        $this->assertEquals($message, $decoded['message']);
    }

    public function testNdJsonFormatHandlesInvalidUtf8Context()
    {
        $formatter = new NdJson();
        $context   = $this->context + ['context_value' => "\xB1\x31"];
        $result    = $formatter->format('info', 'Hello', $context);

        $this->assertNotEquals('', $result);
        $this->assertNotFalse($result);
        $this->assertNotNull(json_decode($result));
    }

    public function testNdJsonFormatEmptyContextIsJsonObject()
    {
        $formatter = new NdJson();
        $context   = [
            'timestamp' => '2026-01-01 00:00:00',
            'name'      => 'INFO',
        ];
        $result = $formatter->format('info', 'Hello', $context);

        $this->assertStringContainsString('"context":{}', $result);
    }

    public function testLineFormatStillFlattensMultiLineMessage()
    {
        $formatter = new Line();
        $result = $formatter->format('info', "Hello\r\nWorld", $this->context);
        $this->assertEquals("2026-01-01 00:00:00\tinfo\tINFO\tHello  World\tfoo=bar;", $result);
    }

}
