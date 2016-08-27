<?php

namespace TestApiRest\Exceptions
{
	use TestApiRest\Exceptions\TestApiRestException;

	class ConfigFileNotFoundException extends TestApiRestException
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
