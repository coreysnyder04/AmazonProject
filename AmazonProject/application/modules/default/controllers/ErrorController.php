<?php

class ErrorController extends Zend_Controller_Action
{

	protected $cohService;

	public function init()
	{
		require_once APPLICATION_PATH.'/configs/Init.php';
    }
    
	public function preDispatch()
    {
    	require_once APPLICATION_PATH.'/configs/PreDispatch.php';
    }
    
	public function postDispatch()
    {
    	require_once APPLICATION_PATH.'/configs/PostDispatch.php';
    }
	
	public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }


}

    