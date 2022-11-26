<?php 	
require 'environment.php';
global $config;
$config = array();

if(ENVIRONMENT == "DEV"){
	define("BASE_URL", "http://localhost/php/WebServices/Projeto WebService/");
	
	$config['dbname'] = "webservice";
	$config['host'] = "localhost";
	$config['user'] = "root";
	$config['password'] = "123456";
	$config['jwt_secret_key'] = "abC123!";
}else{
	//config servidor 
}
	
try {
	global $db;
	$db = new PDO("mysql:host=".$config['host'].";dbname=".$config['dbname']."",$config['user'],$config['password']);
} catch (PDOException $e) {
		echo "Erro ao conectar ao banco <br>";
		exit;
}


 ?>