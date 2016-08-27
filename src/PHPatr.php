<?php

namespace PHPatr
{
	use Phar;
	use Exception;
	use GuzzleHttp\Client;
	use PHPatr\Exceptions\ConfigFileNotFoundException;
	use PHPatr\Exceptions\ErrorTestException;

	class PHPatr
	{
		private $_client;
		private $_auths = array();
		private $_bases = array();
		private $_configFile = './phpatr.json';
		private $_hasError = false;
		private $_saveFile = false;
		private $_version = '0.2.0';
		private $_update = array(
			'base' => 'https://raw.githubusercontent.com',
			'path' => '/00F100/phpatr/master/dist/version',
		);
		private $_download = 'https://github.com/00F100/phpatr/raw/master/dist/phpatr.phar?';

		public function init()
		{
			$configured = false;
			$args = func_get_args();
			if($args[0] == 'index.php'){
				unset($args[0]);
			}
			while($value = current($args)){
				switch($value){
					case '-c':
					case '--config':
						$this->_configFile = next($args);
						$configured = true;
						break;
					case '-o':
					case '--output':
						$this->_saveFile = next($args);
						break;
					case '-up':
					case '--self-update':
						$this->_selfUpdate();
						break;
					case '-v':
					case '--version':
						$this->_version();
						break;
					case '--help':
					case '-h':
						$this->_help();
						break;
				}
				next($args);
			}
			if(!$configured){
				$this->_help();
			}
			if($this->_saveFile){
				$this->_resetLogFile();
			}
			$this->_checkUpdate();
			return $this->_run();
		}

		private function _run()
		{
			$configFile = str_replace($_SERVER['argv'][0], '', Phar::running(false)) . $this->_configFile;
			if(!is_file($configFile)){
				throw new ConfigFileNotFoundException($configFile);
			}
			$this->_log('Start: ' . date('Y-m-d H:i:s'));
			$this->_log('Config File: ' . $this->_configFile);
			$this->_config = json_decode(file_get_contents($configFile), true);
			$this->_log('Test Config: ' . $this->_config['name']);
			$this->_configAuth();
			$this->_configBase();
			$this->_log('Run Tests!');

			if(count($this->_config['tests']) > 0){
				foreach($this->_config['tests'] as $test){

					$base = $this->_bases[$test['base']];
					$auth = $this->_auths[$test['auth']];

					$header = [];
					$query = [];

					if(array_key_exists('header', $base)){
						$header = array_merge($header, $base['header']);
					}
					if(array_key_exists('query', $base)){
						$query = array_merge($query, $base['query']);
					}

					if(array_key_exists('header', $auth)){
						$header = array_merge($header, $auth['header']);
					}
					if(array_key_exists('query', $auth)){
						$query = array_merge($query, $auth['query']);
					}

					// debug(compact('header', 'query'));

					$this->_client = new Client([
						'base_uri' => $base['url'],
						'timeout' => 10,
						'allow_redirects' => false,
					]);

					$assert = $test['assert'];

					$statusCode = $assert['code'];

					try {
						$response = $this->_client->request('GET', $test['path'], [
							'query' => $query,
							'headers' => $header
						]);	
					} catch(Exception $e){
						if($e->getCode() == $statusCode){
							$this->_success($base, $auth, $test);
							break;
						}else{
							$this->_error($base, $auth, $test);
							break;
						}
					}

					if($response->getStatusCode() != $statusCode){
						$this->_error($base, $auth, $test);
						break;
					}

					switch($assert['type']){
						case 'json':
							$body = $response->getBody();
							$json = array();
							while (!$body->eof()) {
								$json[] = $body->read(1024);
							}
							$json = implode($json);
							if(
								(substr($json, 0, 1) == '{' && substr($json, -1) == '}') ||
								(substr($json, 0, 1) == '[' && substr($json, -1) == ']')
							){
								$json = json_decode($json, true);

								if(!$this->_parseJson($assert['fields'], $json)){
									$this->_error($base, $auth, $test);
									break;
								}else{
									$this->_success($base, $auth, $test);
									break;
								}

							}else{
								$this->_error($base, $auth, $test);
								break;
							}
							break;
					}
				}
			}
			$this->_log('End: ' . date('Y-m-d H:i:s'));
			if($this->_hasError){
				throw new ErrorTestException();
			}
		}

		private function _parseJson($required, $json)
		{
			if(is_array($required) && is_array($json)){

				$findFields = array();

				foreach($required as $indexRequired => $valueRequired){

					$error = false;

					foreach($json as $indexJson => $valueJson){

						if(is_array($valueRequired) && is_array($valueJson)){
							return $this->_parseJson($valueRequired, $valueJson);
						}else{

							if(is_array($valueRequired) || is_array($valueJson)){
								$error = true;
							}else{
								if($indexJson == $indexRequired){
									if($valueRequired != gettype($valueJson)){
											$error = true;
									}else{
										$success[] = $valueJson;
									}
								}
							}
							
						}
					}

					if($error){
						return false;
					}
					
				}

				if(count($success) == count($required)){
					return true;
				}
				
			}
			return false;
		}

		private function _configAuth()
		{
			$this->_auths = array();
			foreach($this->_config['auth'] as $auth){
				$this->_auths[$auth['name']] = $auth;
			}
		}

