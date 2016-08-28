<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\PHpatrException;

	class ConfigFileEmptyException extends PHpatrException
	{
		protected $message = 'The file on config has empty';
		protected $code = 500;
	}
}
