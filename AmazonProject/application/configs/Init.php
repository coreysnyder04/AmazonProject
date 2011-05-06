<?php

/* Controller */

$this->controllerName = $this->getRequest()->getControllerName();
$this->actionName = $this->getRequest()->getActionName();

/* Model */

$this->amazonService = new Default_Model_AmazonService();

/* View */

$this->view->doctype('XHTML1_STRICT');

$this->view->pageTitle = "IdleBuy";
$this->view->keywords = "IdleBuy";
$this->view->description = "IdleBuy";

$this->view->style = "main";
$this->_helper->layout->setLayout($this->view->style);

$this->view->controllerName = $this->controllerName;
$this->view->actionName = $this->actionName;
