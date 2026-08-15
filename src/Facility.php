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

/**
 * Syslog facility enum (RFC-3164 §4.1.1, Table 1)
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
enum Facility: int
{
    case KERNEL   = 0;
    case USER     = 1;
    case MAIL     = 2;
    case DAEMON   = 3;
    case AUTH     = 4;
    case SYSLOG   = 5;
    case LPR      = 6;
    case NEWS     = 7;
    case UUCP     = 8;
    case CRON     = 9;
    case AUTHPRIV = 10;
    case FTP      = 11;
    case NTP      = 12;
    case LOGAUDIT = 13;
    case LOGALERT = 14;
    case CLOCK    = 15;
    case LOCAL0   = 16;
    case LOCAL1   = 17;
    case LOCAL2   = 18;
    case LOCAL3   = 19;
    case LOCAL4   = 20;
    case LOCAL5   = 21;
    case LOCAL6   = 22;
    case LOCAL7   = 23;
}
