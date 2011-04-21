<?php 
	 
	/* View */

	$response = $this->getResponse();
	$response->insert('head', $this->view->render('/modules/head.phtml'));
	$response->insert('header', $this->view->render('/modules/header.phtml'));
	$response->insert('footer', $this->view->render('/modules/footer.phtml'));
	$response->insert('analytics', $this->view->render('/modules/analytics.phtml'));