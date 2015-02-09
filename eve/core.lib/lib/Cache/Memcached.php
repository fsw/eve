<?php

/**
 * Memcached.
 * 
 * @package Core
 * @author fsw
 */
class Cache_Memcached
{

    private static $connection = null;

    private static function getConnection()
    {
        if (self::$connection == null) {
            $connection = new Memcached();
            $connection->addServer('localhost', 11211);
        }
        return $connection;
    }

    public static function set($key, $value)
    {
        Eve::startTimer('memcached');
        if (Eve::useCache('memcached')) {
            self::getConnection()->set($key, $value);
        }
        Eve::stopTimer();
    }

    public static function get($key)
    {
        Eve::startTimer('memcached');
        if (Eve::useCache('memcached')) {
            $ret = self::getConnection()->get($key);
            Eve::logEvent('memcached', $key, $ret);
        } else {
            $ret = false;
        }
        Eve::stopTimer();
        return $ret === false ? null : $ret;
    }

    public static function del($key)
    {
        Eve::startTimer('memcached');
        if (Eve::useCache('memcached')) {
            $ret = self::getConnection()->delete($key);
        }
        Eve::stopTimer();
    }
}
