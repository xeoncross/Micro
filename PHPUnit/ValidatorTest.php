<?php
class ValidatorTest extends PHPUnit_Framework_TestCase
{
	public function testString()
	{
		$validator = new \Micro\Validator();

		$validator->short = function($value, $key, $this)
		{
			if( ! $this::string($value, 20, 10))
			{
				return $key;
			}
		};

		$validator->normal = function($value, $key, $this)
		{
			if( ! $this::string($value, 20, 10))
			{
				return $key;
			}
		};

		$validator->long = function($value, $key, $this)
		{
			if( ! $this::string($value, 20, 10))
			{
				return $key;
			}
		};

		$validator->notstring = function($value, $key, $this)
		{
			if( ! $this::string($value, 20, 10))
			{
				return $key;
			}
		};

		$_POST = array(
			'short' => 'string',
			'normal' => 'a normal string',
			'long' => 'this string is much too long to be used',
			'notstring' => array('string')
		);

		// Several of the values should fail
		$this->assertFalse($validator($_POST));

		$errors = $validator->errors();

		$expected = array(
			'short' => 'short',
			'long' => 'long',
			'notstring' => 'notstring'
		);

		// Are the problem fields what we expected?
		$this->assertSame($errors, $expected);
	}
}

