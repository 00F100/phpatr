<?php

require(__DIR__ . '/vendor/autoload.php');

$files = array(
	dirname(__FILE__) . '/',
);

build_phar('phpatr', $files, 'src/index.php', __DIR__ . '/dist/');