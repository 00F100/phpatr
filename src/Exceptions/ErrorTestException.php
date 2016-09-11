<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\PHpatrException;

	class ErrorTestException extends PHpatrException
	{
		protected $message = 'Test failed';
		protected $code = 500;
	}
}
