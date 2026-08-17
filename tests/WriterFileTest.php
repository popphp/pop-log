<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\File;
use Pop\Log\Writer\Exception;
use Pop\Log\Formatter\Line;
use Pop\Log\Formatter\NdJson;
use Psr\Log\LogLevel;
use PHPUnit\Framework\TestCase;

class WriterFileTest extends TestCase
{

    public function testConstructor()
    {
        $writer = new File(__DIR__ . '/tmp/test.log');
        $this->assertInstanceOf('Pop\Log\Writer\File', $writer);
        $this->assertFileExists(__DIR__ . '/tmp/test.log');
        $this->assertEquals(__DIR__ . '/tmp/test.log', $writer->getFile());
        $this->assertEquals('log', $writer->getType());
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testCsv()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.csv');
        $writer->writeLog(LogLevel::NOTICE, 'This is a CSV test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.csv');
        $contents = file_get_contents(__DIR__ . '/tmp/test.csv');
        $this->assertStringContainsString('This is a CSV test.', $contents);
        $this->assertStringContainsString(',notice,', $contents);
        unlink(__DIR__ . '/tmp/test.csv');
    }

    public function testTsv()
    {
        $writer = new File(__DIR__ . '/tmp/test.tsv');
        $writer->writeLog(LogLevel::NOTICE, 'This is a TSV test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.tsv');
        $this->assertStringContainsString('This is a TSV test.', file_get_contents(__DIR__ . '/tmp/test.tsv'));
        unlink(__DIR__ . '/tmp/test.tsv');
    }

    public function testXml()
    {
        $writer = new File(__DIR__ . '/tmp/test.xml');
        $writer->writeLog(LogLevel::NOTICE, 'This is an XML test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.xml');
        $this->assertStringContainsString('This is an XML test.', file_get_contents(__DIR__ . '/tmp/test.xml'));
        unlink(__DIR__ . '/tmp/test.xml');
    }

    public function testXmlWithContext()
    {
        $writer = new File(__DIR__ . '/tmp/test.xml');
        $writer->writeLog(LogLevel::NOTICE, 'This is an XML test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'foo' => 'bar'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.xml');
        $this->assertStringContainsString('This is an XML test.', file_get_contents(__DIR__ . '/tmp/test.xml'));
        $this->assertStringContainsString('foo=bar;', file_get_contents(__DIR__ . '/tmp/test.xml'));
        unlink(__DIR__ . '/tmp/test.xml');
    }

