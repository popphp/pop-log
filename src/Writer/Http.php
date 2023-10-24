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

use Pop\Http\Client;

/**
 * Http log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Http extends AbstractWriter
{

    /**
     * Stream object
     * @var ?Client
     */
    protected ?Client $client = null;

    /**
     * Constructor
     *
     * Instantiate the Mail writer object
     *
     * @param  Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return Http
     */
    public function writeLog(mixed $level, string $message, array $context = []): Http
    {
        if ($this->isWithinLogLimit($level)) {
            $timestamp = $context['timestamp'];
            $name      = $context['name'];

            unset($context['timestamp']);
            unset($context['name']);

            $this->client->setData([
                'timestamp' => $timestamp,
                'level'     => $level,
                'name'      => $name,
                'message'   => $message,
                'context'   => $context
            ]);

            $this->client->send();
        }

        return $this;
    }

}
