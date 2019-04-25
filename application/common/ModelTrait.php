<?php
namespace app\common;

use think\Db;

trait ModelTrait
{
	public function exists($where)
	{
		$count = self::where($where)->count();
		return $count > 0 ? true : false;
	}

	public static function updateOrCreate($attr, $parms)
	{
		return Db::transaction(function () use ($attr, $parms) {
			$find = self::where($attr)->lock(true)->find();
			if ($find) {
				return $find->save($parms);
			}
			return self::create($parms);
		});
	}

}
