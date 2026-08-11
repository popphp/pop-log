<?php
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

use Pop\Log\Facility;
use Pop\Log\Level;

/**
 * Syslog log writer class
 *
 * Builds a real RFC-3164 packet (<PRI>HEADER TAG: MSG) and sends it over UDP to a syslog
 * daemon/collector. Does not use Logger's configurable timestampFormat — RFC-3164 mandates
 * an exact HEADER timestamp shape, generated independently on every write.
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Syslog extends AbstractWriter
{

    /**
     * UDP socket resource
     * @var mixed
     */
    protected mixed $socket = null;

    /**
     * Tag (program identifier) written into the syslog TAG field
     * @var string
     */
    protected string $tag;

    /**
     * Hostname written into the syslog HEADER
     * @var string
     */
    protected string $hostname;

    /**
     * Constructor
     *
     * Instantiate the syslog writer object
     *
     * @param  Facility $facility
     * @param  string   $host
     * @param  int      $port
     * @param  ?string  $tag
     * @param  ?string  $hostname
     * @param  bool     $includePid
     * @throws Exception
     */
    public function __construct(
        protected Facility $facility   = Facility::USER,
        protected string   $host       = '127.0.0.1',
        protected int      $port       = 514,
        ?string             $tag        = null,
        ?string             $hostname   = null,
        protected bool      $includePid = true
    ) {
        $this->tag      = $tag ?? basename($_SERVER['SCRIPT_NAME'] ?? $_SERVER['argv'][0] ?? 'php');
        $this->hostname = $hostname ?? (gethostname() ?: 'localhost');

        // RFC-3164 4.1.2/4.1.3: HOSTNAME must not contain whitespace, TAG must be <= 32 chars with no whitespace/control chars
        $this->tag      = substr(preg_replace('/[\s\x00-\x1F\x7F]+/', '', (string)$this->tag), 0, 32);
        $this->hostname = preg_replace('/[\s\x00-\x1F\x7F]+/', '', (string)$this->hostname);

        $socket = @stream_socket_client('udp://' . $this->host . ':' . $this->port, $errno, $errstr);

        if ($socket === false) {
            throw new Exception('Unable to open UDP socket to ' . $this->host . ':' . $this->port . ': ' . $errstr);
        }

        $this->socket = $socket;
    }

    /**
     * Get facility
     * @return Facility
     */
    public function getFacility(): Facility
    {
        return $this->facility;
    }

    /**
     * Get tag
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Get hostname
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Write to the log
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return Syslog
     */
    public function writeLog(string $level, string $message, array $context = []): Syslog
    {
        if ($this->isWithinLogLimit($level)) {
            $pri = ($this->facility->value * 8) + Level::toSeverity($level);

            // RFC-3164 HEADER timestamp: "Mmm dd hh:mm:ss" - day is space-padded, not zero-padded
            $now       = time();
            $timestamp = sprintf('%s %2d %s', date('M', $now), (int)date('j', $now), date('H:i:s', $now));

            $tagPart = $this->tag . ($this->includePid ? '[' . getmypid() . ']' : '');
            $message = $this->sanitize($message);
            $ctx     = $this->sanitize($this->getContext($context));
            $body    = $message . ($ctx !== '' ? ' ' . $ctx : '');

            $packet = '<' . $pri . '>' . $timestamp . ' ' . $this->hostname . ' ' . $tagPart . ': ' . $body;

            if (strlen($packet) > 1024) {
                $packet = mb_strcut($packet, 0, 1024);
            }

            fwrite($this->socket, $packet);
        }

        return $this;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

}
