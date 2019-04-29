<?php

namespace app\common;

use app\common\model\Goods;
use app\common\model\Config;

class Formula
{
	private $payClientRate;
	private $feeRate;
	private $price;
	private $seckillPrice;
	private $returnRate;

	public function __construct($price, $seckillPrice, $returnRate)
	{
		$this->config();
		$this->price = $price;
		$this->seckillPrice = $seckillPrice;
		$this->returnRate = $returnRate;
	}

	protected function config()
	{
		$this->payClientRate = Config::getValue('forsale_bill_member_rate')/100;
		$this->feeRate = Config::getValue('forsale_bill_platform_rate')/100;

	}

	public function payClient()
	{
		return $this->payClientRate * $this->price;
	}

	public function fee()
	{
		return round(($this->payClient() - $this->seckillPrice * (1 + $this->returnRate)) * $this->feeRate, 2);
	}

	public function mi()
	{
		return round($this->fee() / (1 + $this->returnRate));
	}

	public static function miByInput($input)
	{
		$price = Goods::where('goods_id', $input['goods_id'])->find()->goods_price;
		$fo = new self($price, $input['price'], $input['return_rate']/100);
		return $fo->mi();
	}

	public function __get($key)
	{
		return $this->$key ?? $this->$key() ?? null;
	}

}