
PHPTar - Test Api Rest
========================================

Test your API REST on Jenkins based on JSON file!

Installation
--------------------

[Download Phar file]()

Configuration
--------------------

Configure the file "phptar.json":

```
{
	"name": "[NAME OF TESTS]",
	"base": [
		{
			"name": "firstBase",
			"url": "http://example.cm",
			"query": {
				"type": "json"
			},
			"header": {
				"Content-Type": "application/json"
			}
		}
	],
	"auth": [
		{
			"name": "firstAuth",
			"type": "GET",
			"query":{
				"access_token": "xyz"
			},
			"header": {}
		}
	],
	"tests": [
		{
			"name": "First test - list of users",
			"base": "firstBase",
			"auth": "firstAuth",
			"path": "/users",
			"query": {},
			"header": {},
			"assert": {
				"type": "json",
				"code": 200,
				"fields": [
					{
						"id": "integer",
						"name": "string",
						"email": "string"
					}
				]
			}
		}
	]
}
```

Usage
--------------------

```
$ php phptar.phar  --config ./phptar.json
```