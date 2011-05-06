<?php
class Default_Model_AmazonService
{
	protected $db;
	protected $itemTable;
	
	function __construct()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini', APPLICATION_ENV);
		
		$options = array(
			'host'      => $config->db->host,
			'username'	=>	$config->db->username,
			'password'	=>	$config->db->password,
			'dbname'	=>	$config->db->dbname
		);
		
		$this->db = Zend_Db::factory($config->db->adapter, $options);
		Zend_Db_Table_Abstract::setDefaultAdapter($this->db);
		$this->itemTable = new Zend_Db_Table('item_lookups');
	}
	
	public function getAmazonData($itemID)
	{	
		$client = new Zend_Service_Amazon('AKIAJVBMLHZM5WWDE52A','US','2pz5jsxWFfkFbWQLkveKmrmnDwZSbvdWZNzfEzcQ');
		$item = $client->itemLookup($itemID,array(
		'ResponseGroup' => 'Medium,Offers'
		));
		
		//format variables for consistency
		if($item->FormattedPrice)
		{
			$ListPriceNoDollarSign = $item->FormattedPrice;	
			$ListPriceNoDollarSign = preg_replace('/[\$,]/', '', $item->FormattedPrice);
		}
		else 
		{
			$ListPriceNoDollarSign = "";
		}
		
		if($item->Offers->LowestNewPrice)
		{
			$lnp = $item->Offers->LowestNewPrice;
			$lnp = $lnp/100;
		}
		else
		{
			$lnp = "";
		}
		
		if($item->Offers->LowestUsedPrice)
		{
			$lup = $item->Offers->LowestUsedPrice;
			$lup = $lup/100;
		}
		else
		{
			$lup = "";
		}
		
		//build array response
		$itemData = "";
		$itemData = array(
			'Title' => $item->Title,
			'ListPrice' => $ListPriceNoDollarSign,
			'LowestNewPrice' => $lnp,
			'LowestUsedPrice' => $lup,
			'StockNew' => $item->Offers->TotalNew,
			'StockUsed' => $item->Offers->TotalUsed,
			'SmallImageUrl' => $item->SmallImage->Url,
			'MediumImageUrl' => $item->MediumImage->Url,
			'LargeImageUrl' => $item->LargeImage->Url,
			'AmazonUrl' => $item->DetailPageURL,
			'Manufacturer' => $item->Manufacturer
		);
		
		
		//get and format variables for database input
		$currentDate = date("Y-m-d");		
		
		
		$writeToDb = false;
		if($writeToDb == true)
		{
			$data = array(
					'item_id' => 'NULL' ,
				  	'asin' => $itemID,
				  	'last_lookup_date' => $currentDate,
				 	'title' => $itemData[Title],
				 	'manufacturer' => $itemData[Manufacturer],
				  	'lowest_new_price' => $itemData[LowestNewPrice],
				   	'lowest_used_price' => $itemData[LowestUsedPrice],
				    'list_price' => $itemData[ListPrice],
				    'amazon_url' => $itemData[AmazonUrl]
			);
			
			$this->itemTable->insert($data);
		}
		
		return $itemData;
	}
	
	public function testDatabase()
	{
	
		$sql = "SELECT item_id FROM item_lookups WHERE id = '1'";
		
		return $this->db->fetchAll($sql);
	
	}
	
	public function getLeagueNameByAlias($alias)
	{
		
		$sql = "SELECT name FROM leagues WHERE alias = '".$alias."'";
		
		return $this->db->fetchAll($sql);
		
	}
}

?>