<?php
namespace PhpEssence;

use PhpEssence\Component\CliDispatcher;
use PhpEssence\Component\Dispatcher;
use PhpEssence\Component\Request;
use PhpEssence\Component\Response;
use PhpEssence\Helper\Tag;
use PhpEssence\Helper\Url;
use PhpEssence\Mvc\View;

class ServiceContainer {

    /**
     * @var ServiceContainer
     */
    static protected $instance;

    protected $container;
    protected $raw;

    private function __construct() {
        $this->container = array();
        $this->raw = array();
    }

    public static function getInstance() {
        if (static::$instance === null) {
            static::$instance = new ServiceContainer();
        }
        return static::$instance;
    }

    public static function createDefaultForCli()
    {
        static::$instance = new ServiceContainer();
        static::$instance->set(
            'dispatcher',
            function(){
                return new CliDispatcher();
            }
        );
        static::$instance->set(
            'tag',
            function(){
                return new Tag();
            }
        );
        return static::$instance;
    }

    public static function createDefault()
    {
        static::$instance = new ServiceContainer();
        static::$instance->set(
            'request',
            function(){
                return new Request();
            }
        );
        static::$instance->set(
            'response',
            function(){
                return new Response();
            }
        );
        static::$instance->set(
            'dispatcher',
            function(){
                return new Dispatcher();
            }
        );
        static::$instance->set(
            'tag',
            function(){
                return new Tag();
            }
        );
        static::$instance->set(
            'url',
            function(){
                return new Url();
            }
        );
        static::$instance->set(
            'view',
            function(){
                return new View();
            }
        );
        return static::$instance;
    }

    public function set($name, $obj) {
        $this->raw[$name] = $obj;
        unset($this->container[$name]);
    }

    public function get($name) {
        if (!array_key_exists($name, $this->container)) {
            $this->container[$name] = $this->fetch($name);
        }
        return $this->container[$name];
    }

    protected function fetch($name) {
        if (!isset($this->raw[$name])) {
            return null;
        }
        if (is_callable($this->raw[$name])) {
            return $this->raw[$name]();
        }
        return $this->raw[$name];
    }
}
