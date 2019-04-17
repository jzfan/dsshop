<?php

function dd($var)
{
	var_dump($var);
	exit;
}

function checkInput($rules, $input = null)
{
    $input = $input ?? input();
    
    $v = \think\Loader::validate();
    $v->rule($rules);

    if (!$v->check($input)) {
        throw new \app\common\JsonException($v->getError(), 422);
    }
    $keys = array_map(function ($row) {
        return explode('|', $row)[0];
    }, array_keys($rules));
    return request()->only($keys);
}