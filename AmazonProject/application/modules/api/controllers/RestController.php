<?php
class Api_RestController extends Zend_Rest_Controller
{

	protected $amazonService;

	public function init()
	{
		require_once APPLICATION_PATH.'/configs/Init.php';
		$this->_helper->layout->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
	}
	public function indexAction()
	{
		// handle GET requests
		$itemData = "";
		$itemID = $this->_getParam('id');
		$itemData = $this->amazonService->getAmazonData($itemID);

		$this -> _helper->json($itemData);
	}
	public function getAction()
	{
		// handle GET requests
		$itemData = "";
		$itemID = $this->_getParam('id');
		$itemData = $this->amazonService->getAmazonData($itemID);

		$this -> _helper->json($itemData);
	}
	public function postAction()
	{
		// handle POST requests
	}
	public function putAction()
	{
		// handle PUT requests
	}
	public function deleteAction()
	{
		// handle DELETE requests
	}
}