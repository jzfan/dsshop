<?php
namespace app\common;

trait ModelTrait
{
	public function exists($where)
	{
		$count = $this->where($where)->count();
		return $count > 0 ? true : false;
	}

	public function updateOrCreate($attr, $parms)
	{
		$find = $this->where($attr)->find();
		if ($find) {
			return $find->save($parms);
		}
		return $this->create($parms);
	}

	public function decr($key, $num)
	{
		$val = $this->$key - $num;	
		$this->$key = $val;
		return $this->save();
	}
}
