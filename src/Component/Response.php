<?php

namespace PhpEssence\Component;

use PhpEssence\Service;

class Response extends Service {

    protected $headers;
    protected $cookies;
    protected $content;
    protected $responseCode;

    public function  __construct() {
        parent::__construct();
        $this->headers = array();
        $this->cookies = array();
    }

    public function setHeader($name, $value = null) {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setCookie($name, $value, $expired = 0, $path = '/') {
        $this->cookies[$name] = array(
            'value' => $value,
            'expired' => $expired,
            'path' => $path
        );
        return $this;
    }

    public function setResponseCode($code) {
        $this->responseCode = $code;
        return $this;
    }

    public function setContent($data) {
        if (is_scalar($data)) {
            return $this->content = $data;
        }
        $this->content = json_encode($data);
        return $this;
    }

    public function setAjaxContent($data) {
        $this->getView()->disable();
        $this->setContent($data);
        $this->setContentType('application/json');
        return $this;
    }

    public function noCache() {
        $this->setHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->setHeader('Cache-Control: post-check=0, pre-check=0');
        $this->setHeader('Pragma: no-cache');
        return $this;
    }

    public function send() {
        if (headers_sent()) {
            throw new Exception('Headers are already sent.');
        }
        if ($this->responseCode !== null) {
            http_response_code($this->responseCode);
        }
        $this->sendHeaders();
        $this->sendCookies();
        echo $this->content;
    }

    protected function sendHeaders() {
        foreach ($this->headers as $key => $value) {
            $header = $key;
            if ($value) {
                $header .= ': ' . $value;
            }
            header($header, false);
        }
        return $this;
    }

    protected function sendCookies() {
        foreach ($this->cookies as $key => $value) {
            setcookie($key, $value['value'], $value['expired'], $value['path']);
        }
        return $this;
    }

    public function redirect($url) {
        $this->getView()->disable();
        $this->noCache();
        $this->setHeader('location', $url);
        return $this;
    }

    public function setContentType($contentType) {
        $this->setHeader('Content-Type', $contentType);
        return $this;
    }

    public function end()
    {
        exit;
    }
}
