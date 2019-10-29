<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Writer;

use Pop\Http\Client\Stream;

/**
 * Http log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
class Http extends AbstractWriter
{

    /**
     * Stream object
     * @var Stream
     */
    protected $stream = null;

    /**
     * Constructor
     *
     * Instantiate the Mail writer object
     *
     * @param  Stream $stream
     * @throws Exception
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return Http
     */
    public function writeLog($level, $message, array $context = [])
    {
        if ($this->isWithinLogLimit($level)) {
            $timestamp = $context['timestamp'];
            $name      = $context['name'];

            unset($context['timestamp']);
            unset($context['name']);

            $this->stream->setFields([
                'timestamp' => $timestamp,
                'level'     => $level,
                'name'      => $name,
                'message'   => $message,
                'context'   => json_encode($context)
            ]);

            $this->stream->send();
        }

        return $this;
    }

}
