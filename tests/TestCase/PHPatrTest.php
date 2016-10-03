<?php

namespace PHPatr\Test\TestCase
{
	use PHPUnit_Framework_TestCase as PHPunit;
	use PHPatr\PHPatr;

	class PHPatrTest extends PHPunit
	{
		const VERSION='0.12.0';
		private $_PHPatr;

		public function __construct()
		{
			$this->_PHPatr = new PHPatr(true);
		}

		public function testHelp()
		{
			$output = $this->_PHPatr->init('-h');
			$this->assertTrue(strpos($output, 'PHPatr version ' . self::VERSION) !== false);
		}

		public function testVersion()
		{
			$output = $this->_PHPatr->init('-v');
			$this->assertTrue($output == self::VERSION);
		}

		public function testExample()
		{
			$output = $this->_PHPatr->init('-e');
			$this->assertTrue(strpos($output, 'Save new file in: "./phpatr.json"') !== false);
		}

		/**
		  * @expectedException     PHPatr\Exceptions\PHpatrException
		  */
		public function testConfigNoFileException()
		{
			$output = $this->_PHPatr->init('-c');
		}

		/**
		  * @expectedException     PHPatr\Exceptions\ConfigFileNotFoundException
		  */
		public function testConfigFileNotFoundException()
		{
			$output = $this->_PHPatr->init('-c', 'filenotfound');
		}

		public function testConfigSuccess()
		{
			$output = $this->_PHPatr->init('-c', 'phpatr.json');
			$this->assertTrue(strpos($output, 'PHPatr version ' . self::VERSION) !== false);
		}

		public function testConfigSuccessOutput()
		{
			$output = $this->_PHPatr->init('-c', 'phpatr.json', '-o', 'log');
			$this->assertTrue(strpos($output, 'PHPatr version ' . self::VERSION) !== false);
		}
	}
}
