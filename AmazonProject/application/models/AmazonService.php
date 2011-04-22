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
			
			//replace all the commas and colons in the URL after the questionmark
			$paramstart = strpos($url,"?");
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
			$filecontent = "";
			$filecontent = @file_get_contents($signedurl);
			
			//we'll assign the variables here once they're selected from xpath
			return $filecontent;
	}
	
	public function getLeagueNameByAlias($alias)
	{
		
		$sql = "SELECT name FROM leagues WHERE alias = '".$alias."'";
		
		return $this->db->fetchAll($sql);
		
	}
}

?>