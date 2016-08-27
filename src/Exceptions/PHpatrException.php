<?php

namespace PHPatr\Exceptions
{
	use Exception;

	class PHpatrException extends Exception
	{
		public function __construct()
		{
			die(1);
			// $message = $this->message;
			// $code = $this->code;
			// debug(compact('code', 'message'));
		}
	}
}
