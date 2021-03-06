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

	protected function _initLogger()
    {
		$logger = new Zend_Log();
		$writer = new Zend_Log_Writer_Firebug();
		$logger->addWriter($writer);
		Zend_Registry::set('logger',$logger);
	    function logger($message, $label=null)
		{
		    if ($label!=null) {
		        $message = array($label,$message);
		    }
		    Zend_Registry::get('logger')->debug($message);
		}
    }

	protected function _initRoutes()
	{
		//get instance of front controller
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		
		//initialize rest route
		$restRoute = new Zend_Rest_Route($frontController, array(), array('api'));
		$router->addRoute('rest', $restRoute);
		
		//routes
		$router->addRoute(
			'home',
			new Zend_Controller_Router_Route(
				'/home', 
				array('module' => 'default', 'controller' => 'index', 'action' => 'index')
			)
		);
		$router->addRoute(
				'aboutus',
				new Zend_Controller_Router_Route(
					'/aboutus', 
					array('module' => 'default', 'controller' => 'index', 'action' => 'aboutus')
					)
				);
				
		$router->addRoute(
				'addItemEmpty',
				new Zend_Controller_Router_Route(
					'/addItem', 
					array('module' => 'default', 'controller' => 'index', 'action' => 'index')
					)
				);
				
		$router->addRoute(
				'addItem',
				new Zend_Controller_Router_Route(
					'/addItem/:itemID', 
					array('module' => 'default', 'controller' => 'index', 'action' => 'additem')
					)
				);
	}
}
?>