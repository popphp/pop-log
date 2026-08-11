<?php

namespace Pop\Log\Test;

use Pop\Log\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{

    public function testSerializeStripsReservedKeys()
    {
        $result = Context::serialize([
            'timestamp' => '2026-01-01 00:00:00',
            'name'      => 'INFO',
            'foo'       => 'bar'
        ]);
        $this->assertEquals('foo=bar;', $result);
    }

    public function testSerializeDefaultTextFormat()
    {
        $result = Context::serialize(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertEquals('foo=bar;baz=qux;', $result);
    }

    public function testSerializeJsonFormat()
    {
        $result = Context::serialize(['foo' => 'bar', 'format' => 'json']);
        $this->assertEquals('{"foo":"bar"}', $result);
    }

    public function testSerializePhpFormat()
    {
        $result = Context::serialize(['foo' => 'bar', 'format' => 'php']);
        $this->assertEquals(serialize(['foo' => 'bar']), $result);
    }

    public function testSerializeArrayAndObjectCollapse()
    {
        $result = Context::serialize(['foo' => [123], 'bar' => new \StdClass()]);
        $this->assertStringContainsString('foo=[Array]', $result);
        $this->assertStringContainsString('bar=[Object]', $result);
    }

    public function testSanitizeStripsCrLf()
    {
        $this->assertEquals('a  b', Context::sanitize("a\r\nb"));
    }

    public function testSanitizeLeavesOrdinaryStringsUnchanged()
    {
        $this->assertEquals('no newlines here', Context::sanitize('no newlines here'));
    }

}
