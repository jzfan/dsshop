<?php

namespace app\mobile\controller\api;

use think\Controller;

abstract class BaseController extends Controller
{
	protected $model;

    protected function _initialize()
    {
    	$this->model = $this->getModel();
    }

    abstract public function getModel();
}