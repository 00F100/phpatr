
PHPatr - Simple Api Test REST
========================================

[![Jenkins](https://img.shields.io/badge/build%20Jenkins-passing-brightgreen.svg)](https://codeclimate.com/github/00F100/phpatr) [![Travis](https://img.shields.io/travis/00F100/phpatr.svg?maxAge=2592000)]() [![coverage](https://img.shields.io/codecov/c/github/00F100/phpatr.svg)](https://codecov.io/gh/00F100/phpatr) [![Code Climate](https://img.shields.io/codeclimate/github/00F100/phpatr.svg)](https://codeclimate.com/github/00F100/phpatr) [![MIT](https://img.shields.io/dub/l/vibe-d.svg?maxAge=2592000)](https://codeclimate.com/github/00F100/phpatr) 

Package to [Smoke testing](https://en.wikipedia.org/wiki/Smoke_testing_(software))

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
}
```

Usage
--------------------

```
PHPatr version 0.7.0
   Usage:
         Test API REST: 
	 php phpatr.phar --config <config file> [--output <file>, [--debug]]  

         Generate example JSON configuration: 
	 php phpatr.phar --example-config-json  

         Self Update: 
	 php phpatr.phar --self-update  

         Help: 
	 php phpatr.phar --help  

	Options:
	  -d,  --debug                    			Debug the calls to API REST  
	  -c,  --config                     		File of configuration in JSON to test API REST calls  
	  -e,  --example-config-json         		Generate a example file JSON to configuration  
	  -o,  --output                     		Output file to save log  
	  -u,  --self-update                		Upgrade to the latest version version  
	  -v,  --version                    		Return the installed version of this package  
	  -h,  --help                      			Show this menu  
```

How to:
--------------------

Execute test:

```
$ php phpatr.phar --config <config file> [--output <file>, [--debug]]

	Options:
	  -c,  --config                     File of configuration in JSON to test API REST calls  
	  -o,  --output                    Output file to save log
	  -d,  --debug                     Debug the calls to API REST   
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
[FLOG] Test failed
```