		private function _configBase()
		{
			$this->_bases = array();
			foreach($this->_config['base'] as $base){
				$this->_bases[$base['name']] = $base;
			}
		}

		private function _log($message, $array = false)
		{
			echo "LOG: \033[33m$message\033[0m \n";
			if($array && is_array($array)){
				print_r($array);
			}
			if($this->_saveFile){
				$this->_logFile('LOG: ' . $message . "\n");
			}
		}

		private function _error($base, $auth, $test)
		{
			$this->_hasError = 1;
			echo "[\033[31mFAIL\033[0m] " . $test['name'] . " \n";
			if($this->_saveFile){
				$this->_logFile('[FAIL] ' . $test['name'] . "\n");
			}
		}

		private function _true($message)
		{
			echo "[\033[32m OK \033[0m] " . $message . " \n";
			if($this->_saveFile){
				$this->_logFile('[ OK ] ' . $message . "\n");
			}
		}

		private function _success($base, $auth, $test)
		{
			echo "[\033[32m OK \033[0m] " . $test['name'] . " \n";
			if($this->_saveFile){
				$this->_logFile('[ OK ] ' . $test['name'] . "\n");
			}
		}

		private function _logFile($message)
		{
			$fopen = fopen($this->_saveFile, 'a');
			fwrite($fopen, 'LOG: ' . $message);
			fclose($fopen);
		}

		private function _resetLogFile()
		{
			unlink($this->_saveFile);
		}

		private function _help()
		{
			echo "   \033[33mUsage:\033[0m\n";
			echo "        \033[33m Test API REST: \033[0m\n";
			echo "	\033[32m php phpatr.phar --config <config file> [--output <file>] \033[0m \n\n";
			echo "        \033[33m Self Update: \033[0m\n";
			echo "	\033[32m php phpatr.phar --self-update \033[0m \n\n";
			echo "        \033[33m Help: \033[0m\n";
			echo "	\033[32m php phpatr.phar --help \033[0m \n\n";
			echo "	Options:\n";
			echo "	\033[37m  -c,  --config                     File of configuration in JSON to test API REST calls \033[0m \n";
			echo "	\033[37m  -h,  --help                       Show this menu \033[0m \n";
			echo "	\033[37m  -o,  --output                     Output file to save log \033[0m \n";
			echo "	\033[37m  -up, --self-update                Upgrade to the latest version version \033[0m \n";
			echo "	\033[37m  -v,  --version                    Return the installed version of this package \033[0m";
			die(1);
		}

		private function _checkUpdate($returnVersion = false)
		{
			$client = new Client([
				'base_uri' => $this->_update['base'],
				'timeout' => 10,
				'allow_redirects' => false,
			]);

			try {
				$response = $client->request('GET', $this->_update['path']);
			} catch(Exception $e){
				return false;
			}

			$body = $response->getBody();
			$version = array();
			while (!$body->eof()) {
				$version[] = $body->read(1024);
			}
			$version = implode($version);
			$_cdn_version = str_replace('.', '', $version);

			if($returnVersion){
				return $version;
			}

			$_local_version = $this->_version;
			$_local_version = str_replace('.', '', $_local_version);

			if($_local_version < $_cdn_version){
				$this->_messageUpdate($version);
			}
		}

		private function _messageUpdate($version)
		{
			echo "\033[31mUPDATE:\033[0m \033[31m There is a new version available! \033[0m \n";
			echo "\033[31mUPDATE:\033[0m \033[31m $this->_version -> $version \033[0m \n";
			echo "\033[31mUPDATE:\033[0m \033[31m Automatic: Run the self-update: php phpatr.phar --self-update \033[0m \n";
			echo "\033[31mUPDATE:\033[0m \033[31m Manual: visit the GitHub repository and download the latest version (https://github.com/00F100/phpatr/) \033[0m \n";
		}

		private function _selfUpdate()
		{
			$version = $this->_checkUpdate(true);
			$_cdn_version = str_replace('.', '', $version);
			$_local_version = $this->_version;
			$_local_version = str_replace('.', '', $_local_version);

			if($_local_version < $_cdn_version){
				$pharFile = str_replace($_SERVER['argv'][0], '', Phar::running(false)) . '/phpatr-updated.phar';
				// $toFile = __DIR__ . '/dist/phpatr-updated.phar';
				 try {
				 	$client = new Client();
				 	$this->_log('Downloading new version');
					$response = $client->request('GET', $this->_download . md5(microtime()));
					$body = $response->getBody();
					$phar = array();
					while (!$body->eof()) {
						$phar[] = $body->read(10240);
					}
					$phar = implode($phar);
				 	$this->_true('Downloading new version');
					$fopen = fopen($pharFile, 'w');
					fwrite($fopen, $phar);
					fclose($fopen);
					 	$this->_log('Updating Phar file');
					copy($pharFile, 'phpatr.phar');
					 	$this->_true('Updating Phar file');
					 	$this->_log('Removing temporary file');
						unlink($pharFile);
					 	$this->_true('Removing temporary file');
						$this->_true('PHPatr updated to: ' . $version);
				     	die();
				 } catch (Exception $e) {
					$this->_error('Downloading new version');
				     	die();
				 }
			}else{
				$this->_log('Your version is updated');
			     	die();
			}
		}

		private function _version()
		{
			echo $this->_version;
			die();
		}
	}
}