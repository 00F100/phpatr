<?php

require(__DIR__ . '/../vendor/autoload.php');

$files = array(
	dirname(__FILE__) . '/',
);

$outputDir = __DIR__ . '/../dist/';

if(is_file($outputDir . 'phpatr.phar')){
	unlink($outputDir . 'phpatr.phar');
}
if(is_file($outputDir . 'phpatr.phar.gz')){
	unlink($outputDir . 'phpatr.phar.gz');
}

build_phar('phpatr', $files, '../src/index.php', $outputDir);