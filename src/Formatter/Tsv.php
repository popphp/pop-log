<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Formatter;

use Pop\Log\Context;

/**
 * TSV line formatter
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Tsv implements FormatterInterface
{

    /**
     * Format a single log entry into a TSV line
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    public function format(string $level, string $message, array $context): string
    {
        $message = '"' . str_replace('"', '\"', Context::sanitize($message)) . '"';
        return Context::sanitize($context['timestamp']) . "\t" . $level . "\t" .
            Context::sanitize($context['name']) . "\t" . $message . "\t" .
            Context::sanitize(Context::serialize($context));
    }

}
