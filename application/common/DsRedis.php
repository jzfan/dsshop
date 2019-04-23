<?php

namespace app\common;

class DsRedis
{
	const HOST = '47.110.235.2';
	const PORT = 6379;
	const PASSWD = '123456';

	public static $instance;

	private function __construct()
	{
		
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new \Redis;
			self::$instance->connect(self::HOST, self::PORT);
			self::$instance->auth(self::PASSWD);
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