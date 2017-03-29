<?php

namespace PhpEssence\Form;

class FormElement {

    const TYPE_TEXT = 'text';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_TEXT_AREA = 'text_area';
    const TYPE_SUBMIT = 'submit';
    const TYPE_SELECT = 'select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_NUMBER = 'number';

    const VALIDATE_REQUIRED = 'required';
    const VALIDATE_PATTERN = 'pattern';
    const VALIDATE_CUSTOM = 'custom';
    const VALIDATE_IN_LIST = 'in_list';

    protected $type;
    protected $name;
    protected $options;
    protected $messages;
    protected $value;
    protected $prefix;
    protected $validations;
    protected $namespace;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param $type
     * @param $name
     * @param array $options
     */
    public function __construct($type, $name, $options = array()) {
        $this->type = $type;
        $this->name = $name;
        $this->options = array();
        $this->setOptions($options);
        if (!isset($options['id'])) {
            $this->options['id'] = $name;
        }
        $this->messages = array();
        $this->validations = array();
    }

    public function setValidateRequired($isRequired, $message) {
        if ($isRequired) {
            $this->validations[static::VALIDATE_REQUIRED] = $message;
        } else {
            unset($this->validations[static::VALIDATE_REQUIRED]);
        }
    }

    public function setValidatePattern($pattern, $message) {
        $this->validations[static::VALIDATE_PATTERN] = array(
            'pattern' => $pattern,
            'message' => $message
        );
    }

    public function setValidateInList($list, $message) {
        $this->validations[static::VALIDATE_IN_LIST] = array(
            'list' => $list,
            'message' => $message
        );
    }

    public function setValidator($function) {
        $this->validations[static::VALIDATE_CUSTOM][] = $function;
    }

