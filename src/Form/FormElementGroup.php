<?php
namespace PhpEssence\Form;

class FormElementGroup extends FormElement {
    private $values = array();
    private $elements = array();

    public function addValue($value) {
        $this->values[] = $value;
        return $this;
    }

    public function clearValues() {
        $this->values = [];
    }

    public function count() {
        return count($this->values);
    }

    public function get($index) {
        if ($index < 0 || $index > count($this->values)) {
            return;
        }
        if (isset($this->elements[$index])) {
            return $this->elements[$index];
        }
        $options = $this->options;
        $options['value'] = $this->values[$index];
        $options['id'] = $this->name . '_' . $index;
        $e = new FormElement($this->type, $this->getElementName(), $options);
        $this->elements[$index] = $e;
        return $e;
    }

    protected function getElementName() {
        if (substr($this->name, -2) == '[]') {
            return $this->name;
        }
        return $this->name. '[]';
    }
}
