<?php

namespace PhpEssence\Component;

use PhpEssence\Exception\DispatcherException;
use PhpEssence\Service;

class Dispatcher extends Service {

    protected $config;

    protected $module;
    protected $controller;
    protected $action;
    protected $params;

    public function setConfig($config) {
        $this->config = $config;
    }

    public function dispatch() {
        $result = $this->fetchRoute();
        if (!$result) {

        }
        $this->lauch();
    }

    public function forward($module, $controller, $action) {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
        $this->lauch();
    }

    protected function lauch() {
        // create controller and call action
        $controllerClass = ucfirst($this->module) . '\\Controller\\' . ucfirst($this->controller) . 'Controller';
        if (!class_exists($controllerClass)) {
            throw new DispatcherException('Controller not found: ' . $controllerClass);
        }
        $action = ucfirst($this->action) . 'Action';
        $controller = new $controllerClass();
        $initResult = $controller->initialize();
        if ($initResult !== false) {
            if (!method_exists($controller, $action)) {
                throw new DispatcherException('Action not found: ' . $action . ' in ' . $controllerClass);
            }
            call_user_func([$controller, $action]);
        }

        $this->sendOutput();
    }

    protected function sendOutput()
    {
        //send output
        $this->getResponse()->send();
        if (!$this->getView()->isDisabled()) {
            $this->getView()->render();
        }
    }

    public function getModule() {
        return $this->module;
    }

    public function getController() {
        return $this->controller;
    }

    public function getAction() {
        return $this->action;
    }

    public function getParam($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }

    protected function fetchRoute()
    {
        $route = $this->getRoute();
        if ($route == false) {
            throw new DispatcherException('Route not found for request: ' . $this->getRequestUri());
        }
        $this->module = $route['module'];
        $this->controller = $route['controller'];
        $this->action = $route['action'];
        return true;
    }

    protected function getRoute() {
        $requestUri = $this->getRequestUri();
        foreach ($this->config as $route) {
            $pattern = '#^' . $route['pattern'] . '$#';
            if (preg_match($pattern, $requestUri, $matches)) {
                $this->params = $matches;
                if (!isset($route['controller'])) {
                    if (!isset($matches['controller'])) {
                        throw new DispatcherException('Route not found for request: ' . $this->getRequestUri());
                    }
                    $route['controller'] = $this->cammelizeFilter($matches['controller']);
                }
                if (!isset($route['action'])) {
                    if (!isset($matches['action'])) {
                        $matches['action'] = 'index';
                    }
                    $route['action'] = $this->cammelizeFilter($matches['action']);
                }
                return $route;
            }
        }
    }

    protected function getRequestUri() {
        return parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH);
    }

    public function isWebApp()
    {
        return php_sapi_name() != 'cli';
    }

    private function cammelizeFilter($name)
    {
        if (strpos($name, '-') === false) {
            return $name;
        }
        $words = explode('-', $name);
        $newName = $words[0];
        for ($i = 1; $i < count($words); $i++) {
            $newName .= ucfirst($words[$i]);
        }
        return $newName;
    }
}
