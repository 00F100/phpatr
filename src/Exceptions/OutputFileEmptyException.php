<?php

namespace PHPatr\Exceptions
{
	use PHPatr\Exceptions\PHpatrException;

	class OutputFileEmptyException extends PHpatrException
	{
		protected $message = 'The file to output has empty';
		protected $code = 500;
	}
}
