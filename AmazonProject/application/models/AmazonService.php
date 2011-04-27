<?php
/*
require_once 'GamesTable.php';
require_once 'LeaguesTable.php';
require_once 'LocationsTable.php';
require_once 'SessionsTable.php';
require_once 'TeamsTable.php';
*/

class Default_Model_AmazonService
{
	protected $db;
	protected $games;
	protected $leagues;
	protected $locations;
	protected $sessions;
	protected $teams;
	
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
	}
	
	public function getAmazonData($itemID)
	{
		$client = new Zend_Service_Amazon('AKIAJVBMLHZM5WWDE52A','US','2pz5jsxWFfkFbWQLkveKmrmnDwZSbvdWZNzfEzcQ');
		$item = $client->itemLookup($itemID,array(
		'ResponseGroup' => 'Medium,Offers'
		));
		
		$itemData = "";
		$itemData = array(
			'Title' => $item->Title,
			'CurrentPrice' => $item->FormattedPrice,
			'LowestUsedPrice' => $item->Offers->LowestUsedPrice,
			'StockNew' => $item->Offers->TotalNew,
			'StockUsed' => $item->Offers->TotalUsed,
			'SmallImageUrl' => $item->SmallImage->Url,
			'MediumImageUrl' => $item->MediumImage->Url,
			'LargeImageUrl' => $item->LargeImage->Url,
			'AmazonUrl' => $item->DetailPageURL
		);
		
		return $itemData;
	}
	
	public function getLeagueNameByAlias($alias)
	{
		
		$sql = "SELECT name FROM leagues WHERE alias = '".$alias."'";
		
		return $this->db->fetchAll($sql);
		
	}
}

?>