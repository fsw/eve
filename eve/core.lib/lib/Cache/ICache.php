<?php

interface Cache_ICache
{

    public static function set($key, $value, $ttl = 60);

    public static function get($key);

    public static function del($key);
}