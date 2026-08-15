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
namespace Pop\Log\Writer;

use Pop\Log\Context;
use Pop\Log\Level;

/**
 * Log writer abstract class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
abstract class AbstractWriter implements WriterInterface
{

    /**
     * Log limit, stored as the canonical PSR-3 level string
     * @var ?string
     */
    protected ?string $limit = null;

    /**
     * Set log limit
     *
     * Accepts either a PSR-3 level string or a legacy severity int (0-7) for backward compatibility;
     * always normalized and stored as the canonical PSR-3 string.
     *
     * @param  string|int $level
     * @return AbstractWriter
     */
    public function setLogLimit(string|int $level): AbstractWriter
    {
        $this->limit = Level::fromSeverity(Level::toSeverity($level));
        return $this;
    }

    /**
     * Get log limit
     *
     * @return string|null
     */
    public function getLogLimit(): string|null
    {
        return $this->limit;
    }

    /**
     * Has log limit
     *
     * @return bool
     */
    public function hasLogLimit(): bool
    {
        return ($this->limit !== null);
    }

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  string|int $level
     * @return bool
     */
    public function isWithinLogLimit(string|int $level): bool
    {
        $severity = Level::toSeverity($level);
        return (($this->limit === null) || ($severity <= Level::toSeverity($this->limit)));
    }

    /**
     * Write to the log
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return AbstractWriter
     */
    abstract public function writeLog(string $level, string $message, array $context = []): AbstractWriter;

    /**
     * Get context for log
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = []): string
    {
        return Context::serialize($context);
    }

    /**
     * Strip CR/LF from a value before it's written into a delimited/line-based log format, to prevent an
     * embedded newline from forging a fake extra line (or, for writers that build header-style text like
     * Mail's Subject line, a fake extra header).
     *
     * @param  string $value
     * @return string
     */
    protected function sanitize(string $value): string
    {
        return Context::sanitize($value);
    }

}
