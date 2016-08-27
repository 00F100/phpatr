<?php

require(__DIR__ . '/../vendor/autoload.php');

use PHPatr\PHPatr;

if(isset($argv)){
	$class = new PHPatr();
	echo call_user_func_array(array($class, 'init'), $argv);
	die;
}else{
	die('not isset argv');
}

