<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
interface WriterInterface
{

    /**
     * Set log limit
     *
     * @param  int $level
     * @return WriterInterface
     */
    public function setLogLimit($level);

    /**
     * Get log limit
     *
     * @return int
     */
    public function getLogLimit();

    /**
     * Has log limit
     *
     * @return boolean
     */
    public function hasLogLimit();

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  int $level
     * @return boolean
     */
    public function isWithinLogLimit($level);

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return WriterInterface
     */
    public function writeLog($level, $message, array $context = []);

    /**
     * Determine
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = []);

}
