<?php

namespace PhpEssence\Form;

use PhpEssence\Service;

abstract class Form extends Service {

    protected $entity;
    protected $elements;
    protected $messages;
    protected $subForms;
    protected $prefix;
    protected $namespace;

    public function __construct() {
        parent::__construct();
        $this->elements = array();
        $this->messages = array();
        $this->initialize();
    }

    public function getEntity() {
        return $this->entity;
    }

    public function addSubForm(Form $sub, $index = null) {
        $currentSub = $index ?:count($this->subForms);
        $subPrefix = '_sub[';
        if (!empty($this->prefix)) {
            $subPrefix .= $this->prefix . '][';
        }
        $subPrefix .= $currentSub . ']';
        $sub->setPrefix($subPrefix);
        $this->subForms[] = $sub;
        return $this;
    }

    public function getSubForm($index) {
        if (isset($this->subForms[$index])) {
            return $this->subForms[$index];
        }
    }

    public function getSubForms() {
        return $this->subForms;
    }

    public function clearSubForms() {
        $this->subForms = array();
    }

    public function bind(array $data) {
        $this->bindElements($data);
    }

    protected function bindElements(array $data) {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                foreach ($value as $key1 => &$value1) {
                    if (isset($this->subForms[$key1])) {
                        $this->subForms[$key1]->bindElements($value1);
                    }
                }
                continue;
            }
            if ($this->prefix) {
                $key = str_replace($this->prefix, '', $key);
            }
            $element = $this->get($key);
            if ($element !== null) {
                $value = trim($value);
                $this->entity[$key] = $value;
                $element->setValue($value);
            }
        }
    }

    public function add(FormElement $element) {
        $element->setForm($this);
        if (isset($this->elements[$element->getName()])) {
            if (is_array($this->elements[$element->getName()])) {
                $this->elements[$element->getName()][] = $element;
            } else {
                $old = $this->elements[$element->getName()];
                $this->elements[$element->getName()] = array($old, $element);
            }
        } else {
            $this->elements[$element->getName()] = $element;
        }
    }

    /**
     * @param $name
     * @return FormElement | null
     */
    public function get($name) {
        if (isset($this->elements[$name])) {
            return $this->elements[$name];
        }
    }

    public function has($name) {
        return isset($this->elements[$name]);
    }

    public function getElements() {
        return $this->elements;
    }

    public function addMessages($message) {
        $this->messages[] = $message;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function setNamespace($namespace) {
        foreach($this->elements as $element) {
            $element->setNamespace($namespace);
        }
        $this->namespace = $namespace;
        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        foreach ($this->elements as $element) {
            $element->setPrefix($prefix);
        }
    }

    public function getPrefix() {
        return $this->prefix;
    }

    protected abstract function checkForm();

    public function isValid($breakOnError = false) {
        $ok = true;
        foreach ($this->elements as $element) {
            $res = $element->isValid($breakOnError);
            if (!$res) {
                $ok = false;
                if ($breakOnError) {
                    return false;
                }
            }
        }
        return $ok && $this->checkForm();
    }

    protected abstract function initialize();
}
