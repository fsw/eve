<?php

/**
 * @package Core
 * @author fsw
 */
class Template
{

    protected $____path = null;

    protected $____data = array();

    public function __construct($path, Array $data = null)
    {
        $this->____path = $path;
        if (! empty($data)) {
            $this->____data = $data;
        }
    }

    public function __set($key, $value)
    {
        $this->____data[$key] = $value;
    }

    private static function quote($var)
    {
        if (is_string($var)) {
            return htmlspecialchars($var, ENT_COMPAT);
        } elseif (is_array($var)) {
            foreach ($var as &$val) {
                $val = self::quote($val);
            }
            return $var;
        } else {
            return $var;
        }
    }

    public function __get($key)
    {
        if (! array_key_exists($key, $this->____data)) {
            throw new Exception('Unset variable: ' . $key . ' in template: ' . $this->____path);
        }
        return self::quote($this->____data[$key]);
    }

    public function unsecured($key)
    {
        if (! array_key_exists($key, $this->____data)) {
            throw new Exception('Unset variable: ' . $key . ' in template: ' . $this->____path);
        }
        return $this->____data[$key];
    }

    public static function exists($path)
    {
        return file_exists(Eve::getCacheDir() . 'templates' . DS . $path . '.php') || Eve::find($path) ||
                 Eve::find($path . '.php') || Eve::find($path . '.haml');
    }

    public function __toString()
    {
        Eve::startTimer('render');
        try {
            $cachepath = Eve::getCacheDir() . 'templates' . DS . $this->____path . '.php';
            if (! Eve::useCache('core') || ! file_exists($cachepath)) {
                if ($path = Eve::find($this->____path)) {
                    $contents = Fs::read($path);
                } elseif ($path = Eve::find($this->____path . '.php')) {
                    $contents = Fs::read($path);
                } elseif ($path = Eve::find($this->____path . '.haml')) {
                    Eve::requireVendor('phamlp/haml/HamlParser.php');
                    $haml = new HamlParser([]);
                    $contents = $haml->parse($path);
                } else {
                    throw new Exception('Template "' . $this->____path . '" not found.');
                }
                if (Eve::useCache('core')) {
                    Fs::rwrite($cachepath, $contents);
                }
            }
            ob_start();
            if (Eve::useCache('core')) {
                require $cachepath;
            } else {
                eval('?>' . $contents . '<?php ');
            }
            $ret = ob_get_clean();
        } catch (Exception $e) {
            Eve::stackException($e);
            return '';
        }
        Eve::stopTimer();
        return $ret;
    }
}
