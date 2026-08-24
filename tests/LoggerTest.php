<?php

namespace Pop\Log\Test;

use Pop\Log\Logger;
use Pop\Log\Writer;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{

    public function testConstructor1()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->assertInstanceOf('Pop\Log\Logger', $logger);
        $this->assertEquals(1, count($logger->getWriters()));
        $this->assertFileExists(__DIR__ . '/tmp/test.log');
    }

    public function testImplementsLoggerInterface()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->assertInstanceOf(LoggerInterface::class, $logger);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLevelConstantsMatchPsr3()
    {
        $this->assertEquals(LogLevel::EMERGENCY, Logger::EMERGENCY);
        $this->assertEquals(LogLevel::ALERT, Logger::ALERT);
        $this->assertEquals(LogLevel::CRITICAL, Logger::CRITICAL);
        $this->assertEquals(LogLevel::ERROR, Logger::ERROR);
        $this->assertEquals(LogLevel::WARNING, Logger::WARNING);
        $this->assertEquals(LogLevel::NOTICE, Logger::NOTICE);
        $this->assertEquals(LogLevel::INFO, Logger::INFO);
        $this->assertEquals(LogLevel::DEBUG, Logger::DEBUG);
    }

    public function testConstructor2()
    {
        $log1 = new Writer\File(__DIR__ . '/tmp/test1.log');
        $log2 = new Writer\File(__DIR__ . '/tmp/test2.log');
        $logger = new Logger([$log1, $log2]);
        $this->assertInstanceOf('Pop\Log\Logger', $logger);
        $this->assertEquals(2, count($logger->getWriters()));
        $this->assertFileExists(__DIR__ . '/tmp/test1.log');
        $this->assertFileExists(__DIR__ . '/tmp/test2.log');
        unlink(__DIR__ . '/tmp/test1.log');
        unlink(__DIR__ . '/tmp/test2.log');
    }

    public function testAddWriters()
    {
        if (file_exists(__DIR__ . '/tmp/log.sqlite')) {
            unlink(__DIR__ . '/tmp/log.sqlite');
        }
        touch(__DIR__ . '/tmp/log.sqlite');
        chmod(__DIR__ . '/tmp/log.sqlite', 0777);
        $db     = \Pop\Db\Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $logger = new Logger();
        $logger->addWriters([
            new Writer\File(__DIR__ . '/tmp/test.log'),
            new Writer\Database($db, 'logs')
        ]);
        $this->assertEquals(2, count($logger->getWriters()));
        if (file_exists(__DIR__ . '/tmp/log.sqlite')) {
            unlink(__DIR__ . '/tmp/log.sqlite');
        }
    }

    public function testSetTimestamp()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->setTimestampFormat('m/d/Y');
        $this->assertEquals('m/d/Y', $logger->getTimestampFormat());
    }

    public function testSetLogLimit()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->setLogLimit(1);
        $this->assertEquals(LogLevel::ALERT, $logger->getWriters()[0]->getLogLimit());
    }

    public function testSetLogLimitWithStringLevel()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->setLogLimit(LogLevel::ALERT);
        $this->assertEquals(LogLevel::ALERT, $logger->getWriters()[0]->getLogLimit());
    }

    public function testGetLevel()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->assertEquals('EMERGENCY', $logger->getLevel(Logger::EMERGENCY));
        $this->assertEquals('INFO', Logger::getLogLevel(6));
        $this->assertEquals('ERROR', Logger::getLogLevel(LogLevel::ERROR));
    }

    public function testLog()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->log(Logger::ALERT, 'This is an alert.');
        $logger->emergency('This is an emergency.');
        $logger->alert('This is an alert #2.');
        $logger->critical('This is a critical warning.');
        $logger->error('This is an error.');
        $logger->warning('This is a warning.');
        $logger->notice('This is a notice.');
        $logger->info('This is an info.');
        $logger->debug('This is a debug.');

        $log = file_get_contents(__DIR__ . '/tmp/test.log');

        $this->assertStringContainsString('This is an alert', $log);
        $this->assertStringContainsString('This is an emergency.', $log);
        $this->assertStringContainsString('This is an alert #2.', $log);
        $this->assertStringContainsString('This is a critical warning.', $log);
        $this->assertStringContainsString('This is an error.', $log);
        $this->assertStringContainsString('This is a warning.', $log);
        $this->assertStringContainsString('This is a notice.', $log);
        $this->assertStringContainsString('This is an info.', $log);
        $this->assertStringContainsString('This is a debug.', $log);

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLogWithLegacyIntLevel()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->log(3, 'This is a legacy int level error.');
        $this->assertStringContainsString('This is a legacy int level error.', file_get_contents(__DIR__ . '/tmp/test.log'));
        $this->assertStringContainsString("\terror\t", file_get_contents(__DIR__ . '/tmp/test.log'));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLogThrowsOnInvalidLevel()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->expectException(InvalidArgumentException::class);
        try {
            $logger->log('bogus', 'This should not be written.');
        } finally {
            unlink(__DIR__ . '/tmp/test.log');
        }
    }

    public function testTextContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => 'bar']);
        $this->assertStringContainsString('foo=bar;', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testJsonContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => 'bar', 'format' => 'json']);
        $this->assertStringContainsString('{', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testPhpContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => 'bar', 'format' => 'php']);
        $this->assertStringContainsString('{', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testArrayAndObjectContext()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('This is an info.', ['foo' => [123], 'bar' => new \StdClass()]);
        $this->assertStringContainsString('foo=[Array]', file_get_contents(__DIR__ . '/tmp/test.log'));
        $this->assertStringContainsString('bar=[Object]', file_get_contents(__DIR__ . '/tmp/test.log'));

        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLogLimit()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->setLogLimit(1);
        $logger->alert('This is an alert!');
        $logger->notice('This is a notice!');
        $this->assertStringContainsString('This is an alert!', file_get_contents(__DIR__ . '/tmp/test.log'));
        $this->assertStringNotContainsString('This is a notice!', file_get_contents(__DIR__ . '/tmp/test.log'));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testInterpolation()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('User {user} logged in from {ip}', ['user' => 'nick', 'ip' => '1.2.3.4']);
        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        $this->assertStringContainsString('User nick logged in from 1.2.3.4', $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testInterpolationStripsConsumedContextKeys()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('User {user} logged in', ['user' => 'nick', 'ip' => '1.2.3.4']);
        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        // 'user' was consumed by interpolation, so it should not also appear in the context blob
        $this->assertStringNotContainsString('user=nick;', $log);
        $this->assertStringContainsString('ip=1.2.3.4;', $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testInterpolationLeavesUnmatchedPlaceholderLiteral()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('Missing {nothere} placeholder', []);
        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        $this->assertStringContainsString('Missing {nothere} placeholder', $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testInterpolationDoesNotConsumeReservedKeys()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info('Timestamp placeholder {timestamp} left alone', ['timestamp' => '2000-01-01 00:00:00']);
        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        // the literal placeholder is left untouched, and the real generated timestamp still appears as the log line's own timestamp column
        $this->assertStringContainsString('Timestamp placeholder {timestamp} left alone', $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testMessageMustBeStringOrStringable()
    {
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $this->expectException(\TypeError::class);
        try {
            $logger->info(['not', 'a', 'string']);
        } finally {
            unlink(__DIR__ . '/tmp/test.log');
        }
    }

    public function testMessageAcceptsStringable()
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable message';
            }
        };

        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->info($stringable);
        $this->assertStringContainsString('stringable message', file_get_contents(__DIR__ . '/tmp/test.log'));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testLogContinuesToOtherWritersWhenOneThrows()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }

        $throwingWriter = new class extends Writer\AbstractWriter {
            public function writeLog(string $level, string $message, array $context = []): static
            {
                throw new Writer\Exception('Simulated writer failure');
            }
        };

        $fileWriter = new Writer\File(__DIR__ . '/tmp/test.log');
        $logger     = new Logger([$throwingWriter, $fileWriter]);

        $this->expectException(Writer\Exception::class);
        try {
            $logger->info('This should still reach the file writer.');
        } finally {
            $this->assertStringContainsString(
                'This should still reach the file writer.',
                file_get_contents(__DIR__ . '/tmp/test.log')
            );
            unlink(__DIR__ . '/tmp/test.log');
        }
    }

    public function testLogChainsFailuresFromMultipleWritersOntoThrownException()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }

        $firstThrowingWriter = new class extends Writer\AbstractWriter {
            public function writeLog(string $level, string $message, array $context = []): static
            {
                throw new Writer\Exception('First writer failed');
            }
        };

        $secondThrowingWriter = new class extends Writer\AbstractWriter {
            public function writeLog(string $level, string $message, array $context = []): static
            {
                throw new Writer\Exception('Second writer failed');
            }
        };

        $fileWriter = new Writer\File(__DIR__ . '/tmp/test.log');
        $logger     = new Logger([$firstThrowingWriter, $secondThrowingWriter, $fileWriter]);

        try {
            $logger->info('This should still reach the file writer.');
            $this->fail('Expected a Writer\Exception to be thrown.');
        } catch (Writer\Exception $exception) {
            $this->assertStringContainsString(
                'This should still reach the file writer.',
                file_get_contents(__DIR__ . '/tmp/test.log')
            );
            unlink(__DIR__ . '/tmp/test.log');

            $this->assertEquals('First writer failed', $exception->getMessage());
            $this->assertNotNull($exception->getPrevious());
            $this->assertEquals('Second writer failed', $exception->getPrevious()->getMessage());
            $this->assertNull($exception->getPrevious()->getPrevious());
        }
    }

    public function testAddProcessorEnrichesContext()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->addProcessor(function (array $context): array {
            $context['request_id'] = 'req-123';
            return $context;
        });

        $logger->info('This is an info.');

        $this->assertCount(1, $logger->getProcessors());
        $this->assertStringContainsString('request_id=req-123;', file_get_contents(__DIR__ . '/tmp/test.log'));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testProcessorInjectedValueUsableAsPlaceholder()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->addProcessor(function (array $context): array {
            $context['request_id'] = 'req-123';
            return $context;
        });

        $logger->info('Handling request {request_id}');

        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        $this->assertStringContainsString('Handling request req-123', $log);
        // Consumed by interpolation, so it should not also appear in the serialized context blob
        $this->assertStringNotContainsString('request_id=req-123;', $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testAddProcessorsRunInRegistrationOrder()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->addProcessors([
            function (array $context): array {
                $context['step'] = 'one';
                return $context;
            },
            function (array $context): array {
                $context['step'] .= '-two';
                return $context;
            }
        ]);

        $logger->info('Ordered processors.');

        $this->assertCount(2, $logger->getProcessors());
        $this->assertStringContainsString('step=one-two;', file_get_contents(__DIR__ . '/tmp/test.log'));
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testProcessorDroppingReservedKeysDoesNotBreakTheWrite()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->addProcessor(function (array $context): array {
            // Simulates a processor mistakenly returning a fresh array instead of enriching the existing one
            return ['request_id' => 'req-1'];
        });

        $logger->info('Entry survives a processor that drops timestamp/name.');

        $log = file_get_contents(__DIR__ . '/tmp/test.log');
        $this->assertStringContainsString('Entry survives a processor that drops timestamp/name.', $log);
        $this->assertStringContainsString("\tINFO\t", $log);
        unlink(__DIR__ . '/tmp/test.log');
    }

    public function testProcessorThrowsAbortsLoggingBeforeAnyWriterRuns()
    {
        if (file_exists(__DIR__ . '/tmp/test.log')) {
            unlink(__DIR__ . '/tmp/test.log');
        }
        $logger = new Logger(new Writer\File(__DIR__ . '/tmp/test.log'));
        $logger->addProcessor(function (array $context): array {
            throw new \RuntimeException('Simulated processor failure');
        });

        $this->expectException(\RuntimeException::class);
        try {
            $logger->info('This should never reach the writer.');
        } finally {
            $this->assertStringNotContainsString(
                'This should never reach the writer.',
                file_get_contents(__DIR__ . '/tmp/test.log')
            );
            unlink(__DIR__ . '/tmp/test.log');
        }
    }

}
