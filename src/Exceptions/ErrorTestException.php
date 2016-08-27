<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\PHpatrException;

	class ErrorTestException extends PHpatrException
	{
		protected $message = 'Test  Api RET fail';
		protected $code = 500;
	}
}
