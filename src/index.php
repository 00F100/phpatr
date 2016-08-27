<?php

require(__DIR__ . '/../vendor/autoload.php');

use TestApiRest\Test;

if(isset($argv)){
	$class = new Test();
	echo call_user_func_array(array($class, 'init'), $argv);
	die;
}else{
	die('not isset argv');
}

