<?php

/**
 * HTTP Request
 * 
 * @package Core
 * @author fsw
 */
class Request
{

    private $getParams = array();

    private $postParams = array();

    private $domain = '';

    private $pathList = array();

    private $subdomainList = array();

    private $extension = '';

    public function __construct($url = null)
    {
        if (PHP_SAPI === 'cli') {
            global $argv, $argc;
            array_shift($argv);
            $this->pathList = [];
            foreach ($argv as $arg) {
                if (strpos($arg, '=')) {
                    parse_str($arg, $bit);
                    $this->getParams = array_merge_recursive($this->getParams, $bit);
                } else {
                    $this->pathList[] = $arg;
                }
            }
        } else {
            if (! empty($url)) {
                $this->domain = Config::get('site', 'domain');
                $this->subdomainList = array();
                $path = $url;
            } else {
                $this->getParams = $_GET;
                $this->postParams = $_POST;
                
                $path = empty($_SERVER['REDIRECT_URL']) ? '' : $_SERVER['REDIRECT_URL'];
                // TODO
                if (strpos($_SERVER['HTTP_HOST'], Config::get('site', 'domain')) !== 0) {
                    header('Location: http://' . Config::get('site', 'domain') . '/' . $path);
                    exit();
                } else {
                    $this->domain = Config::get('site', 'domain');
                    $this->subdomainList = array();
                    // $this->subdomainList = array_reverse(explode('.',
                    // $_SERVER['HTTP_HOST']));
                    // $this->domain = array_shift($this->subdomainList);
                    // $this->domain = array_shift($this->subdomainList) . '.' .
                    // $this->domain;
                }
            }
            
            if (empty($path) || ($path[strlen($path) - 1] === '/')) {
                $this->pathList = empty($path) || $path === '/' ? array() : explode('/', substr($path, 1, - 1));
                $this->extension = 'html';
            } elseif (substr($path, - 10) === 'index.html') {
                // TODO?
                header('Location: http://' . $_SERVER['HTTP_HOST'] . substr($path, 0, - 10));
                exit();
            } elseif (strrpos($path, '.') <= strrpos($path, '/')) {
                // TODO?
                header('Location: http://' . $_SERVER['HTTP_HOST'] . $path . '.html');
                exit();
            } else {
                $this->extension = substr($path, strrpos($path, '.') + 1);
                $this->pathList = explode('/', substr($path, 1, strrpos($path, '.') - 1));
            }
        }
    }

    public static function getCurrentPageUrl()
    {
        $pageURL = (empty($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != 'on')) ? 'http://' : 'https://';
        if ($_SERVER['SERVER_PORT'] != '80') {
            $pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
        return $pageURL;
    }

    public function getReferer()
    {
        return empty($_SERVER['HTTP_REFERER']) ? Site::lt() : $_SERVER['HTTP_REFERER'];
    }

    public function getType()
    {
        return PHP_SAPI;
        // === 'cli')
    }

    /* public static function serialize()
     * {
     * return array('s' => $this->subdomainStack, 'p' => $this->pathStack, 'g'
     * => $this->getParams);
     * } */
    public function domain()
    {
        return $this->domain;
    }

    public function extension()
    {
        return $this->extension;
    }

    public function getParams()
    {
        return $this->getParams;
    }

    public function getParam()
    {
        $args = func_get_args();
        $ret = & $this->getParams;
        for ($i = 0; $i < count($args); $i ++)
            if (isset($ret[$args[$i]]))
                $ret = & $ret[$args[$i]];
            else
                return null;
        return $ret;
    }

    public function postParams()
    {
        return $this->postParams;
    }

    public function postParam()
    {
        $args = func_get_args();
        $ret = & $this->postParams;
        for ($i = 0; $i < count($args); $i ++)
            if (isset($ret[$args[$i]]))
                $ret = & $ret[$args[$i]];
            else
                return null;
        return $ret;
    }

    public function getPath()
    {
        return $this->pathList;
    }

    public function getPathElem($offset)
    {
        return isset($this->pathList[$offset]) ? $this->pathList[$offset] : null;
    }

    public function shiftPath()
    {
        return array_shift($this->pathList);
    }

    public function glancePath()
    {
        return $this->getPathElem(0);
    }

    public function isPathEmpty()
    {
        return empty($this->pathList);
    }

    public function getSubdomain()
    {
        return $this->subdomainList;
    }

    public function getSubdomainElem($offset)
    {
        return isset($this->subdomainList[$offset]) ? $this->subdomainList[$offset] : null;
    }

    public function shiftSubdomain()
    {
        return array_shift($this->subdomainList);
    }

    public function isAjax()
    {
        return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public static function isLocalHost()
    {
        return $_SERVER['SERVER_ADDR'] === $_SERVER['REMOTE_ADDR'];
    }

    public static function getClientIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 
                'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return null;
    }
}
