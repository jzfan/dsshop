<?php

namespace app\api\controller;

use think\Controller;

class Goods extends Controller
{
	public function categories()
	{
		$categories = model('goodsclass')->getGoodsclassListByParentId(input('pid'));
		return json($categories);
	}

	public function index()
	{
		$cid = input('cid');
		$goods = db('goods')->where('gc_id_3', $cid)
							->whereOr('gc_id_2', $cid)
							->whereOr('gc_id_1', $cid)
							->whereOr('gc_id', $cid)
							->select(['goods_id', 'goods_name', 'goods_image']);
		$goods = array_map(function ($item) {
			$item['thumb'] = goods_thumb($item, 240);
			return $item;
		}, $goods);
		return json($goods);
	}
}