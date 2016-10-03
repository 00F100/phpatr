<?php

namespace PHPatr
{
    use Phar;
    use Exception;
    use GuzzleHttp\Client;
    use PHPUPhar\PHPUPhar;
    use PHPatr\Exceptions\ConfigFileNotFoundException;
    use PHPatr\Exceptions\ConfigFileEmptyException;
    use PHPatr\Exceptions\OutputFileEmptyException;
    use PHPatr\Exceptions\ErrorTestException;

    class PHPatr
    {
        const VERSION = '0.12.0';
        private $_client;
        private $_auths = array();
        private $_bases = array();
        private $_configFile = './phpatr.json';
        private $_hasError = false;
        private $_saveFile = false;
        private $_update = array(
            'base' => 'https://raw.githubusercontent.com',
            'path' => '/00F100/phpatr/master/dist/version',
        );
        private $_download = 'https://github.com/00F100/phpatr/raw/master/dist/phpatr.phar?';
        private $_error = array();
        private $_debug = false;
        private $_echo = array();
        private $_return_logs = false;

        public function __construct($return_logs = false)
        {
            $this->_return_logs = $return_logs;
        }

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
                    case '-e':
                    case '--example-config-json':
                        return $this->_exampleConfigJson();
                        break;
                    case '-o':
                    case '--output':
                        $this->_saveFile = next($args);
                        break;
                    case '-u':
                    case '--self-update':
                        $this->_selfUpdate();
                        break;
                    case '-d':
                    case '--debug':
                        $this->_debug = true;
                        break;
                    case '-h':
                    case '--help':
                        $this->_echoWelcome();
                        return $this->_help();
                        break;
                    case '-v':
                    case '--version':
                        return self::VERSION;
                        break;
                }
                next($args);
            }
            if(!$configured){
                $this->_echoWelcome();
                return $this->_help();
            }
            $this->_echoWelcome();
            if($this->_saveFile){
                $this->_resetLogFile();
            }
            $this->_checkUpdate();
            return $this->_run();
        }

        private function _run()
        {
            set_exception_handler(array($this, 'exceptionHandler'));
            if(empty($this->_configFile)){
                throw new ConfigFileEmptyException();
            }
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
                foreach($this->_config['tests'] as $index => $test){
                    $base = $this->_bases[$test['base']];
                    $auth = $this->_auths[$test['auth']];
                    $header = [];
                    $query = [];
                    $data = [];
                    if(array_key_exists('header', $base)){
                        $header = array_merge($header, $base['header']);
                    }
                    if(array_key_exists('query', $base)){
                        $query = array_merge($query, $base['query']);
                    }
                    if(array_key_exists('data', $base)){
                        $data = array_merge($data, $base['data']);
                    }
                    if(array_key_exists('header', $auth)){
                        $header = array_merge($header, $auth['header']);
                    }
                    if(array_key_exists('query', $auth)){
                        $query = array_merge($query, $auth['query']);
                    }
                    if(array_key_exists('data', $auth)){
                        $data = array_merge($data, $auth['data']);
                    }
                    if(array_key_exists('data', $test)){
                        $data = array_merge($data, $test['data']);
                    }

                    $this->_client = new Client([
                        'base_uri' => $base['url'],
                        'timeout' => 10,
                        'allow_redirects' => false,
                    ]);
                    $assert = $test['assert'];
                    $statusCode = $assert['code'];
                    $break = true;
                    try {
                        if($test['method'] == 'POST' || $test['method'] == 'PUT'){
                            $response = $this->_client->request($test['method'], $test['path'], [
                                'query' => $query,
                                'headers' => $header,
                                'form_params' => $data,
                            ]);
                        }else{
                            $response = $this->_client->request($test['method'], $test['path'], [
                                'query' => $query,
                                'headers' => $header,
                            ]);
                        }
                        $break = false;
                    } catch(Exception $e){
                        if($e->getCode() == $statusCode){
                            $this->_success($base, $auth, $test);
                            if($this->_debug){
                                if(array_key_exists('fields', $assert)){
                                    $this->_log('JSON config');
                                    print_r($assert['fields'], false);
                                    $this->_echo("\n");
                                }
                                $this->_log('JSON response');
                                if(isset($json)){
                                    print_r($json, false);
                                }
                                $this->_echo("\n======================\n\n");
                            }
                        }else{
                            $this->_error[] = 'The status code does not match the assert';
                            $this->_error($base, $auth, $test);
                        }
                        continue;
                    }

                    if($response->getStatusCode() != $statusCode){
                        $this->_error[] = 'The status code does not match the assert';
                        $this->_error($base, $auth, $test);
                        
                        continue;
                    }

                    if(array_key_exists('type', $assert)){
                        switch($assert['type']){
                            case 'json':
                                $body = $response->getBody();
                                $json = array();
                                while (!$body->eof()) {
                                    $json[] = $body->read(1024);
                                }
                                $json = trim(implode($json));
                                if(
                                    (substr($json, 0, 1) == '{' && substr($json, -1) == '}') ||
                                    (substr($json, 0, 1) == '[' && substr($json, -1) == ']')
                                ){
                                    $json = json_decode($json, true);
                                    if(!$this->_parseJson($assert['fields'], $json)){
                                        $this->_error[] = 'The tests[]->assert->fields does not match to test';
                                        $this->_error($base, $auth, $test);
                                        if($this->_debug){

                                            $this->_log('JSON config');
                                            print_r($assert['fields'], false);
                                            if($this->_saveFile){
                                                $this->_logFile(print_r($assert['fields'], true));
                                            }
                                            $this->_echo("\n");

                                            $this->_log('JSON response');
                                            print_r($json, false);
                                            if($this->_saveFile){
                                                $this->_logFile(print_r($json, true));
                                            }

                                            $this->_echo("\n======================\n\n");
                                        }
                                        continue;
                                    }else{
                                        $this->_success($base, $auth, $test);
                                        if($this->_debug){

                                            $this->_log('JSON config');
                                            print_r($assert['fields'], false);
                                            if($this->_saveFile){
                                                $this->_logFile(print_r($assert['fields'], true));
                                            }
                                            $this->_echo("\n");

                                            $this->_log('JSON response');
                                            print_r($json, false);
                                            if($this->_saveFile){
                                                $this->_logFile(print_r($json, true));
                                            }
                                            $this->_echo("\n======================\n\n");
                                        }
                                        continue;
                                    }
                                }else{
                                    $this->_error[] = 'The response of HTTP server no corresponds to a valid JSON format';
                                    $this->_error($base, $auth, $test);
                                    if($this->_debug){

                                        $this->_log('JSON config');
                                        print_r($assert['fields'], false);
                                        if($this->_saveFile){
                                            $this->_logFile(print_r($assert['fields'], true));
                                        }
                                        $this->_echo("\n");

                                        $this->_log('JSON response');
                                        print_r($json, false);
                                        if($this->_saveFile){
                                            $this->_logFile(print_r($json, true));
                                        }
                                        $this->_echo("\n======================\n\n");
                                    }
                                    continue;
                                }
                                if($this->_debug){
                                    $this->_log('JSON config');
                                    print_r($assert['fields'], false);
                                    if($this->_saveFile){
                                        $this->_logFile(print_r($assert['fields'], true));
                                    }
                                    $this->_echo("\n");
                                    $this->_log('JSON response');
                                    print_r($json, false);
                                    if($this->_saveFile){
                                        $this->_logFile(print_r($json, true));
                                    }
                                    $this->_echo("\n======================\n\n");
                                }
                                continue;
                        }
                    }else{
                        $this->_success($base, $auth, $test);
                        if($this->_debug){
                            $this->_log('JSON config');
                            print_r($assert['fields'], false);
                            if($this->_saveFile){
                                $this->_logFile(print_r($assert['fields'], true));
                            }
                            $this->_echo("\n");

                            $this->_log('JSON response');
                            if(isset($json)){
                                print_r($json, false);
                                if($this->_saveFile){
                                    $this->_logFile(print_r($json, true));
                                }
                            }
                            $this->_echo("\n======================\n\n");
                        }
                        continue;
                    }
                }
            }
            $this->_log('End: ' . date('Y-m-d H:i:s'));
            if(php_sapi_name() != 'cli' || $this->_return_logs){
                return implode($this->_echo);
            }
            if($this->_hasError){
                throw new ErrorTestException();
            }
            die(0);
        }

        private function _parseJson($required, $json)
        {
            if(is_array($required) && is_array($json)){

                $findFields = array();
                $success = array();
                $error = false;

                $valueToCheck = null;
                foreach($required as $value){
                    if(array_key_exists('name', $value)){
                        if(!array_key_exists($value['name'], $json)){
                            $error = true;
                        }
                    }
                    if(array_key_exists('name', $value) && array_key_exists('type', $value)){
                        if(array_key_exists('name', $value) && array_key_exists($value['name'], $json) && $value['type'] != gettype($json[$value['name']])){
                            // debug('error');
                            $error = true;
                        }
                    }
                    if(array_key_exists('name', $value) && array_key_exists($value['name'], $json) && array_key_exists('type', $value)){
                        if($value['type'] == 'array' && array_key_exists('fields', $value)){
                            $error = $this->_parseJson($value['fields'], $json[$value['name']]) ? false : true;
                        }
                        if(array_key_exists('name', $value) && array_key_exists('type', $value) && $value['type'] != 'array'){
                            if(array_key_exists('eq', $value)){
                                if($value['eq'] != $json[$value['name']]){
                                    $error = true;
                                }
                            }
                        }
                    }
                }
                if($error){
                    return false;
                }
                return true;
            }
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
            $this->_echo("[SLOG] $message \n");
            if($array && is_array($array)){
                print_r($array);
                if($this->_saveFile){
                    $this->_logFile(print_r($array, true));
                }
            }
            if($this->_saveFile){
                $this->_logFile('[SLOG] ' . $message . "\n");
            }
        }

        private function _echoWelcome()
        {
            $this->_echo("PHPatr version " . self::VERSION . "\n");
        }

        private function _error($base, $auth, $test)
        {
            $this->_hasError = 1;
            $this->_echo("[FAIL] " . $test['name'] . " \n");
            if(count($this->_error) > 0){
                foreach($this->_error as $run_error){
                    $this->_echo("[FLOG] $run_error \n");
                }
                $this->_error = array();
            }
            if($this->_saveFile){
                $this->_logFile('[FAIL] ' . $test['name'] . "\n");
            }
        }

        private function _true($message)
        {
            $this->_echo("[ OK ] " . $message . " \n");
            if($this->_saveFile){
                $this->_logFile('[ OK ] ' . $message . "\n");
            }
        }

        private function _success($base, $auth, $test)
        {
            $this->_echo("[ OK ] " . $test['name'] . " \n");
            if($this->_saveFile){
                $this->_logFile('[ OK ] ' . $test['name'] . "\n");
            }
        }

        private function _logFile($message)
        {
            if(empty($this->_saveFile)){
                throw new OutputFileEmptyException();
            }
            $fopen = fopen($this->_saveFile, 'a');
            fwrite($fopen, $message);
            fclose($fopen);
        }

        private function _resetLogFile()
        {
            if(is_file($this->_saveFile)){
                unlink($this->_saveFile);
            }
        }

        private function _help()
        {
            $this->_echo("   Usage:\n");
            $this->_echo("         Test API REST: \n");
            $this->_echo("     php phpatr.phar --config <config file> [--output <file>, [--debug]]  \n\n");
            $this->_echo("         Generate example JSON configuration: \n");
            $this->_echo("     php phpatr.phar --example-config-json  \n\n");
            $this->_echo("         Self Update: \n");
            $this->_echo("     php phpatr.phar --self-update  \n\n");
            $this->_echo("         Help: \n");
            $this->_echo("     php phpatr.phar --help  \n\n");
            $this->_echo("    Options:\n");
            $this->_echo("      -d,  --debug                            Debug the calls to API REST  \n");
            $this->_echo("      -c,  --config                             File of configuration in JSON to test API REST calls  \n");
            $this->_echo("      -e,  --example-config-json                 Generate a example file JSON to configuration  \n");
            $this->_echo("      -o,  --output                             Output file to save log  \n");
            $this->_echo("      -u,  --self-update                        Upgrade to the latest version version  \n");
            $this->_echo("      -v,  --version                            Return the installed version of this package  \n");
            $this->_echo("      -h,  --help                              Show this menu  \n");
            if(php_sapi_name() != 'cli' || $this->_return_logs){
                return implode($this->_echo);
            }
            die(0);
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
            $version = trim(implode($version));
            $_cdn_version = str_replace('.', '', $version);

            if($returnVersion){
                return $version;
            }

            $_local_version = self::VERSION;
            $_local_version = str_replace('.', '', $_local_version);

            if($_local_version != $_cdn_version){
                $this->_messageUpdate($version);
            }
        }

        private function _messageUpdate($version)
        {
            $this->_echo("UPDATE:  There is a new version available!  \n");
            $this->_echo("UPDATE:  self::VERSION -> $version  \n");
            $this->_echo("UPDATE:  Automatic: Run the self-update: php phpatr.phar --self-update  \n");
            $this->_echo("UPDATE:  Manual: visit the GitHub repository and download the latest version (https://github.com/00F100/phpatr/)  \n");
        }

        private function _selfUpdate()
        {
            $this->_echo('Your version: ' . self::VERSION . "\n");
            $selfUpdate = new PHPUPhar($this->_update, false, self::VERSION, $this->_download, 'phpatr.phar');
            $this->_echo("Versão em 00F100/phpatr: " . $selfUpdate->getVersion() . " \n");
            if(self::VERSION == $selfUpdate->getVersion()){
                $this->_echo("A sua versão esta atualizada!\n");
            }
            if (self::VERSION != $selfUpdate->getVersion() && $selfUpdate->update()) {
                $this->_echo("A sua versão foi atualizada com sucesso!\n");
            }
            exit(0);
        }

        private function _version()
        {
            $this->_echo(self::VERSION);
            if(php_sapi_name() != 'cli' || $this->_return_logs){
                return implode($this->_echo);
            }
            die(0);
        }

        private function _exampleConfigJson()
        {
             $this->_log('Loading example file');
             $content = $this->_returnJson();
            $fopen = fopen('phpatr.json', 'w');
            fwrite($fopen, $content);
            fclose($fopen);
             $this->_true('Save new file in: "./phpatr.json"');
             if(php_sapi_name() != 'cli' || $this->_return_logs){
                 $this->_echo($content);
                return implode($this->_echo);
            }
                 die(0);
        }

        private function _echo($var)
        {
            if(php_sapi_name() == 'cli' && !$this->_return_logs){
                echo $var;
            }else{
                $this->_echo[] = $var;
            }
        }

        private function _returnJson()
        {
            return '{
                            "name": "Test reqres.in",
                            "base": [
                                {
                                    "name": "httpbin.org",
                                    "url": "http://httpbin.org",
                                    "query": {},
                                    "header": {}
                                }
                            ],
                            "auth": [
                                {
                                    "name": "noAuth",
                                    "query":{},
                                    "header": {},
                                    "data": {}
                                }
                            ],
                            "tests": [
                                {
                                    "name": "Test to get IP",
                                    "base": "httpbin.org",
                                    "auth": "noAuth",
                                    "path": "/ip",
                                    "method": "GET",
                                    "query": {},
                                    "header": {},
                                    "data": {},
                                    "assert": {
                                        "type": "json",
                                        "code": 200,
                                        "fields": [
                                            {
                                                "name": "origin",
                                                "type": "string"
                                            }
                                        ]
                                    }
                                },
                                {
                                    "name": "Test to POST data",
                                    "base": "httpbin.org",
                                    "auth": "noAuth",
                                    "path": "/post",
                                    "method": "POST",
                                    "query": {},
                                    "header": {},
                                    "data": {
                                        "posttest": "95ddcb76ded165f81607e3f050070946"
                                    },
                                    "assert": {
                                        "type": "json",
                                        "code": 200,
                                        "fields": [
                                            {
                                                "name": "form",
                                                "type": "array",
                                                "fields": [
                                                    {
                                                        "name": "posttest",
                                                        "type": "string",
                                                        "eq": "95ddcb76ded165f81607e3f050070946"
                                                    }
                                                ]
                                            }
                                        ]
                                    }
                                },
                                {
                                    "name": "Test not found 404",
                                    "base": "httpbin.org",
                                    "auth": "noAuth",
                                    "path": "/status/404",
                                    "method": "GET",
                                    "query": {},
                                    "header": {},
                                    "data": {},
                                    "assert": {
                                        "code": 404
                                    }
                                },
                                {
                                    "name": "Test status teapot",
                                    "base": "httpbin.org",
                                    "auth": "noAuth",
                                    "path": "/status/418",
                                    "method": "GET",
                                    "query": {},
                                    "header": {},
                                    "data": {},
                                    "assert": {
                                        "code": 418
                                    }
                                }
                            ]
                        }';
        }

        public function exceptionHandler(Exception $exception)
        {
            $this->_echo('[FLOG] ' . $exception->getMessage());
        }
    }
}
