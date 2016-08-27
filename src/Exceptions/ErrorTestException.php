<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\TestApiRestException;

	class ErrorTestException extends TestApiRestException
	{
		protected $message = 'Test  Api RET fail';
		protected $code = 500;
	}
}
