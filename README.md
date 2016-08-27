
PHPatr - Api Test REST
========================================

Test your API REST on Jenkins based on JSON file!

Easy configuration and secure result!

Installation
--------------------

[Download Phar file](https://raw.githubusercontent.com/00F100/phpatr/master/dist/phpatr.phar)

Configuration
--------------------

Configure the file "phpatr.json":

Example:

```json
{
	"name": "Test jsonplaceholder.typicode.com",
	"base": [
		{
			"name": "jsonPlaceholder",
			"url": "https://jsonplaceholder.typicode.com",
			"query": {},
			"header": {}
		}
	],
	"auth": [
		{
			"name": "noAuth",
			"type": "GET",
			"query":{},
			"header": {}
		}
	],
	"tests": [
		{
			"name": "Test comments",
			"base": "jsonPlaceholder",
			"auth": "noAuth",
			"path": "/comments",
			"query": {},
			"header": {},
			"assert": {
				"type": "json",
				"code": 200,
				"fields": [
					{
						"id": "integer",
						"name": "string",
						"body": "string"
					}
				]
			}
		},
		{
			"name": "Test posts",
			"base": "jsonPlaceholder",
			"auth": "noAuth",
			"path": "/posts",
			"query": {},
			"header": {},
			"assert": {
				"type": "json",
				"code": 200,
				"fields": [
					{
						"id": "integer",
						"title": "string",
						"body": "string"
					}
				]
			}
		},
		{
			"name": "Page error 404",
			"base": "jsonPlaceholder",
			"auth": "noAuth",
			"path": "/error",
			"query": {},
			"header": {},
			"assert": {
				"type": "json",
				"code": 404,
				"fields": {}
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
joao@joao /m/j/2/96-php-scripts> php phpatr.phar -h
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