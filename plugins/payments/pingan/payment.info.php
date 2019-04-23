<?php

return array(
    'payment_code' => 'pingan',
    'payment_name' => '平安支付',
    'payment_desc' => '平安支付接口',
    'payment_is_online' => '1',
    'payment_platform' => 'h5', #支付平台 pc h5 app
    'payment_author' => '长沙德尚',
    'payment_website' => 'http://www.alipay.com',
    'payment_version' => '1.0',
    'payment_config' => array(
        array('name' => 'alipay_appid', 'type' => 'text', 'value' => '', 'desc' => '描述'),
        array('name' => 'public_key', 'type' => 'textarea', 'value' => '', 'desc' => '描述'),
        array('name' => 'private_key', 'type' => 'textarea', 'value' => '', 'desc' => '描述'),
    ),
);
?>
