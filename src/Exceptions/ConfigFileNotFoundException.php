<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\PHpatrException;

	class ConfigFileNotFoundException extends PHpatrException
	{
		protected $message = 'The file on config the test not found in "%s"';
		protected $code = 500;

		public function __construct($message)
		{
			$this->message = sprintf($this->message, $message);
			return parent::__construct();
		}
	}
}
