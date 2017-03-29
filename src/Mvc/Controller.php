<?php

namespace PhpEssence\Mvc;

use PhpEssence\Component\Request;
use PhpEssence\Component\Response;
use PhpEssence\Component\Dispatcher;
use PhpEssence\Component\Security;
use PhpEssence\Service;

abstract class Controller extends Service {

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var View
     */
    protected $view;
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Security
     */
    protected $security;

    public function __construct() {
        parent::__construct();
        $this->request = $this->getRequest();
        $this->response = $this->getResponse();
        $this->view = $this->getView();
        $this->dispatcher = $this->getDispatcher();
    }

    public function initialize() {

    }
}
