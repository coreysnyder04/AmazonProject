<?php

	function validateName($fieldName, $value){
		//if it's less than 2 OR greater than 15
		if(strlen($value) < 2 || strlen($value) > 15)
			return "<li>Invalid ".$fieldName.".</li>";
		//if it's valid
		else
			return;
	}
	function validateEmail($email){
		if(ereg("^[a-zA-Z0-9]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$", $email)){
			return;
		}else{
			return "<li>Invalid email.</li>";
		}
	}
	function validatePasswords($pass1, $pass2) {
		//if DOESN'T MATCH
		if(strpos($pass1, ' ') !== false)
			return "<li>Invalid password.</li>";
		//if are valid
		if($pass1 == $pass2 && strlen($pass1) > 5){
			return;
		}else{
			return "<li>Invalid password.</li>";
		}
	}
	function validateLength($value, $min, $max, $fieldName){
		//if it's NOT valid
		if(strlen($value) < $min)
			return "<li>".$fieldName." too short.</li>";
		else if(strlen($value) > $max)
			return "<li>".$fieldName." too long.</li>";
		else
			return;
	}
	function validateNotEmpty($value, $max, $fieldName){
		//if it's NOT valid
		if($value == "")
			return "<li>".$fieldName." cannot be empty..</li>";
		else if(strlen($value) > $max)
			return "<li>".$fieldName." too long.</li>";
		else
			return;
	}
	function validateScore($value){
		if(ereg("^[0-9]{1,2}$", $value) && strlen($value) < 3){
			return;
		}else{
			return "<li>Invalid Score (".$value.").</li>";
		}
	}
	function validateNumber($value, $max, $name){
		if(ereg("^[0-9]{1,".$max."}$", $value) && strlen($value) < $max){
			return;
		}else{
			return "<li>Invalid ".$name.".</li>";
		}
	}
	function validateIP($value){
		if(ereg("\b((2[0-5]{2}|1[0-9]{2}|[0-9]{1,2})\.){3}(2[0-5]{2}|1[0-9]{2}|[0-9]{1,2})\b", $value)){
			return;
		}else{
			return "<li>Invalid IP Address ( $value ).</li>";
		}
	}
	//Any # 1-5 chars
	function validateRowID($value){
		if(ereg("^[0-9]{1,6}$", $value) && strlen($value) < 6){
			return;
		}else{
			return; //"<li>Invalid RowID Number.</li>";
		}
	}
	function validatePhone($value){
		if(ereg("\+?1?[-\s.]?\(?(\d{3})\)?[-\s.]?(\d{3})[-\s.]?(\d{4})", $value)){
			return;
		}else{
			return "<li>Invalid Phone Number.</li>";
		}
	}
	function validateGoalsAssists($goal, $assist1, $assist2){
		if( $goal == $assist1 || $goal == $assist2 || ($assist1 == $assist2 && $assist1 !== "") ){
			return "<li>Invalid assist entered.</li>";
		}else{
			return;
		}
	}
	function validateTime($value){
		if(ereg('^(0?[1-9]|1[012])(:[0-5]\d)$', $value)){
			return;
		}else{
			return "<li>Invalid Time.(".$value.")".ereg("^(0?[1-9]|1[012])(:[0-5]\d)$", $value)."</li>";
		}
	}
	/*
	function validate($value){
		if(){
			return;
		}else{
			return "<li>Invalid password.</li>";
		}
	}
	*/
	
	
	

	
?>