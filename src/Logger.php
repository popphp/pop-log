<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log;

use Pop\Log\Writer\WriterInterface;

/**
 * Logger class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Logger
{

    /**
     * Constants for log levels
     * @var int
     */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * Message level short codes
     * @var array
     */
    protected array $levels = [
        0 => 'EMERGENCY',
        1 => 'ALERT',
        2 => 'CRITICAL',
        3 => 'ERROR',
        4 => 'WARNING',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG'
    ];

    /**
     * Log writers
     * @var array
     */
    protected array $writers = [];

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
     * @param  string                 $timestampFormat
     */
    public function __construct(WriterInterface|array|null $writer = [], string $timestampFormat = 'Y-m-d H:i:s')
    {
        if ($timestampFormat !== null) {
            $this->setTimestampFormat($timestampFormat);
        }

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
     * Set log level limit for all log writers
     *
     * @param  int $level
     * @return Logger
     */
    public function setLogLimit(int $level): Logger
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
     * Get level
     *
     * @param  int $level
     * @return string
     */
    public function getLevel(int $level): string
    {
        return $this->levels[(int)$level] ?? '';
    }

    /**
     * Static method to get log level
     *
     * @param  int $level
     * @return string
     */
    public static function getLogLevel(int $level): string
    {
        return (new self())->getLevel($level);
    }

    /**
     * Add an EMERGENCY log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function emergency(mixed $message, array $context = []): Logger
    {
        return $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Add an ALERT log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function alert(mixed $message, array $context = []): Logger
    {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Add a CRITICAL log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function critical(mixed $message, array $context = []): Logger
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Add an ERROR log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function error(mixed $message, array $context = []): Logger
    {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Add a WARNING log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function warning(mixed $message, array $context = []): Logger
    {
        return $this->log(self::WARNING, $message, $context);
    }

    /**
     * Add a NOTICE log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function notice(mixed $message, array $context = []): Logger
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Add an INFO log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function info(mixed $message, array $context = []): Logger
    {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Add a DEBUG log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function debug(mixed $message, array $context = []): Logger
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Add a log entry
     *
     * @param  mixed $level
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function log(mixed $level, mixed $message, array $context = []): Logger
    {
        if (!isset($context['timestamp'])) {
            $context['timestamp'] = date($this->timestampFormat);
        }
        if (!isset($context['name'])) {
            $context['name'] = $this->levels[$level];
        }

        foreach ($this->writers as $writer) {
            $writer->writeLog($level, (string)$message, $context);
        }

        return $this;
    }

}
