<?php

class IndexController extends Zend_Controller_Action
{
	protected $amazonService;

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

    public function indexAction()
    {
    }
	public function aboutusAction()
	{
		$this->view->pageTitle = "About Us!";
	}
	public function additemAction()
	{
		$this->view->pageTitle = "AddItem";
		//Hickman do your thing w/ this item ID to pull back the data from amazon
		$itemID = $this->getRequest()->getParam("itemID");
		
		$itemData = $this->amazonService->getAmazonData($itemID);

		//this is for testing - will remove for production
		foreach($itemData as $d)
		{
			echo "$d <br />";
		}		
			
		//Here's where I push the data to the view
		$this->view->itemData = $itemData;	
	}
}