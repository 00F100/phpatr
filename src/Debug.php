<?php
/**
 * Debugger class
 *
 * @package     	TestApiRest
 * @author 		João Moraes <joaomoraesbr@gmail.com>
 */
namespace PHPatr
{
	class Debug
	{
		/**
		 * Debug da aplicação
		 *
		 * @param mixed $var Variável a ser exibida
		 * @param boolean $exit Finalizar execução após imprimir debug
		 * @return void
		 * @access public
		 */
		public static function dump($var, $exit = true, $save = false)
		{
			if(true){
				$filename = false;
				$file = debug_backtrace();
				$file = next($file);
				if(array_key_exists('file', $file) && array_key_exists('line', $file)){
					$filename = $file['file'] . ':' . $file['line'];
				}
				$type = gettype($var);
				if(php_sapi_name() == 'cli' || $save){
                    if($save){
                        ob_start();
                    }
					echo "\n\n";
					if($filename){
						echo 'File: ' . $filename . "\n";
					}
					echo 'Type: ' . $type . "\n";
					echo '####################################';
					echo "\n";
					if($type == 'boolean'){
						if($var === 1 || $var === '1'){
							echo 'true';
						}else{
							echo 'false';
						}
					}else{
						print_r($var);
					}
					echo "\n####################################\n\n";
                    if($save){
                        $content = ob_get_contents();
                        ob_end_clean();
                        if(!is_dir(API_LOGS_DIR)){
                            mkdir(API_LOGS_DIR);
                        }
                        $hash = md5(json_encode($var));
                        $file = API_LOGS_DIR . microtime(true) . '.log';
                        $fopen = fopen($file, 'w');
                        fwrite($fopen, $content);
                        fclose($fopen);
                        $file = explode('/', $file);
                        $url = (strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
                        echo 'Log: <a href="' . $url . API_LOGS_DOWNLOAD . end($file) . '" target="_blank">' . end($file) . '</a><br>';
                    }
				}else{
					echo '<pre>';
					if($filename){
						echo 'File: <strong>' . $filename . '</strong>';
					}
					echo '<br>';
					echo 'Type: <strong>' . $type . '</strong>';
					echo '<br><br><blockquote style="background: #f7ffd5; padding: 20px; margin: 0px;">';
					if($type == 'boolean'){
						if($var == 1 || $var == '1'){
							echo 'true';
						}else{
							echo 'false';
						}
					}else{
						print_r($var);
					}
					echo '</blockquote><br>';
					echo 'Trace:';
					echo '<ul>';
					$debug_list = debug_backtrace();
					unset($debug_list[0]);
					foreach($debug_list as $debug){
						if(array_key_exists('file', $debug) && array_key_exists('line', $debug)){
							echo '<li>' . $debug['file'] . ':' . $debug['line'] . '</li>';
						}
					}
					echo '</ul>';
					echo '</pre><hr>';
				}
			}
			if($exit){
				exit;
			}
		}
	}
}