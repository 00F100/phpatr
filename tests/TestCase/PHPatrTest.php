<?php

namespace PHPatr\Test\TestCase
{
	use PHPUnit_Framework_TestCase as PHPunit;
	use PHPatr\PHPatr;

	class PHPatrTest extends PHPunit
	{
		const MD5_HELP = 'bd121953221dea860e514b99f5eb0ef9';

		private $_PHPatr;

		public function __construct()
		{
			$this->_PHPatr = new PHPatr(true);
		}

		public function testHelp()
		{
			$output = $this->_PHPatr->init('-h');
			$this->assertTrue(strpos($output, 'PHPatr version 0.8.0') !== false);
		}

		public function testVersion()
		{
			$output = $this->_PHPatr->init('-v');
			$this->assertTrue($output == '0.8.0');
		}

		public function testExample()
		{
			$output = $this->_PHPatr->init('-e');
			$this->assertTrue(strpos($output, 'Save new file in: "./phpatr.json"') !== false);
		}
	}
}