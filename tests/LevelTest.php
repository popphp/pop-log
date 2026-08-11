<?php

namespace Pop\Log\Test;

use Pop\Log\Level;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LevelTest extends TestCase
{

    public function testToSeverityFromString()
    {
        $this->assertEquals(0, Level::toSeverity(LogLevel::EMERGENCY));
        $this->assertEquals(3, Level::toSeverity(LogLevel::ERROR));
        $this->assertEquals(7, Level::toSeverity(LogLevel::DEBUG));
    }

    public function testToSeverityFromStringIsCaseInsensitive()
    {
        $this->assertEquals(3, Level::toSeverity('ERROR'));
        $this->assertEquals(3, Level::toSeverity('Error'));
    }

    public function testToSeverityFromInt()
    {
        $this->assertEquals(3, Level::toSeverity(3));
        $this->assertEquals(0, Level::toSeverity(0));
        $this->assertEquals(7, Level::toSeverity(7));
    }

    public function testToSeverityFromNumericString()
    {
        $this->assertEquals(3, Level::toSeverity('3'));
        $this->assertEquals(0, Level::toSeverity('0'));
        $this->assertEquals(7, Level::toSeverity('7'));
    }

    public function testToSeverityThrowsOnInvalidString()
    {
        $this->expectException(InvalidArgumentException::class);
        Level::toSeverity('bogus');
    }

    public function testToSeverityThrowsOnInvalidInt()
    {
        $this->expectException(InvalidArgumentException::class);
        Level::toSeverity(8);
    }

    public function testToSeverityThrowsOnInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        Level::toSeverity(['bad']);
    }

    public function testToName()
    {
        $this->assertEquals('ERROR', Level::toName(LogLevel::ERROR));
        $this->assertEquals('EMERGENCY', Level::toName('emergency'));
    }

    public function testToNameThrowsOnInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        Level::toName('bogus');
    }

    public function testFromSeverity()
    {
        $this->assertEquals(LogLevel::ERROR, Level::fromSeverity(3));
        $this->assertEquals(LogLevel::EMERGENCY, Level::fromSeverity(0));
        $this->assertEquals(LogLevel::DEBUG, Level::fromSeverity(7));
    }

    public function testFromSeverityThrowsOnInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        Level::fromSeverity(99);
    }

}
