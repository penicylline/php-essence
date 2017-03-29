<?php

namespace PhpEssence\Component;

use PhpEssence\Service;

class Request extends Service {

    public function get($paramName) {
        return isset($_GET[$paramName]) ? $_GET[$paramName]: null;
    }

    public function getPost($paramName = null) {
        if ($paramName === null) {
            return $_POST;
        }
        return isset($_POST[$paramName]) ? $_POST[$paramName]: null;
    }

    public function hasGet($paramName) {
        return array_key_exists($paramName, $_GET);
    }

    public function hasPost($paramName) {
        return array_key_exists($paramName, $_POST);
    }

    public function getRequestType()
    {
        return strtoupper(filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
    }

    public function isPost() {
        return $this->getRequestType() === 'POST';
    }

    public function isGet() {
        return $this->getRequestType() === 'GET';
    }

    public function isAjax() {
        return strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    public function getCookie($key) {
        return filter_input(INPUT_COOKIE, $key);
    }

    public function getCurrentUrl() {
        $url = $_SERVER['REQUEST_URI'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }

    function getClientIp() {
        $ipaddress = 'UNKNOWN';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        return $ipaddress;
    }
}
