<?php

namespace PhpEssence\Mvc;

use PhpEssence\Service;

class View extends Service {

    protected $viewDir;
    protected $layout;
    protected $template;
    protected $data;
    protected $disabledView;
    protected $pageTitle;
    protected $pageDesc;
    protected $partialData = [];
    protected $partialIndex;
    protected $partialStack = [];

    /**
     * @var \PhpEssence\Helper\Tag
     */
    protected $tag;

    public function __construct() {
        parent::__construct();
        $this->data = array();
        $this->tag = $this->getTag();
        $module = $this->getDispatcher()->getModule();
        if ($module) {
            $this->viewDir = APP_DIR . '/modules/' . $module . '/view';
            return;
        }
        $this->viewDir = APP_DIR . '/view';
        return;
    }

    public function disable() {
        $this->disabledView = true;
    }

    public function isDisabled() {
        return $this->disabledView == true;
    }

    public function setViewDir($viewDir) {
        $this->viewDir = $viewDir;
    }

    public function render($output = true) {
        if ($this->disabledView) {
            return;
        }
        if (!$output) {
            ob_start();
        }
        if ($this->layout === null) {
            $this->layout = $this->getDispatcher()->getController();
        }
        require($this->viewDir . '/layouts/' . $this->layout . '.phtml');
        if (!$output) {
            $result = ob_get_contents();
            ob_clean();
            return $result;
        }
    }

    protected function getContent() {
        if ($this->disabledView) {
            return;
        }
        if ($this->template === null) {
            $this->template = $this->getDispatcher()->getAction();
        }
        require($this->viewDir . '/' . $this->getDispatcher()->getController() . '/' . $this->template . '.phtml');
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function renderPartial($partial, $data = [], $return = true) {
        if ($return) {
            ob_start();
        }
        $this->partial($partial, $data);
        if ($return) {
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        }
    }

    protected function partial($partial, $data = []) {
        $this->partialIndex = array_push($this->partialData, $data);
        array_push($this->partialStack, $this->partialIndex);
        require($this->viewDir . '/partials/' .$partial . '.phtml');
        array_pop($this->partialData);
        $this->partialIndex = array_pop($this->partialStack);
    }

    public function get($key)
    {
        if (isset($this->partialIndex, $this->partialData[$this->partialIndex], $this->partialData[$this->partialIndex][$key])) {
            if (is_scalar($this->partialData[$this->partialIndex][$key])) {
                return htmlentities($this->partialData[$this->partialIndex][$key]);
            }
            return $this->partialData[$this->partialIndex][$key];
        }
        if (isset($this->data[$key])) {
            if (is_scalar($this->data[$key])) {
                return htmlentities($this->data[$key]);
            }
            return $this->data[$key];
        }
    }

    public function getRaw($key)
    {
        if (isset($this->partialIndex, $this->partialData[$this->partialIndex], $this->partialData[$this->partialIndex][$key])) {
            return $this->partialData[$this->partialIndex][$key];
        }
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    public function set($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
            return $this;
        }
        $this->data[$key] = $value;
        return $this;
    }

    public function remove($key) {
        unset($this->data[$key]);
        return $this;
    }

    public function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }

    public function getPageTitle() {
        return $this->pageTitle;
    }

    public function setPageDescription($desc) {
        $this->pageDesc = $desc;
        return $this;
    }

    public function getPageDescription() {
        return htmlentities($this->pageDesc);
    }

    public function setCssBundle($name) {
        $this->data['cssBundle'] = $name;
    }

    public function setJsBundle($name) {
        $this->data['jsBundle'] = $name;
    }

    public function _echo($str) {
        echo htmlentities($str);
    }
}
