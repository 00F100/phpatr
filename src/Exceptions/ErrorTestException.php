<?php

namespace TestApiRest\Exceptions
{
	use TestApiRest\Exceptions\TestApiRestException;

	class ErrorTestException extends TestApiRestException
	{
		protected $message = 'Test  Api RET fail';
		protected $code = 500;
	}
}
