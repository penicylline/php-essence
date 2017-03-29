<?php
namespace PhpEssence;

class Application {

    const ENV_DEV = 'dev';
    const ENV_PROD = 'prod';

    protected $env;

    protected $namespaceMap;

    public $debug;

    /**
     * @var Application
     */
    private static $instance;

    /**
     * @return Application
     */
    public static function createApplication($dir, $env = self::ENV_PROD) {
        static::$instance = new Application($dir, $env);
        return static::$instance;
    }

    /**
     * @return Application
     */
    public static function getInstance() {
        return static::$instance;
    }

    protected function __construct($dir, $env) {
        define('APP_DIR', $dir);
        if ($env != static::ENV_PROD) {
            $env = static::ENV_DEV;
        }
        $this->setEnv($env);
        //default namespace map
        $this->namespaceMap = array(
            /*'Model\\' => APP_DIR . '/models',
            'Controller\\' => APP_DIR . '/controllers',*/
        );
        spl_autoload_register(function($class) {
            foreach ($this->namespaceMap as $prefix => $dir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    continue;
                }
                $relative_class = substr($class, $len);
                $file = $dir . '/' . str_replace('\\', '/', $relative_class) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }
            return false;
        });
    }

    public function registerModule($moduleName, $path = null)
    {
        $moduleNS = ucfirst($moduleName);
        if (!$path) {
            $path = APP_DIR . '/modules/'. $moduleName;
        }
        $this->registerNamespace($moduleNS . '\\Controller', $path . '/controllers');
        $this->registerNamespace($moduleNS . '\\Model', $path . '/models');
        return $this;
    }

    public function run() {
        ServiceContainer::getInstance()->get('dispatcher')->dispatch();
    }

    public function registerNamespace($prefix, $basePath) {
        $this->namespaceMap[$prefix] = $basePath;
    }

    public function setEnv($env) {
        $this->env = $env;
        error_reporting(-1);
        if ($env === static::ENV_DEV) {
            if ($this->debug === null) {
                $this->debug = true;
            }
            ini_set('display_errors', 1);
        } elseif ($env === static::ENV_PROD) {
            if ($this->debug === null) {
                $this->debug = false;
            }
            ini_set('display_errors', 0);
            //register error handler
            //new ErrorHandler();
        }
    }

    public function setDebug($debug)
    {
        $this->debug = boolval($debug);
        return $this;
    }

    public function getDebug()
    {
        return $this->debug;
    }
}
