<?php

namespace PhpEssence\Component;

use PhpEssence\Form\FormElement;
use PhpEssence\Service;

class Security extends Service {

    const KEY_NAME = 'csrf_key';
    const VALUE_NAME = 'csrf_value';

    protected $fieldName;
    protected $token;

    public function __construct() {
        parent::__construct();
        if ($this->getRequest()->getCookie(static::KEY_NAME) === null) {
            $this->fieldName = $this->randomString(10);
            $this->token = $this->randomString();
            $this->getResponse()->setCookie(static::KEY_NAME, $this->fieldName);
            $this->getResponse()->setCookie(static::VALUE_NAME, $this->token);
            return;
        }
        $this->fieldName = $this->getRequest()->getCookie(static::KEY_NAME);
        $this->token = $this->getRequest()->getCookie(static::VALUE_NAME);
    }

    public function render() {
        $this->getTag()->renderFormElement(
            new FormElement(
                FormElement::TYPE_HIDDEN,
                $this->fieldName,
                array('value' => $this->token)
            )
        );
    }

    public function getKeyName() {
        return $this->fieldName;
    }

    public function getToken() {
        return $this->token;
    }

    public function isValid() {
        if ($this->getRequest()->hasGet($this->fieldName)) {
            $value = $this->getRequest()->get($this->fieldName);
        } else {
            $value = $this->getRequest()->getPost($this->fieldName);
        }
        return $value === $this->token;
    }

    public function randomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
