<?php

namespace PhpEssence\Helper;

use PhpEssence\Service;

class Url extends Service {

    protected $baseUrl;

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function get($path, $params = array(), $withSecurityToken = false) {
        $url = $this->baseUrl . $path;
        if ($withSecurityToken) {
            $security = $this->_sc->get('security');
            $params[$security->getKeyName()] = $security->getToken();
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    public function getCurrentUrl($withDomain = false) {
        $url = $withDomain ? 'http://' . $_SERVER['SERVER_NAME'] : '';
        $url .= $_SERVER['REQUEST_URI'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
        }
        return $url;
    }
}
