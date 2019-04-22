<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-17
 * Time: 15:55
 */

namespace app\common\validate;


use think\Validate;

class Pointgoods extends Validate
{
    protected $rule = [
        ['goods_price', 'require', '商品价格不能为空'],
        ['goods_point', 'require', '密码不能为空'],
        ['goods_storage', 'require', '商品库存不能为空'],
    ];

    protected $scene = [
        'edit_save_goods' => ['goods_price', 'goods_point','goods_storage','goods_state'],
    ];
}