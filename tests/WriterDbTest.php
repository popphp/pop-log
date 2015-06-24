<?php

namespace Pop\Log\Test;

use Pop\Log\Writer\Db;
use Pop\Db\Db as D;
use Pop\Db\Sql;

class WriterDbTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $db     = D::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $sql    = new Sql($db);
        $writer = new Db($sql, 'logs');
        $this->assertInstanceOf('Pop\Log\Writer\Db', $writer);
    }

    public function testConstructorNoTableException()
    {
        $this->setExpectedException('Pop\Log\Writer\Exception');
        $db     = D::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $sql    = new Sql($db);
        $writer = new Db($sql);
    }

    public function testWrite()
    {
        $db     = D::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $sql    = new Sql($db);
        $writer = new Db($sql, 'logs');

        $writer->writeLog([
            'timestamp' => date('Y-m-d H:i:s'),
            'priority'  => 5,
            'name'      => 'NOTICE',
            'message'   => 'This is a database test.'
        ]);

        $db->query('SELECT * FROM logs');
        $rows = [];
        while (($row = $db->fetch())) {
            $rows[] = $row;
        }
        $this->assertEquals('This is a database test.', $rows[0]['message']);
    }

}
