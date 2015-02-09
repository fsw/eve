<?php
/**
 *
 * @author fsw
 *
 */
define('DS', DIRECTORY_SEPARATOR);
define('NL', PHP_EOL);

function __($str)
{
    return $str;
}

final class Eve
{

    private static $appRoot = '';
    private static $eveRoot = '';
    private static $vendorRoot = '';
    private static $cacheDir = '';

    private static $libRoots = [];

    private static $exception = null;
    // statistics:
    private static $timers = array();
    private static $startTime = 0;
    private static $timeStats = array();
    private static $memStats = array();
    private static $events = array();
    private static $saveStats = true;
    private static $allIncluded = false;

    private static $settings;

    /** @Cache_Apc */
    public static function config($section)
    {
        $config = Cache_Apc::get('config');
        if ($config === null) {
            $config = parse_ini_file(self::$appRoot . DS . 'config.ini', true);
            //var_dump($config);
            //TODO load configs from libs
            Cache_Apc::set('config', $config);
        }
        return $config[$section];
    }
    
    public static function init($root)
    {
        spl_autoload_register(['Eve', 'autoload']);
        self::$appRoot = $root . DS;
        self::$eveRoot = dirname(__FILE__) . DS;
        self::$libRoots[] = self::$eveRoot . 'core.lib' . DS;
        
        self::startTimer('other');
        // TODO move to Eve?
        /* if (PHP_SAPI !== 'cli') {
         * session_start();
         * } */
        // self::setCacheDir($cachePath);
        foreach (Eve::config('libs')['libs'] as $lib) {
            if (strrpos($lib, '/', - strlen($lib)) !== FALSE) {
                self::$libRoots[] = $lib . DS;
            } else {
                self::$libRoots[] = self::$eveRoot . $lib . DS;
            }
        }
        self::$libRoots[] = self::$appRoot;
        self::$libRoots = array_reverse(self::$libRoots);
        
        self::$vendorRoot = self::$eveRoot . 'vendor' . DS;
        
        register_shutdown_function(['Eve', 'shutdown']);
        
        // self::$errorHandler = new ErrorHandler();
        // TODO
        /* if (get_magic_quotes_gpc())
         * {
         * function stripslashes_gpc(&$value)
         * {
         * $value = stripslashes($value);
         * }
         * array_walk_recursive($_GET, 'stripslashes_gpc');
         * array_walk_recursive($_POST, 'stripslashes_gpc');
         * array_walk_recursive($_COOKIE, 'stripslashes_gpc');
         * array_walk_recursive($_REQUEST, 'stripslashes_gpc');
         * } */
    }

    public static function executeRequest($path)
    {
        foreach (Eve::getDescendants('Action_Http') as $actionClass) {
            $urlName = lcfirst(str_replace('Action_', '', $actionClass));
            foreach (self::getClassAnnotations($actionClass) as $annotation) {
                if ($annotation instanceof UrlName) {
                    $urlName = $annotation->value;
                }
            }
            $routing[$urlName] = $actionClass;
            $unrouting[$actionClass] = $urlName;
        }
        // var_dump($routing);
        
        if (strrpos($path, '?')) {
            $path = substr($path, 0, strrpos($path, '?'));
        }
        
        try {
            // TODO check if http or what
            $bits = explode('/', $path);
            array_shift($bits);
            
            if (isset($routing[$bits[0]])) {
                $className = $routing[array_shift($bits)];
            } else {
                throw new NotFoundException();
                // $className = 'Action_ShowFlatpage';
            }
            // var_dump($className);
            
            $action = new $className();
            foreach (self::getFieldsAnnotations($className) as $field => $annotations) {
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Param) {
                        // var_dump($annotation);
                        if ($annotation->fullPath) {
                            $action->$field = implode('/', $bits);
                            $bits = [];
                        } else {
                            $value = count($bits) > 0 ? array_shift($bits) : $annotation->default;
                            if ($annotation->type == 'int') {
                                $action->$field = (int) $value;
                            } elseif ($annotation->type == 'string') {
                                $action->$field = $value;
                            } elseif (is_subclass_of($annotation->type, 'Entity')) {
                                // var_dump($annotation);
                                $action->$field = call_user_func([$annotation->type, 'getByUrlParam'], $value);
                                if (empty($action->$field)) {
                                    throw new NotFoundException();
                                }
                            } else {
                                throw new NotFoundException();
                            }
                        }
                    }
                }
            }
            // var_dump($className, $bits, $routing);
            if (! empty($bits)) {
                throw new NotFoundException();
            }
            
