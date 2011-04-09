<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array(
					'namespace' => 'Default',
					'basePath'  => dirname(__FILE__),
					));
		Zend_Session::start(); 
		return $autoloader;
	}
	
	protected function _initBootstrapView()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
	}
	
	protected function _initRoutes()
	{
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		$router->addRoute(
				'additem',
				new Zend_Controller_Router_Route(
					'/additem/', 
					array('controller' => 'index', 'action' => 'additem')
					)
				);
	
		
	}
}

