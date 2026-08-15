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
namespace Pop\Log\Formatter;

use Pop\Log\Context;

/**
 * Plain tab-separated line formatter
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Line implements FormatterInterface
{

    /**
     * Format a single log entry into a tab-separated line
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    public function format(string $level, string $message, array $context): string
    {
        return Context::sanitize($context['timestamp']) . "\t" . $level . "\t" .
            Context::sanitize($context['name']) . "\t" . Context::sanitize($message) . "\t" .
            Context::sanitize(Context::serialize($context));
    }

}
