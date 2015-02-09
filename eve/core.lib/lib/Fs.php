<?php

/**
 * Filesystem access.
 * 
 * Set of static function to speed up common file system operations.
 * 
 * @package Core
 * @author fsw
 */
final class Fs
{

    const TYPE_ANY = 0;

    const TYPE_FILE = 1;

    const TYPE_DIR = 2;

    private static $finfoMime = null;

    static function getSize($path)
    {
        return filesize($path);
    }

    static function getMime($path)
    {
        if (! function_exists('finfo_open')) {
            die('SRAKA');
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'css':
                    return 'text/css';
                case 'js':
                    return 'application/x-javascript';
                default:
                    return 'application/octet-stream';
            }
        }
        if (self::$finfoMime === null) {
            self::$finfoMime = finfo_open(FILEINFO_MIME_TYPE);
        }
        return finfo_file(self::$finfoMime, $path);
    }

    static function exists($path)
    {
        return file_exists($path);
    }

    static function isDir($path)
    {
        return is_dir($path);
    }

    static function isFile($path)
    {
        return is_file($path);
    }

    static function mkdir($path, $recursive = false)
    {
        if (! self::isDir($path)) {
            mkdir($path, 0777, true);
        }
    }

    static function read($path)
    {
        return file_get_contents($path);
    }

    static function write($path, $contents)
    {
        return file_put_contents($path, $contents);
    }

    /** Tries to create directory for path
     *
     * @param string $path            
     * @param string $contents            
     * @return bool */
    static function rwrite($path, $contents)
    {
        while (! is_dir(dirname($path))) {
            // TODO rec?
            mkdir(dirname($path), 0777, true);
        }
        return file_put_contents($path, $contents);
    }

    static function rremove($path, $rec = false)
    {
        self::remove($path, $rec);
        
        while ($path = dirname($path)) {
            if (is_dir($path) && (count(scandir($path)) == 0)) {
                rmdir($path);
            } else {
                break;
            }
        }
    }

    static function listFiles($path, $recursive = false, $returnFullPath = false)
    {
        return self::_list($path, self::TYPE_FILE, $recursive, $returnFullPath);
    }

    static function listDirs($path, $recursive = false, $returnFullPath = false)
    {
        return self::_list($path, self::TYPE_DIR, $recursive, $returnFullPath);
    }

    static function listAll($path, $recursive = false, $returnFullPath = false)
    {
        return self::_list($path, self::TYPE_ANY, $recursive, $returnFullPath);
    }

    private static function _is($path, $what)
    {
        return (($what == self::TYPE_ANY) || ($what == self::TYPE_FILE && is_file($path)) ||
                 ($what == self::TYPE_DIR && is_dir($path)));
    }

    private static function _list($path, $what, $recursive = false, $returnFullPath = false)
    {
        $ret = array();
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    if ($recursive && self::isDir($path . DS . $entry)) {
                        $ret = array_merge($ret, self::_list($path . DS . $entry, $what, true, $returnFullPath));
                    }
                    if (self::_is($path . DS . $entry, $what)) {
                        $ret[] = $returnFullPath ? $path . DS . $entry : $entry;
                    }
                }
            }
            closedir($handle);
        }
        return $ret;
    }

    static function copyr($source, $dest, $preprocessFunc = null)
    {
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        
        if (is_file($source)) {
            if (! is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0777, true);
            }
            if ($preprocessFunc !== null) {
                $ret = $preprocessFunc($source);
                if ($ret === true) {
                    return copy($source, $dest);
                } elseif ($ret !== false) {
                    return file_put_contents($dest, $ret);
                } else {
                    return false;
                }
            } else {
                return copy($source, $dest);
            }
        }
        
        if (! is_dir($dest)) {
            mkdir($dest, 0777, true);
        }
        
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            
            self::copyr($source . '/' . $entry, $dest . '/' . $entry, $preprocessFunc);
        }
        $dir->close();
        return true;
    }

    static function remove($path, $rec = false)
    {
        if (is_dir($path)) {
            if ($rec) {
                $dir = dir($path);
                while (false !== $entry = $dir->read()) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    self::remove($path . '/' . $entry, $rec);
                }
                $dir->close();
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}