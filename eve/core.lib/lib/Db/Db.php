<?php

/**
 * Database access.
 * 
 * Instance of this class represents one database connection.
 * Connect only when necessairy so can be constructed at application start
 * 
 * @package Core
 * @author fsw
 */
class Db
{

    private $config = null;

    private $reader = null;

    private $writer = null;

    /**
     * 
     * @param Array $config
     *            database connection parameters
     *            
     *            Example usage:
     *            @code
     *            new Db(['dsn' => DSN, 'user' => USER, 'pass' => PASS]); //to
     *            create single connection
     *            new Db([
     *            ['dsn' => DSN, 'user' => USER, 'pass' => PASS, 'write' =>
     *            true], //master params
     *            ['dsn' => DSN, 'user' => USER, 'pass' => PASS, 'write' =>
     *            false, weight => 4], // slave 1 params
     *            ['dsn' => DSN, 'user' => USER, 'pass' => PASS, 'write' =>
     *            false, weight => 1], // slave 2 params
     *            ]);
     *            @endcode */
    public function __construct(Array $config = array())
    {
        // TODO slaves!
        $this->config = $config;
    }

    private function getReader()
    {
        if ($this->writer !== null) {
            return $this->writer;
        }
        if ($this->reader === null) {
            // TODO
            $config = $this->config;
            if (! empty($config[0]) && is_array($config[0])) {
                foreach ($config as $k => $c) {
                    if (is_numeric($k)) {
                        $config = $c;
                        break;
                    }
                }
            }
            $this->reader = new PDO($config['dsn'], $config['user'], $config['pass'], 
                    array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            $this->reader->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->reader;
    }

    private function getWriter()
    {
        if ($this->writer === null) {
            // TODO
            $config = $this->config;
            if (! empty($config[0]) && is_array($config[0])) {
                foreach ($config as $c) {
                    if ($c['write']) {
                        $config = $c;
                        break;
                    }
                }
            }
            $this->writer = new PDO($config['dsn'], $config['user'], $config['pass'], 
                    array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            $this->writer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->writer;
    }

    public function fetchAll($sql, $bind = array())
    {
        Eve::startTimer('db');
        $sth = $this->getReader()->prepare($sql);
        $sth->execute($bind);
        $ret = $sth->fetchAll(PDO::FETCH_ASSOC);
        Eve::logEvent('db', $sql, $bind, $ret);
        Eve::stopTimer();
        return $ret;
    }

    public function fetchOne($sql, $bind = array())
    {
        Eve::startTimer('db');
        $sth = $this->getReader()->prepare($sql);
        $sth->execute($bind);
        $ret = $sth->fetchColumn();
        Eve::logEvent('db', $sql, $bind, $ret);
        Eve::stopTimer();
        return $ret;
    }

    public function fetchRow($sql, $bind = array())
    {
        // Eve::startTimer('db');
        $sth = $this->getReader()->prepare($sql);
        $sth->execute($bind);
        $ret = $sth->fetch(PDO::FETCH_ASSOC);
        // Eve::logEvent('db', $sql, $bind, $ret);
        // Eve::stopTimer();
        return $ret;
    }

    public function fetchCol($sql, $bind = array())
    {
        Eve::startTimer('db');
        $sth = $this->getReader()->prepare($sql);
        $sth->execute($bind);
        $ret = $sth->fetchAll(PDO::FETCH_COLUMN);
        Eve::logEvent('db', $sql, $bind, $ret);
        Eve::stopTimer('db');
        return $ret;
    }

    public function quote($value)
    {
        return $this->getReader()->quote($value);
    }

    public function query($sql, $bind = array())
    {
        Eve::startTimer('db');
        $q = $this->getWriter()->prepare($sql);
        $q->execute($bind);
        Eve::logEvent('db', $sql, $bind);
        Eve::stopTimer();
    }

    public static function toSet($data)
    {
        $keys = array_keys($data);
        foreach ($keys as &$key) {
            $key = '`' . $key . '`=?';
        }
        return implode(',', $keys);
    }

    public static function notIn($field, $list)
    {
        if (empty($list)) {
            return '1';
        }
        return $field . ' NOT IN (' . implode(',', array_fill(0, count($list), '?')) . ')';
    }

    public static function in($field, $list)
    {
        if (empty($list)) {
            return '0';
        }
        return $field . ' IN (' . implode(',', array_fill(0, count($list), '?')) . ')';
    }

    public function insert($table, $data)
    {
        $q = 'INSERT INTO `' . $table . '` SET ';
        $q .= self::toSet($data);
        $this->query($q, array_values($data));
    }

    public function lastInsertId()
    {
        return $this->getWriter()->lastInsertId();
    }

    public function update($table, $id, $data)
    {
        $q = 'UPDATE `' . $table . '` SET ';
        $q .= self::toSet($data);
        $q .= ' WHERE id = ' . $id;
        $this->query($q, array_values($data));
    }

    public function delete($table, $id)
    {
        $this->query('DELETE FROM `' . $table . '` WHERE id = ' . $id);
    }
}
