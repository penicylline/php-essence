<?php
namespace PhpEssence;

abstract class Service {
    protected $_sc;

    public function __construct() {
        $this->_sc = ServiceContainer::getInstance();
    }

    /**
     * @return \PhpEssence\Mvc\View
     */
    public function getView() {
        return $this->_sc->get('view');
    }

    /**
     * @return \PhpEssence\Component\Dispatcher
     */
    public function getDispatcher() {
        return $this->_sc->get('dispatcher');
    }

    /**
     * @return \PhpEssence\Helper\Url
     */
    public function getUrl() {
        return $this->_sc->get('url');
    }

    /**
     * @return \PhpEssence\Helper\Tag
     */
    public function getTag() {
        return $this->_sc->get('tag');
    }

    /**
     * @return \PhpEssence\Component\Request
     */
    public function getRequest() {
        return $this->_sc->get('request');
    }

    /**
     * @return \PhpEssence\Component\Response
     */
    public function getResponse() {
        return $this->_sc->get('response');
    }
}
