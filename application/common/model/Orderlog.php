<?php

namespace app\common\model;

use think\Model;

class Orderlog extends Model
{
	const ROLE = [
		'buyer' => '买家',
		'system' => '系统',
		'admin' => '管理员',
	];

	public function write($data)
	{
		$data['log_role'] = $data['log_role'] ?? 'admin';
		
		return self::create(array_merge([
			'log_role' => 'system'
			'log_role' => self::ROLE[$data['log_role']],
			'log_time' => TIMESTAMP,
			'log_user' => 'admin',
		], $data));
	}
}