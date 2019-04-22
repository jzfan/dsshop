<?php

namespace app\common;

class DsRedis
{
	const HOST = '127.0.0.1';
	const PORT = 6379;

	public static $instance;

	private function __construct()
	{
		self::$instance = new \Redis;
		self::$instance->connect(self::HOST, self::PORT); 
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

}