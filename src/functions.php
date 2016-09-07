<?php
if(!function_exists('build_phar')){
	function build_phar($name, array $dirs, $default, $dirSave)
	{
		$app = new Phar($dirSave . $name . '.phar', 0, $name . '.phar');
		$app->startBuffering();
		foreach($dirs as $dir){
			$app->buildFromDirectory($dir, '/\.php|json$/');
		}
		$app->setStub($app->createDefaultStub($default));
		$app->compress(Phar::GZ);
		$app->stopBuffering();
	}
}