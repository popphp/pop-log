<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log;

use Pop\Log\Writer\WriterInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

/**
 * Logger class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Logger implements LoggerInterface
{

    /**
     * Constants for log levels (PSR-3 level strings)
     * @var string
     */
    const EMERGENCY = LogLevel::EMERGENCY;
    const ALERT     = LogLevel::ALERT;
    const CRITICAL  = LogLevel::CRITICAL;
    const ERROR     = LogLevel::ERROR;
    const WARNING   = LogLevel::WARNING;
    const NOTICE    = LogLevel::NOTICE;
    const INFO      = LogLevel::INFO;
    const DEBUG     = LogLevel::DEBUG;

    /**
     * Reserved context keys never treated as interpolation placeholders
     * @var array
     */
    protected const RESERVED_CONTEXT_KEYS = ['timestamp', 'name', 'format'];

    /**
     * Log writers
     * @var array
     */
    protected array $writers = [];

    /**
     * Context-enrichment processors
     * @var callable[]
     */
    protected array $processors = [];

    /**
     * Log timestamp format
     * @var string
     */
    protected string $timestampFormat = 'Y-m-d H:i:s';

    /**
     * Constructor
     *
     * Instantiate the logger object
     *
     * @param  WriterInterface|array|null $writer
     * @param  string                     $timestampFormat
     */
    public function __construct(WriterInterface|array|null $writer = [], string $timestampFormat = 'Y-m-d H:i:s')
    {
        $this->setTimestampFormat($timestampFormat);

        if ($writer !== null) {
            if (is_array($writer)) {
                $this->addWriters($writer);
            } else {
                $this->addWriter($writer);
            }
        }
    }

    /**
     * Add log writers
     *
     * @param  array $writers
     * @return Logger
     */
    public function addWriters(array $writers): Logger
    {
        foreach ($writers as $writer) {
            $this->addWriter($writer);
        }
        return $this;
    }

    /**
     * Add a log writer
     *
     * @param  Writer\WriterInterface $writer
     * @return Logger
     */
    public function addWriter(Writer\WriterInterface $writer): Logger
    {
        $this->writers[] = $writer;
        return $this;
    }

    /**
     * Get all log writers
     *
     * @return array
     */
    public function getWriters(): array
    {
        return $this->writers;
    }

    /**
     * Add context-enrichment processors
     *
     * @param  array $processors
     * @return Logger
     */
    public function addProcessors(array $processors): Logger
    {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
        return $this;
    }

    /**
     * Add a context-enrichment processor
     *
     * @param  callable $processor
     * @return Logger
     */
    public function addProcessor(callable $processor): Logger
    {
        $this->processors[] = $processor;
        return $this;
    }

    /**
     * Get all context-enrichment processors
     *
     * @return callable[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    /**
     * Set log level limit for all log writers
     *
     * @param  string|int $level
     * @return Logger
     */
    public function setLogLimit(string|int $level): Logger
    {
        foreach ($this->writers as $writer) {
            $writer->setLogLimit($level);
        }
        return $this;
    }

    /**
     * Set timestamp format
     *
     * @param  string $format
     * @return Logger
     */
    public function setTimestampFormat(string $format = 'Y-m-d H:i:s'): Logger
    {
        $this->timestampFormat = $format;
        return $this;
    }

    /**
     * Get timestamp format
     *
     * @return string
     */
    public function getTimestampFormat(): string
    {
        return $this->timestampFormat;
    }

    /**
     * Get level display name
     *
     * Accepts either a PSR-3 level string or a legacy severity int (0-7).
     *
     * @param  string|int $level
     * @return string
     */
    public function getLevel(string|int $level): string
    {
        return Level::toName(Level::fromSeverity(Level::toSeverity($level)));
    }

    /**
     * Static method to get log level display name
     *
     * @param  string|int $level
     * @return string
     */
    public static function getLogLevel(string|int $level): string
    {
        return Level::toName(Level::fromSeverity(Level::toSeverity($level)));
    }

    /**
     * Add an EMERGENCY log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Add an ALERT log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Add a CRITICAL log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Add an ERROR log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Add a WARNING log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Add a NOTICE log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Add an INFO log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Add a DEBUG log entry
     *
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Add a log entry
     *
     * $level stays untyped/mixed to match Psr\Log\LoggerInterface::log() exactly (PHP forbids narrowing
     * an interface parameter's type). Accepts a PSR-3 level string or a legacy severity int (0-7).
     * Registered processors enrich $context before interpolation runs.
     *
     * @param  mixed             $level
     * @param  string|Stringable $message
     * @param  array             $context
     * @return void
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $level = Level::fromSeverity(Level::toSeverity($level));

        if (!isset($context['timestamp'])) {
            $context['timestamp'] = date($this->timestampFormat);
        }
        if (!isset($context['name'])) {
            $context['name'] = Level::toName($level);
        }

        foreach ($this->processors as $processor) {
            $context = $processor($context);
        }

        $context['timestamp'] ??= date($this->timestampFormat);
        $context['name']      ??= Level::toName($level);

        [$message, $context] = $this->interpolate((string)$message, $context);

        $failure = null;

        foreach ($this->writers as $writer) {
            try {
                $writer->writeLog($level, $message, $context);
            } catch (\Exception $exception) {
                $failure ??= $exception;
            }
        }

        if ($failure !== null) {
            throw $failure;
        }
    }

    /**
     * Substitute {key} placeholders in the message from scalar/Stringable context values.
     *
     * Reserved keys (timestamp, name, format) are never treated as placeholders, since they're
     * Logger-managed metadata rather than user-supplied context. Consumed keys are removed from the
     * returned context so writers don't also serialize a value that's already inline in the message.
     *
     * @param  string $message
     * @param  array  $context
     * @return array
     */
    protected function interpolate(string $message, array $context): array
    {
        $replace = [];

        foreach ($context as $key => $value) {
            if (in_array($key, self::RESERVED_CONTEXT_KEYS, true)) {
                continue;
            }

            if ((is_scalar($value) || $value instanceof Stringable) && str_contains($message, '{' . $key . '}')) {
                $replace['{' . $key . '}'] = (string)$value;
                unset($context[$key]);
            }
        }

        return [strtr($message, $replace), $context];
    }

}
