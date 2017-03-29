<?php

namespace PhpEssence\Component;

use PhpEssence\Service;

class Session extends Service {

    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function has($key) {
        return array_key_exists($key, $_SESSION);
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function del($key) {
        unset($_SESSION[$key]);
        return $this;
    }

    public function get($key) {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
    }

    public function destroy() {
        session_destroy();
    }

    public function restart() {
        session_regenerate_id(true);
    }

    public function getId() {
        return session_id();
    }
}