    public function isValid($breakOnError = false) {
        foreach ($this->validations as $key => &$value) {
            switch ($key) {
                case static::VALIDATE_REQUIRED:
                    if (empty($this->value)) {
                        $this->messages[] = $value;
                    }
                    break;
                case static::VALIDATE_PATTERN:
                    if (!preg_match($value['pattern'], $this->value)) {
                        $this->messages[] = $value['message'];
                    }
                    break;
                case static::VALIDATE_IN_LIST:
                    if (in_array($this->value, $value['list'])) {
                        $this->messages[] = $value['message'];
                    }
                    break;
                case static::VALIDATE_CUSTOM:
                    if (is_callable($value)) {
                        $res = call_user_func($value);
                        if (is_string($res)) {
                            $this->messages[] = $res;
                        }
                    }
                    break;
            }
            if ($breakOnError && isset($this->messages[0])) {
                return false;
            }

        }
        return empty($this->messages);
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setId($id) {
        $this->options['id'] = $id;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function setOptions($options) {
        $this->options = array_merge($this->options, $options);
        if (isset($this->options['value'])) {
            $this->setValue($options['value']);
        }
    }

    public function addMessage($message) {
        $this->messages[] = $message;
    }

    public function getName() {
        return $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getOptions() {
        return $this->options;
    }

    public function getValue() {
        return $this->value;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function setForm(Form $form) {
        $this->form = $form;
        $this->prefix = $form->getPrefix();
        $this->namespace = $form->getNamespace();
    }

    public function getForm() {
        return $this->form;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @param $name
     * @param array $options
     * @return FormElement
     */
    public static function createTextBox($name, $options = array()) {
        return new FormElement(static::TYPE_TEXT, $name, $options);
    }

    public static function createSelect($name, $values, $options = array()) {
        $options['options'] = $values;
        return new FormElement(static::TYPE_SELECT, $name, $options);
    }

    public static function createCheckbox($name, $options = array()) {
        return new FormElement(static::TYPE_CHECKBOX, $name, $options);
    }

    public static function createRadio($name, $values, $options = array()) {
        $options['options'] = $values;
        return new FormElement(static::TYPE_RADIO, $name, $options);
    }

    public static function createTextArea($name, $options = array()) {
        return new FormElement(static::TYPE_TEXT_AREA, $name, $options);
    }

    public static function createSubmit($name, $options = array()) {
        return new FormElement(static::TYPE_SUBMIT, $name, $options);
    }

    public function renderCommonField($type, $options) {
        $html = '<input type="' . $type . '" ';
        $html .= $this->getOptionsHtml($options);
        $html .= '/>';
        return $html;
    }

    protected function renderCommonElement() {
        $options = $this->options;
        $options['name'] = $this->buildName();
        if (isset($options['id'])) {
            $options['id'] = $this->prefix . $options['id'];
        }
        $options['value'] = $this->value;
        return $this->renderCommonField($this->type, $options);
    }

    protected function renderSelect() {
        $options = $this->options;
        unset($options['value']);
        $values = isset($options['options']) ? $options['options'] : array();
        unset($options['options']);
        $options['name'] = $this->buildName();
        if (isset($options['id'])) {
            $options['id'] = $this->prefix . $options['id'];
        }
        $html = '<select ' . $this->getOptionsHtml($options) . '>';
        foreach ($values as $key => &$value) {
            $html .= '<option value="' . htmlentities($key) . '"';
            if ($key == $this->value) {
                $html .= ' selected';
            }
            $html .= '>' . htmlentities($value) . '</options>';
        }
        $html .= '</select>';
        return $html;
    }

    protected function getOptionsHtml($options) {
        $html = '';
        foreach ($options as $key => &$value) {
            $html .= $key . '="' . htmlentities($value) . '" ';
        }
        return $html;
    }

    protected function renderCheckbox() {
        $options = $this->options;
        unset($options['value']);
        $label = isset($options['label']) ? $options['label'] : null;
        unset($options['label']);
        $options['name'] = $this->buildName();
        if (isset($options['id'])) {
            $options['id'] = $this->prefix . $options['id'];
        } else {
            $options['id'] = $options['name'];
        }
        $html = $html = '<input type="checkbox" ';
        $html .= $this->getOptionsHtml($options);
        if ($this->value) {
            $html .= ' checked';
        }
        $html .= ' />';
        if ($label) {
            $html .= '<label for="' . $options['id'] .'">' . htmlentities($label) . '</label>';
        }
        return $html;
    }

    protected function renderRadio() {
        $options = $this->options;
        unset($options['value']);
        $values = isset($options['options']) ? $options['options'] : array();
        unset($options['options']);
        $options['name'] = $this->buildName();
        if (isset($options['id'])) {
            $id = $this->prefix . $options['id'];
            unset($options['id']);
        } else {
            $id = null;
        }
        $html = null;
        foreach ($values as $key => &$value) {
            $cOptions = $options;
            $cOptions['id'] = $id . $key;
            $html .= '<input type="radio" value="' . htmlentities($key) . '"';
            $html.= $this->getOptionsHtml($cOptions);
            if ($key == $this->value) {
                $html .= ' checked';
            }

            $html .= ' /><label for="' . $cOptions['id'] . '">' . htmlentities($value) . '</label>';
        }
        return $html;
    }

    protected function buildName() {
        if (substr($this->prefix, -1) === ']') {
            return $this->prefix . '[' . $this->name .']';
        }
        if ($this->namespace) {
            return $this->namespace . '[' . $this->prefix . $this->name . ']';
        }
        return $this->prefix . $this->name;
    }

    protected function renderTextArea() {
        $options = $this->options;
        $options['name'] = $this->buildName();
        if (isset($options['id'])) {
            $options['id'] = $this->prefix . $options['id'];
        }
        unset($options['value']);
        $html = '<textarea ';
        $html .= $this->getOptionsHtml($options) .' >';
        $html .= htmlentities($this->value) . '</textarea>';
        return $html;
    }

    public function getHtml() {
        switch ($this->type) {
            case FormElement::TYPE_SELECT:
                return $this->renderSelect();
            case FormElement::TYPE_CHECKBOX:
                return $this->renderCheckbox();
            case FormElement::TYPE_RADIO:
                return $this->renderRadio();
            case FormElement::TYPE_TEXT_AREA:
                return $this->renderTextArea();
            default:
                return $this->renderCommonElement();
        }
    }
}
