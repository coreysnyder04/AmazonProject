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
		echo "$itemID <br />";
		
		//Hickman do your thing w/ this item ID to pull back the data from amazon
		$url =
			"http://ecs.amazonaws.com/onca/xml?"
			."Service=AWSECommerceService"
			."&AWSAccessKeyId=AKIAJVBMLHZM5WWDE52A"
			."&AssociateTag=8842-6428-7537"
			."&Operation=ItemLookup"
			."&ItemId=".$itemID
			."&ResponseGroup=ItemAttributes,OfferSummary,Medium";
			
			//the $secret key is not to be shared with ANYONE
			$secret = '2pz5jsxWFfkFbWQLkveKmrmnDwZSbvdWZNzfEzcQ';
			
			//pulls out 'ecs.amazonaws.com'
			$host = parse_url($url,PHP_URL_HOST);
			
			//puts the timestamp at the end of the URL
			$timestamp = gmstrftime("%Y-%m-%dT%H:%M:%S.000Z");
			$url=$url. "&Timestamp=" . $timestamp;
			$paramstart = strpos($url,"?");
			
			//replace all the commas and colons in the URL after the questionmark
			$strParse = substr($url,$paramstart+1);
			$strParse = str_replace(",","%2C",$strParse);
			$strParse = str_replace(":","%3A",$strParse);
			
			//split, sort, and put back together the string to be signed
			$params = explode("&",$strParse);
			sort($params);
			$strSign = "GET\n" . $host . "\n/onca/xml\n" . implode("&",$params);
			
			//sign the string
			$strSign = base64_encode(hash_hmac('sha256', $strSign, $secret, true));
			$strSign = urlencode($strSign);
			$signedurl = $url . "&Signature=" . $strSign;
			
			//grab contents from URL
			$filecontent = @file_get_contents($signedurl);
			
			//we'll assign the variables here once they're selected from xpath
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

				//get image URL
				$imageQuery = "";
				$imageURL = "";
				
				//output Variables
				echo @"Title: $title <br />".
				@"List Price: $listPrice <br />".
				@"Lowest New Price: $lowestNewPrice <br />".
				@"Lowest Used Price: $lowestUsedPrice";
			}
				
			else 
			{
				echo "There was an error contacting Amazon Web Services. <br />";
			}
			
		//Put the data in this array var
		$itemData = array ();
		
		//Here's where I push the data to the view
		$this->view->itemData = $itemData;	
	}
	
	
}