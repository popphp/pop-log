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

    public function testLog()
    {
        $db     = D::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $sql    = new Sql($db);
        $writer = new Db($sql, 'logs');

        $writer->writeLog(5, 'This is a database test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $db->query('SELECT * FROM logs');
        $rows = [];
        while (($row = $db->fetch())) {
            $rows[] = $row;
        }
        $this->assertEquals('This is a database test.', $rows[0]['message']);
    }

    public function testCustomLog()
    {
        $db     = D::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $sql    = new Sql($db);
        $writer = new Db($sql, 'logs');
        $writer->writeCustomLog('This is a custom log test.');
        $this->assertInstanceOf('Pop\Log\Writer\Db', $writer);
    }

}
