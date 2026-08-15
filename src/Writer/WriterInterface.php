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

/**
 * Log writer interface
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface WriterInterface
{

    /**
     * Set log limit
     *
     * @param  string|int $level
     * @return WriterInterface
     */
    public function setLogLimit(string|int $level): WriterInterface;

    /**
     * Get log limit
     *
     * @return string|null
     */
    public function getLogLimit(): string|null;

    /**
     * Has log limit
     *
     * @return bool
     */
    public function hasLogLimit(): bool;

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  string|int $level
     * @return bool
     */
    public function isWithinLogLimit(string|int $level): bool;

    /**
     * Write to the log
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @throws Exception
     * @return WriterInterface
     */
    public function writeLog(string $level, string $message, array $context = []): WriterInterface;

    /**
     * Determine
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = []): string;

}