    public function testJson()
    {
        $writer = new File(__DIR__ . '/tmp/test.json');
        $writer->writeLog(LogLevel::NOTICE, 'This is an JSON test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $this->assertFileExists(__DIR__ . '/tmp/test.json');
        $this->assertStringContainsString('This is an JSON test.', file_get_contents(__DIR__ . '/tmp/test.json'));
        unlink(__DIR__ . '/tmp/test.json');
    }

    public function testXmlJsonWriteHoldsExclusiveLockDuringReadModifyWrite()
    {
        $path = __DIR__ . '/tmp/test-lock.xml';
        if (file_exists($path)) {
            unlink($path);
        }
        touch($path);

        $writer = new File($path);

        $reflection = new \ReflectionMethod(File::class, 'withExclusiveLock');

        $lockedDuringTransform = null;

        $reflection->invoke($writer, function ($current) use ($path, &$lockedDuringTransform) {
            $second = fopen($path, 'r');
            $lockedDuringTransform = !flock($second, LOCK_EX | LOCK_NB);
            fclose($second);
            return $current;
        });

        $second = fopen($path, 'r');
        $lockedAfterRelease = flock($second, LOCK_EX | LOCK_NB);
        flock($second, LOCK_UN);
        fclose($second);

        $this->assertTrue(
            $lockedDuringTransform,
            'A second handle should NOT be able to acquire an exclusive lock while withExclusiveLock() holds it'
        );
        $this->assertTrue(
            $lockedAfterRelease,
            'A second handle SHOULD be able to acquire the lock after withExclusiveLock() releases it'
        );

        unlink($path);
    }

    public function testWithExclusiveLockThrowsWhenLockCannotBeAcquired()
    {
        $path = __DIR__ . '/tmp/test-lock-fail.xml';
        if (file_exists($path)) {
            unlink($path);
        }

        $writer = new File($path);

        // php://memory has a valid, fopen()-able resource but does not support flock(), so it
        // deterministically exercises withExclusiveLock()'s failure-to-acquire-a-lock branch
        // (including the is_resource()-guarded fclose() cleanup) without needing a second
        // process to hold a real conflicting lock.
        $fileProperty = new \ReflectionProperty(File::class, 'file');
        $fileProperty->setValue($writer, 'php://memory');

        $reflection = new \ReflectionMethod(File::class, 'withExclusiveLock');

        $this->expectException(Exception::class);
        try {
            $reflection->invoke($writer, fn($current) => $current);
        } finally {
            unlink($path);
        }
    }

    public function testCsvStripsCrLf()
    {
        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
        $writer = new File(__DIR__ . '/tmp/test.csv');
        $writer->writeLog(LogLevel::NOTICE, "Injected\r\nsecond line", [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => "Bad\r\nName"
        ]);

        $contents = rtrim(file_get_contents(__DIR__ . '/tmp/test.csv'), "\r\n");
        $this->assertStringNotContainsString("\r", $contents);
        $this->assertStringNotContainsString("\n", $contents);
        $this->assertStringContainsString('Injected  second line', $contents);
        $this->assertStringContainsString('Bad  Name', $contents);
        unlink(__DIR__ . '/tmp/test.csv');
    }

    public function testTsvStripsCrLf()
    {
        if (file_exists(__DIR__ . '/tmp/test.tsv')) {
            unlink(__DIR__ . '/tmp/test.tsv');
        }
        $writer = new File(__DIR__ . '/tmp/test.tsv');
        $writer->writeLog(LogLevel::NOTICE, "Injected\r\nsecond line", [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => "Bad\r\nName"
        ]);

        $contents = rtrim(file_get_contents(__DIR__ . '/tmp/test.tsv'), "\r\n");
        $this->assertStringNotContainsString("\r", $contents);
        $this->assertStringNotContainsString("\n", $contents);
        $this->assertStringContainsString('Injected  second line', $contents);
        $this->assertStringContainsString('Bad  Name', $contents);
        unlink(__DIR__ . '/tmp/test.tsv');
    }

    public function testDefaultBranchStripsCrLf()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->writeLog(LogLevel::NOTICE, "Injected\r\nsecond line", [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => "Bad\r\nName"
        ]);

        $contents = rtrim(file_get_contents(__DIR__ . '/tmp/test.log'), "\r\n");
        $this->assertStringNotContainsString("\r", $contents);
        $this->assertStringNotContainsString("\n", $contents);
        $this->assertStringContainsString('Injected  second line', $contents);
        $this->assertStringContainsString('Bad  Name', $contents);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testCsvStripsCrLfFromContextValue()
    {
        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
        $writer = new File(__DIR__ . '/tmp/test.csv');
        $writer->writeLog(LogLevel::NOTICE, 'Login failed', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'username'  => "bob\r\nADMIN GRANTED ROOT;"
        ]);

        $contents = file_get_contents(__DIR__ . '/tmp/test.csv');
        $trimmed  = rtrim($contents, "\r\n");
        $this->assertStringNotContainsString("\r", $trimmed);
        $this->assertStringNotContainsString("\n", $trimmed);
        // Exactly one PHP_EOL: the entry's own trailing terminator, no forged extra line
        $this->assertSame(1, substr_count($contents, PHP_EOL));
        unlink(__DIR__ . '/tmp/test.csv');
    }

    public function testTsvStripsCrLfFromContextValue()
    {
        if (file_exists(__DIR__ . '/tmp/test.tsv')) {
            unlink(__DIR__ . '/tmp/test.tsv');
        }
        $writer = new File(__DIR__ . '/tmp/test.tsv');
        $writer->writeLog(LogLevel::NOTICE, 'Login failed', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'username'  => "bob\r\nADMIN GRANTED ROOT;"
        ]);

