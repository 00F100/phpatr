
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

Example return
--------------------

```
user@ubuntu /path/to/project> php phpatr.phar --config ./phpatr.json 
LOG: Start: 2016-08-27 03:13:25
LOG: Config File: ./phpatr.json
LOG: Test Config: Test jsonplaceholder.typicode.com
LOG: Run Tests!
[ OK ] Test comments
[ OK ] Test posts
[ OK ] Page error 404
LOG: End: 2016-08-27 03:13:26
```