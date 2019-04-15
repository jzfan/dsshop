<?php

namespace app\api\controller;

use think\Controller;

class Goods extends Controller
{
	public function categories()
	{
		$pid = input('pid');
		$categories = model('goodsclass')->getGoodsclassListByParentId($pid);
		return json_encode($categories);
	}
}