        $contents = file_get_contents(__DIR__ . '/tmp/test.tsv');
        $trimmed  = rtrim($contents, "\r\n");
        $this->assertStringNotContainsString("\r", $trimmed);
        $this->assertStringNotContainsString("\n", $trimmed);
        // Exactly one PHP_EOL: the entry's own trailing terminator, no forged extra line
        $this->assertSame(1, substr_count($contents, PHP_EOL));
        unlink(__DIR__ . '/tmp/test.tsv');
    }

    public function testDefaultBranchStripsCrLfFromContextValue()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->writeLog(LogLevel::NOTICE, 'Login failed', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE',
            'username'  => "bob\r\nADMIN GRANTED ROOT;"
        ]);

        $contents = file_get_contents(__DIR__ . '/tmp/test.log');
        $trimmed  = rtrim($contents, "\r\n");
        $this->assertStringNotContainsString("\r", $trimmed);
        $this->assertStringNotContainsString("\n", $trimmed);
        // Exactly one PHP_EOL: the entry's own trailing terminator, no forged extra line
        $this->assertSame(1, substr_count($contents, PHP_EOL));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testSetLogLimitException()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $this->expectException('InvalidArgumentException');
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(8);
    }

    public function testSetLogLimitExceptionOnInvalidString()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $this->expectException('InvalidArgumentException');
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit('bogus');
    }

    public function testIsWithinLogLimitException()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $this->expectException('InvalidArgumentException');
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(1);
        $this->assertFalse($writer->isWithinLogLimit(8));
    }

    public function testLogLimit1()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(1);
        $this->assertTrue($writer->hasLogLimit());
        $this->assertEquals(LogLevel::ALERT, $writer->getLogLimit());
        $this->assertTrue($writer->isWithinLogLimit(1));
        $this->assertFalse($writer->isWithinLogLimit(3));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLogLimitAcceptsStringLevel()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $writer->setLogLimit(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $writer->getLogLimit());
        $this->assertTrue($writer->isWithinLogLimit(LogLevel::ALERT));
        $this->assertFalse($writer->isWithinLogLimit(LogLevel::ERROR));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testJsonlExtensionAutoSelectsNdJsonFormatter()
    {
        if (file_exists(__DIR__ . '/tmp/test.jsonl')) {
            unlink(__DIR__ . '/tmp/test.jsonl');
        }
        $writer = new File(__DIR__ . '/tmp/test.jsonl');
        $this->assertInstanceOf(NdJson::class, $writer->getFormatter());

        $writer->writeLog(LogLevel::NOTICE, 'This is an NDJSON test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $line = trim(file_get_contents(__DIR__ . '/tmp/test.jsonl'));
        $decoded = json_decode($line, true);
        $this->assertEquals('This is an NDJSON test.', $decoded['message']);
        unlink(__DIR__ . '/tmp/test.jsonl');
    }

    public function testNdjsonExtensionAutoSelectsNdJsonFormatter()
    {
        if (file_exists(__DIR__ . '/tmp/test.ndjson')) {
            unlink(__DIR__ . '/tmp/test.ndjson');
        }
        $writer = new File(__DIR__ . '/tmp/test.ndjson');
        $this->assertInstanceOf(NdJson::class, $writer->getFormatter());
        unlink(__DIR__ . '/tmp/test.ndjson');
    }

    public function testExplicitFormatterOverridesExtensionDefault()
    {
        if (file_exists(__DIR__ . '/tmp/test.csv')) {
            unlink(__DIR__ . '/tmp/test.csv');
        }
        $formatter = new NdJson();
        $writer = new File(__DIR__ . '/tmp/test.csv', $formatter);
        $this->assertSame($formatter, $writer->getFormatter());
        unlink(__DIR__ . '/tmp/test.csv');
    }

    public function testExplicitFormatterOverridesXmlExtension()
    {
        if (file_exists(__DIR__ . '/tmp/test.xml')) {
            unlink(__DIR__ . '/tmp/test.xml');
        }
        $formatter = new NdJson();
        $writer = new File(__DIR__ . '/tmp/test.xml', $formatter);
        $this->assertSame($formatter, $writer->getFormatter());

        $writer->writeLog(LogLevel::NOTICE, 'This should be NDJSON, not XML.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $contents = file_get_contents(__DIR__ . '/tmp/test.xml');
        $this->assertStringNotContainsString('<?xml', $contents);
        $decoded = json_decode(trim($contents), true);
        $this->assertEquals('This should be NDJSON, not XML.', $decoded['message']);
        unlink(__DIR__ . '/tmp/test.xml');
    }

    public function testGetFormatterReturnsAutoSelectedDefault()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $writer = new File(__DIR__ . '/tmp/test.log');
        $this->assertInstanceOf(Line::class, $writer->getFormatter());
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testGetFormatterReturnsNullForXmlWithNoExplicitFormatter()
    {
        if (file_exists(__DIR__ . '/tmp/test.xml')) {
            unlink(__DIR__ . '/tmp/test.xml');
        }
        $writer = new File(__DIR__ . '/tmp/test.xml');
        $this->assertNull($writer->getFormatter());
        unlink(__DIR__ . '/tmp/test.xml');
    }

}