            $action->run();
        } catch (NotFoundException $e) {
            (new Exception404())->run();
        }
        // echo $path;
    }

    public static function executeCliCommand($argv)
    {
        $scriptName = array_shift($argv);
        $actions = [];
        foreach (Eve::getDescendants('Action_Command') as $actionClass) {
            $actions[$actionClass::getShortName()] = $actionClass;
        }
        $actionName = array_shift($argv);
        if (empty($actionName) || ! isset($actions[$actionName])) {
            print "usage:\n";
            print "$ php $scriptName <action> [parameters...]\n";
            print "available actions:\n";
            foreach ($actions as $actionClass) {
                print $actionClass::getShortHelp();
            }
        } else {
            $action = new $actions[$actionName]();
            $action->executeByArgv($argv);
        }
    }

    public static function run($root)
    {
        Eve::init($root);
        // TODO case cli / built-in server / production mode
        if (PHP_SAPI === 'cli') {
            global $argv;
            // script name
            Eve::executeCliCommand($argv);
        } elseif (PHP_SAPI === 'cli-server') {
            Eve::executeRequest($_SERVER["REQUEST_URI"]);
        } else {
            Eve::executeRequest($_SERVER["REQUEST_URI"]);
        }
    }

    function async($function)
    {
        $args = func_get_args();
    }

    public static function getStats()
    {
        $ret = $_SESSION['stats'];
        $_SESSION['stats'] = [];
        self::$saveStats = false;
        return $ret;
    }

    public static function shutdown()
    {
        self::stopTimer();
        if (self::$saveStats) {
            if (empty($_SESSION['stats'])) {
                $_SESSION['stats'] = [];
            }
            $_SESSION['stats'][empty($_SERVER['REQUEST_URI']) ? 0 : $_SERVER['REQUEST_URI']] = [self::$timeStats, 
                    self::$memStats, self::$events];
        }
    }

    public static function requireVendor($file)
    {
        require_once static::$vendorRoot . $file;
    }

    public static function getCacheDir()
    {
        return self::$cacheDir;
    }

    public static function setCacheDir($path)
    {
        self::$cacheDir = $path . DS;
    }

    public static function stackException(Exception $e)
    {
        self::$exception = $e;
    }

    public static function stackedException()
    {
        return self::$exception;
    }

    public static function getClassAnnotations($className)
    {
        // WAITING FOR:
        // https://wiki.php.net/rfc/annotations
        static::requireVendor('addendum' . DS . 'annotations.php');
        $reflection = new ReflectionAnnotatedClass($className);
        return $reflection->getAllAnnotations();
    }

    public static function getFieldsAnnotations($className)
    {
        // TODO apc
        $return = array();
        // WAITING FOR:
        // https://wiki.php.net/rfc/annotations
        static::requireVendor('addendum' . DS . 'annotations.php');
        // $reflection = new ReflectionAnnotatedClass($className); // by class
        // name
        $reflect = new ReflectionClass($className);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        foreach ($props as $prop) {
            // print $prop->getName() . "\n";
            $reflection = new ReflectionAnnotatedProperty($className, $prop->getName()); // by
                                                                                         // class
                                                                                         // name
            $annotations = $reflection->getAllAnnotations();
            if (! empty($annotations)) {
                $return[$prop->getName()] = $annotations;
            }
        }
        return $return;
    }

    public static function autoload($className)
    {
        self::startTimer('autoload');
        
        // dirty hack to include twig files TODO benchmark
        /* if (strpos($className, 'Twig') === 0 ) {
         * if (is_file($file = dirname(__FILE__).'/../'.str_replace(array('_',
         * "\0"), array('/', ''), $className).'.php')) {
         * require $file;
         * }
         * } */
        
        self::logEvent('autoload', $className);
        if (false && file_exists($path = self::$cacheDir . 'classes' . DS . $className . '.php')) {
            require $path;
        } else {
            $file = self::getClassFileName($className);
            if ($file !== null) {
                require $file;
                if (false && ! empty(self::$useCache['core'])) {
                    // save to cache
                    copy($file, Eve::getCacheDir() . 'classes' . DS . $className . '.php');
                }
            }
        }
        self::stopTimer();
    }

    public static function getClassFileName($className)
    {
        // var_dump($className);
        $path = explode('_', $className);
        $baseName = array_pop($path);
        $path = implode(DS, $path);
        
        foreach (static::$libRoots as $root) {
            foreach (['actions', 'model', 'lib'] as $srcDir) {
                $searchFiles[] = $root . $srcDir . DS . (empty($path) ? '' : $path . DS) . $baseName . '.php';
                $searchFiles[] = $root . $srcDir . DS . (empty($path) ? '' : $path . DS) . $baseName . DS . $baseName .
                         '.php';
            }
        }
        // var_dump($searchFiles);
        foreach ($searchFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
        return null;
    }

    public static function classExists($className)
    {
        return class_exists($className) || (self::getClassFileName($className) !== null);
    }

    public static function findFile($path)
    {
        self::startTimer('resourceloader');
        // just for windows sake
        $path = str_replace('/', DS, $path);
        $ret = null;
        foreach (static::$libRoots as $root) {
            if (file_exists($root . $path)) {
                $ret = $root . $path;
                break;
            }
        }
        self::stopTimer();
        return $ret;
    }

    public static function getLibRoots()
    {
        return static::$libRoots;
    }

    public static function findAll($path)
    {
        self::startTimer('resourceloader');
        // just for windows sake
        $path = str_replace('/', DS, $path);
        $ret = array();
        foreach (static::$libRoots as $root) {
            if (file_exists($root . $path)) {
                $ret[] = $root . $path;
            }
        }
        self::stopTimer();
        return $ret;
    }

    public static function listDir($path)
    {
        self::startTimer('resourceloader');
        // just for windows sake
        $path = str_replace('/', DS, $path);
        $ret = [];
        foreach (static::$libRoots as $root) {
            if (is_dir($root . $path)) {
                $all = Fs::listAll($root . $path);
                foreach ($all as $file) {
                    $ret[$file] = true;
                }
            }
        }
        self::stopTimer();
        return array_keys($ret);
    }

    private static function includeAll()
    {
        if (static::$allIncluded) {
            return true;
        }
        static::$allIncluded = true;
        static::requireVendor('addendum' . DS . 'annotations.php');
        $includedClasses = array_merge(get_declared_traits(), get_declared_classes(), get_declared_interfaces());
        foreach (static::$libRoots as $root) {
            foreach (['actions', 'model', 'lib'] as $srcDir) {
                if (Fs::exists($root . $srcDir)) {
                    $files = Fs::listFiles($root . $srcDir, true, true);
                    // var_dump($root, $files);
                    foreach ($files as $file) {
                        $relative = substr($file, strlen($root . $srcDir . DS));
                        if (strpos($relative, '_') === 0) {
                            continue;
                        }
                        
                        $ext = substr(basename($file), strpos(basename($file), '.') + 1);
                        if (($ext == 'php') && (strpos($file, '.svn') === false)) {
                            $className = substr($file, strlen($root . $srcDir . DS));
                            $className = substr($className, 0, strrpos($className, '.php'));
                            $className = explode('/', $className);
                            if (count($className) > 1) {
                                $last = array_pop($className);
                                $prev = array_pop($className);
                                if (ucfirst($prev) == $last) {
                                    $className[] = $last;
                                } else {
                                    $className[] = $prev;
                                    $className[] = $last;
                                }
                            }
                            $className = implode('_', $className);
                            if (!in_array($className, $includedClasses)) {
                                // var_dump($file);
                                require $file;
                                $includedClasses = array_merge(get_declared_traits(), get_declared_classes(), get_declared_interfaces());
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getDescendants($class)
    {
        self::startTimer('classtools');
        $ret = Cache_Apc::get('descendants/' . $class);
        if ($ret === null) {
            static::includeAll();
            $ret = array();
            foreach (get_declared_classes() as $c) {
                if (is_subclass_of($c, $class)) {
                    $r = new ReflectionClass($c);
                    if (! $r->isAbstract()) {
                        $ret[] = $c;
                    }
                }
            }
            Cache_Apc::set('descendants/' . $class, $ret);
        }
        self::stopTimer();
        return $ret;
    }

    public static function startTimer($key)
    {
        if (empty(self::$startTime)) {
            self::$startTime = microtime(true);
        }
        array_push(self::$timers, [$key, microtime(true), memory_get_usage(true)]);
        if (! isset(self::$timeStats[$key])) {
            self::$timeStats[$key] = 0;
            self::$memStats[$key] = 0;
        }
    }

    public static function stopTimer()
    {
        list($key, $time, $memory) = array_pop(self::$timers);
        $time = microtime(true) - $time;
        $memory = memory_get_usage(true) - $memory;
        
        self::$timeStats[$key] += $time;
        self::$memStats[$key] += $memory;
        
        foreach (self::$timers as &$timer) {
            $timer[1] += $time;
            $timer[2] += $memory;
        }
    }

    public static function logEvent($class)
    {
        $args = func_get_args();
        array_shift($args);
        $start_time = microtime(true) - self::$startTime;
        array_unshift($args, $start_time);
        self::$events[$class][] = $args;
    }
}
