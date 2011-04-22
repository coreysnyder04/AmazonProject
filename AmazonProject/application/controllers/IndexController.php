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
		$this->view->pageTitle = "About Us";
	}
	public function additemAction()
	{
		$this->view->pageTitle = "AddItem";
		$itemID = $this->getRequest()->getParam("itemID");
		$itemData = "";
		
		//Hickman do your thing w/ this item ID to pull back the data from amazon
		$filecontent = $this->amazonService->getAmazonData($itemID);
		
		if($filecontent)
		{ 
			$xml = simplexml_load_string($filecontent);
			$xml->registerXPathNamespace("ns", "http://webservices.amazon.com/AWSECommerceService/2005-10-05");
				
			//get title
			$titleQuery = $xml->xpath("//ns:Title");
			$title = $titleQuery[0];
				
			//get prices
			$priceQuery = $xml->xpath("//ns:FormattedPrice");
			$listPrice = $priceQuery[0];
			$lowestNewPrice = $priceQuery[1];
			$lowestUsedPrice = $priceQuery[2];
				
			$this->view->listPrice = $listPrice;
			
			//get image URL
			$imageQuery = "";
			$imageURL = "";
			
			//Put the data in this array var
			$itemData = array (
				"title" => $title,
				"listPrice" => $listPrice,
				"lowestNewPrice" => $lowestNewPrice,
				"lowestUsedPrice" => $lowestUsedPrice);
			
			//For testing purposes only -- Delete for Production
			echo $itemData["title"]."<br />";
			echo $itemData["listPrice"]."<br />";
			echo $itemData["lowestNewPrice"]."<br />";
			echo $itemData["lowestUsedPrice"]."<br />";
		}
		else 
		{
			echo "There as an error contacting AWS.";
		}
			
		//Here's where I push the data to the view
		$this->view->itemData = $itemData;	
	}
	
	
}