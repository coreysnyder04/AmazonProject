<?php 
	
	global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template;
	define('IN_PHPBB', true);
	$phpbb_root_path = './forum/phpBB3/';
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	$phpBBFile = $phpbb_root_path . 'common.' . $phpEx;
	include($phpBBFile);

	// Start session management
	$user->session_begin();
	$auth->acl($user->data);
	$user->setup();
	
	$this->view->user = false;
	
	
	if($user->data['is_registered'])
	 {
	 	$this->view->user = $user->data['username']; 
	 	
	 	//Admin
		if($user->data['user_type'] === 3){
			$this->view->isAdmin = "true";
		}else{
			$this->view->isAdmin = "false";
		}
		$this->view->userData = $user->data;
	 }