<?php

namespace app\common;

class DsRedis extends \Redis
{
	const HOST = '127.0.0.1';
	const PORT = 6379;

	public static $instance;

	private function __construct()
	{
		parent::__construct();
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self;
			self::$instance->connect(self::HOST, self::PORT);
		}
		return self::$instance;
	}

	public function setLock($key)
	{
	    while(true) {
	        if ($this->set($key, 1, ['nx', 'ex'=>5])) {
	            break;
	        }
	        sleep(rand(200, 300));
	    }
	}

	public function releaseLock($key)
	{
	    $this->delete($key);
	}

}