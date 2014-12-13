<?php
/**
 *
 * @author fsw
 *
 */
define('DS', DIRECTORY_SEPARATOR);
define('NL', PHP_EOL);

function __ ($str) {
    return $str;
}

final class Eve
{

    private static $cacheDir = '';

    private static $libRoots = array();

    private static $vendorRoot;

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

    public static function init ($modules, $settings = []) {
        self::startTimer('other');
        // TODO move to Eve?
        /*if (PHP_SAPI !== 'cli') {
            session_start();
        }*/
        //self::setCacheDir($cachePath);
        $eveRoot = dirname(__FILE__) . DS;
        
        foreach (array_reverse($modules) as $lib) {
            if (strrpos($lib, '/', -strlen($lib)) !== FALSE ) {
                self::$libRoots[] = $lib . DS;
            } else {
                self::$libRoots[] = $eveRoot . $lib . DS;
            }
            
        }
        self::$libRoots[] = $eveRoot . 'core.lib' . DS;
        
        spl_autoload_register(['Eve', 'autoload']);
        
        self::$vendorRoot = $eveRoot . 'vendor' . DS;
        
        register_shutdown_function(['Eve', 'shutdown']);
        
        self::$settings = $settings;
        
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
    
    public static function setting($key) {
        return self::$settings[$key];
    }
    
    public static function executeRequest ($path) {
        foreach (Eve::getDescendants ('Action_Http') as $actionClass) {
            $urlName = lcfirst(str_replace('Action_', '', $actionClass));
            foreach (self::getClassAnnotations($actionClass) as $annotation) {
                if ($annotation instanceof UrlName) {
                    $urlName = $annotation->value;
                }
            }
            $routing[$urlName] = $actionClass;
            $unrouting[$actionClass] = $urlName;
        }
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
                // throw new NotFoundException();
                $className = 'Action_ShowFlatpage';
            }
            //var_dump($className, $bits, $routing);
            $action = new $className();
            foreach (self::getFieldsAnnotations($className) as $field => $annotations) {
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Param) {
                        if ($annotation->value == 'int') {
                            $action->$field = (int) array_shift($bits);
                        } elseif ($annotation->value == 'string') {
                            $action->$field = array_shift($bits);
                        } elseif (is_subclass_of($annotation->value, Entity)) {
                            $action->$field = call_user_func(array(
                                    $annotation->value,
                                    'getByUrlParam'
                            ), array_shift($bits));
                            if (empty($action->$field)) {
                                throw new NotFoundException();
                            }
                        } else {
                            throw new NotFoundException();
                        }
                    }
                }
            }
            if (!empty($bits)) {
                throw new NotFoundException();
            }
    
            $action->run();
        } catch (NotFoundException $e) {
    
            (new Action_404())->run();
        }
        // echo $path;
    }
    
    public static function executeCliCommand ($argv) {
        //action name
        $actionName = array_shift($argv);
        $actions = [];
        foreach (Eve::getDescendants ('Action_Command') as $actionClass) {
            $actions[lcfirst(str_replace('Action_', '', $actionClass))] = $actionClass;
        }
        
        var_dump('KAKAKAKAKAKLA', $actionName, $actions); die();
    }
    
    public static function run ($modules, $settings) {
        Eve::init ($modules, $settings);
        //TODO case cli / built-in server / production mode
        if (PHP_SAPI === 'cli') {
            global $argv;
            //script name
            array_shift($argv);
            Eve::executeCliCommand($argv);
        } else {
            Eve::executeRequest ($_SERVER["REQUEST_URI"]);
        }
    }

    function async ($function) {
        $args = func_get_args();
    }

    public static function getStats () {
            $ret = $_SESSION['stats'];
            $_SESSION['stats'] = [];
            self::$saveStats = false;
            return $ret;
    }

    public static function shutdown () {
        self::stopTimer();
        if (self::$saveStats) {
            if (empty($_SESSION['stats'])) {
                $_SESSION['stats'] = [];
            }
            $_SESSION['stats'][empty($_SERVER['REQUEST_URI']) ? 0 : $_SERVER['REQUEST_URI']] = [
                    self::$timeStats,
                    self::$memStats,
                    self::$events
            ];
        }
    }

    public static function requireVendor ($file) {
        require_once static::$vendorRoot . $file;
    }

    public static function getCacheDir () {
        return self::$cacheDir;
    }

    public static function setCacheDir ($path) {
        self::$cacheDir = $path . DS;
    }

    public static function stackException (Exception $e) {
        self::$exception = $e;
    }

    public static function stackedException () {
        return self::$exception;
    }



    public static function getClassAnnotations ($className) {
        // WAITING FOR:
        // https://wiki.php.net/rfc/annotations
        static::requireVendor('addendum' . DS . 'annotations.php');
        $reflection = new ReflectionAnnotatedClass($className);
        return $reflection->getAllAnnotations();
    }

    public static function getFieldsAnnotations ($className) {
        // TODO array cache
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

    public static function autoload ($className) {
        self::startTimer('autoload');
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

    public static function getClassFileName ($className) {
        //var_dump($className);
        $path = explode('_', $className);
        $baseName = array_pop($path);
        $path = implode(DS, $path);
        foreach (static::$libRoots as $root) {
            $searchFiles[] = $root . 'src' . DS . (empty($path) ? '' : $path . DS) . $baseName . '.php';
            $searchFiles[] = $root . 'src' . DS . (empty($path) ? '' : $path . DS) . $baseName . DS . $baseName . '.php';
        }
        //var_dump($searchFiles);
        foreach ($searchFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
        return null;
    }

    public static function classExists ($className) {
        return class_exists($className) || (self::getClassFileName($className) !== null);
    }

    public static function find ($path) {
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

    public static function findAll ($path) {
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

    public static function listDir ($path) {
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

    /** this function should never be called in a normal application workflow */
    private static function includeAll () {
        if (static::$allIncluded) {
            return true;
        }
        static::$allIncluded = true;
        static::requireVendor('addendum' . DS . 'annotations.php');
        $included = array();
        foreach ($classes = get_declared_traits() + get_declared_classes() as $class) {
            $included[$class] = true;
        }
        foreach (static::$libRoots as $root) {
            $files = Fs::listFiles($root . 'src', true, true);
            //var_dump($root, $files);
            foreach ($files as $file) {
                $relative = substr($file, strlen($root . 'src' . DS));
                if (strpos($relative, '_') === 0) {
                    continue;
                }
                
                $ext = substr(basename($file), strpos(basename($file), '.') + 1);
                if (($ext == 'php') && (strpos($file, '.svn') === false)) {
                    $className = substr($file, strlen($root . 'src' . DS));
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
                    //var_dump($className);
                    if (empty($included[$className])) {
                        //var_dump($file);
                        require $file;
                        $new = array_diff(get_declared_traits() + get_declared_classes(), $classes);
                        //var_dump($new);
                        foreach ($new as $className) {
                            $included[$className] = true;
                        }
                        $classes = array_merge($classes, $new);
                    }
                }
            }
        }
    }

    public static function getDescendants ($class) {
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

    public static function startTimer ($key) {
            if (empty(self::$startTime)) {
                self::$startTime = microtime(true);
            }
            array_push(self::$timers, [
                    $key,
                    microtime(true),
                    memory_get_usage(true)
            ]);
            if (! isset(self::$timeStats[$key])) {
                self::$timeStats[$key] = 0;
                self::$memStats[$key] = 0;
            }
    }

    public static function stopTimer () {
            list ($key, $time, $memory) = array_pop(self::$timers);
            $time = microtime(true) - $time;
            $memory = memory_get_usage(true) - $memory;
            
            self::$timeStats[$key] += $time;
            self::$memStats[$key] += $memory;
            
            foreach (self::$timers as &$timer) {
                $timer[1] += $time;
                $timer[2] += $memory;
            }
    }

    public static function logEvent ($class) {
            $args = func_get_args();
            array_shift($args);
            $start_time = microtime(true) - self::$startTime;
            array_unshift($args, $start_time);
            self::$events[$class][] = $args;
    }
}
