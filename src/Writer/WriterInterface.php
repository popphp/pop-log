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
namespace Pop\Log\Writer;

/**
 * Log writer interface
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface WriterInterface
{

    /**
     * Set log limit
     *
     * @param  int $level
     * @return WriterInterface
     */
    public function setLogLimit(int $level): WriterInterface;

    /**
     * Get log limit
     *
     * @return int|null
     */
    public function getLogLimit(): int|null;

    /**
     * Has log limit
     *
     * @return bool
     */
    public function hasLogLimit(): bool;

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  int $level
     * @return bool
     */
    public function isWithinLogLimit(int $level): bool;

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return WriterInterface
     */
    public function writeLog(mixed $level, string $message, array $context = []): WriterInterface;

    /**
     * Determine
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = []): string;

}
