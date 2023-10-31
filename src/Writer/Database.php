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

use Pop\Db\Adapter\AbstractAdapter;

/**
 * Database log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Database extends AbstractWriter
{

    /**
     * DB adapter
     * @var ?AbstractAdapter
     */
    protected ?AbstractAdapter $db = null;

    /**
     * Table
     * @var string
     */
    protected string $table = 'pop_log';

    /**
     * Constructor
     *
     * Instantiate the DB writer object
     *
     * The DB table requires the following fields at a minimum:
     *     timestamp  DATETIME
     *     level      INT
     *     name       VARCHAR
     *     message    TEXT, VARCHAR, etc.
     *     context    TEXT, VARCHAR, etc.
     *
     * @param  AbstractAdapter $db
     * @param  string          $table
     */
    public function __construct(AbstractAdapter $db, string $table = 'pop_log')
    {
        $this->db = $db;
        $this->setTable($table);

        if (!$db->hasTable($this->table)) {
            $this->createTable();
        }
    }

    /**
     * Get DB
     * @return AbstractAdapter
     */
    public function getDb(): AbstractAdapter
    {
        return $this->db;
    }

    /**
     * Get table
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Set table
     * @parma  string $table
     * @return Database
     */
    public function setTable(string $table): Database
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return Database
     */
    public function writeLog(mixed $level, string $message, array $context = []): Database
    {
        if ($this->isWithinLogLimit($level)) {
            $sql    = $this->db->createSql();
            $fields = [
                'timestamp' => $context['timestamp'],
                'level'     => $level,
                'name'      => $context['name'],
                'message'   => $message,
                'context'   => $this->getContext($context)
            ];

            $columns = [];
            $params  = [];

            $i = 1;
            foreach ($fields as $column => $value) {
                $placeholder = $sql->getPlaceholder();

                if ($placeholder == ':') {
                    $placeholder .= $column;
                } else if ($placeholder == '$') {
                    $placeholder .= $i;
                }
                $columns[$column] = $placeholder;
                $params[$column]  = $value;
                $i++;
            }

            $sql->insert($this->table)->values($columns);

            $this->db->prepare((string)$sql)
                ->bindParams($params)
                ->execute();
        }

        return $this;
    }

    /**
     * Create table in database
     *
     * @return void
     */
    protected function createTable(): void
    {
        $schema = $this->db->createSchema();
        $schema->create($this->table)
            ->int('id')->increment()
            ->datetime('timestamp')
            ->int('level', 1)
            ->varchar('name', 255)
            ->text('message')
            ->text('context')
            ->primary('id');

        $this->db->query($schema);
    }

}
