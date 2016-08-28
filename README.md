
PHPatr - Api Test REST
========================================

Test your API REST on Jenkins based on JSON file!

Easy configuration and secure result!

Installation
--------------------

```
$ wget https://raw.githubusercontent.com/00F100/phpatr/master/dist/phpatr.phar
```

or

[Download Phar file](https://raw.githubusercontent.com/00F100/phpatr/master/dist/phpatr.phar)

Configuration
--------------------

Configure the file "phpatr.json":

Example:

```json
{
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
				"fields": {
					"origin": "string"
				}
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
				"posttest": "return to post"
			},
			"assert": {
				"type": "json",
				"code": 200,
				"fields": {
					"data": "string",
					"json": {
						"posttest": "string"
					}
				}
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
}
```

Usage
--------------------

```
php phpatr.phar -c <config file>  

	Options:
	  -c, --config                     File of configuration in JSON to test API REST calls  
	  -h, --help                       Show this menu  
	  -o, --output                     Output file to save log 
```

How to:
--------------------

Execute test:

```
$ php phpatr.phar --config <config file> [--output <file>]

	Options:
	  -c,  --config                     File of configuration in JSON to test API REST calls  
	  -o,  --output                    Output file to save log  
```

Update:

```
$ php phpatr.phar --self-update
```

Help:

```
$ php phpatr.phar --help
```

Example "execute test" return success:

```
user@ubuntu /path/to/project> php phpatr.phar --config phpatr.json
[SLOG] Start: 2016-08-27 15:40:11 
[SLOG] Config File: phpatr.json 
[SLOG] Test Config: Test reqres.in 
[SLOG] Run Tests! 
[ OK ] Test users single vetor 
[ OK ] Test users vector multilevel 
[ OK ] Example error: Test users vector multilevel 
[SLOG] End: 2016-08-27 15:40:12 

```

Example "execute test" return error:

```
user@ubuntu /path/to/project> php phpatr.phar --config phpatr.json
[SLOG] Start: 2016-08-27 15:40:11 
[SLOG] Config File: phpatr.json 
[SLOG] Test Config: Test reqres.in 
[SLOG] Run Tests! 
[ OK ] Test users single vetor 
[ OK ] Test users vector multilevel 
[FAIL] Example error: Test users vector multilevel 
[FLOG] The tests[]->assert->fields does not match to test 
[SLOG] End: 2016-08-27 15:40:12 

```

Example "help"

```
user@ubuntu /m/j/2/96-php-scripts> php phpatr.phar -h
   Usage:
         Test API REST: 
	 php phpatr.phar --config <config file> [--output <file>]  

         Self Update: 
	 php phpatr.phar --self-update  

         Help: 
	 php phpatr.phar --help  

	Options:
	  -c,  --config                     File of configuration in JSON to test API REST calls  
	  -h,  --help                       Show this menu  
	  -o,  --output                     Output file to save log  
	  -u, --self-update                Upgrade to the latest version version  
	  -v,  --version                    Return the installed version of this package

```