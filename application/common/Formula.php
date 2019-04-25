<?php

namespace app\common;

class Formula
{
	private payClientRate;
	private feeRate;
	private $marketPrice;
	private $seckillPrice;
	private $returnRate;

	public function __construct($marketPrice, $seckillPrice, $returnRate)
	{
		$this->config();
		$this->marketPrice = $marketPrice;
		$this->seckillPrice = $seckillPrice;
		$this->returnRate = $returnRate;
	}

	public function config()
	{
		
	}

	public function payClient()
	{
		return $this->payClientRate * $this->marketPrice;
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
		$marketPrice = Goods::where('goods_id', $input['goods_id'])->find();
		$fo = new self($marketPrice, $input['price'], $input['return_rate']/100);
		return $fo->mi();
	}

}