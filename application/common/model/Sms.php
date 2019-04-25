<?php
/**
 * Created by 91go.miaozancn.com
 * @Date: 2019/4/25
 * @Time: 15:55
 * @auther: mark_haibo
 * @email:920793414@qq.com
 * @File:Sms.php
 */
namespace app\common\model;
use think\Model;
use think\Cache;
use Overtrue\EasySms\EasySms;
class Sms extends Model
{
    private $api_account = 'N3014664';//创蓝账号
    private $api_password = 'PMWyf2i7v9fd78';//创蓝密码
    private $sign_name = '【爱秒赞】';
    public function __construct()
    {
        $this->config = [
            'timeout' => 5.0,
            'default' => [
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                'gateways' => [
                    'chuanglan',
                ],
            ],
            'gateways' => [
                'chuanglan' => [
                    'account' =>$this->api_account   ,
                    'password' => $this->api_password,
                    'channel' => \Overtrue\EasySms\Gateways\ChuanglanGateway::CHANNEL_VALIDATE_CODE,
                    'sign' => $this->sign_name,
                    'unsubscribe' => '回TD退订',
                ],
            ],
        ];

    }

    public function send($phone){
        $easySms = new EasySms($this->config);
        $code = $this->genercode();
        Cache::set($phone,$code,600);//设置yzm失效
        $content = '您好，您的验证码是' . $code;
        $content = $this->config['gateways']['chuanglan']['sign'] . $content;
        $res = $easySms->send($phone, $content);
        if($res['chuanglan']['status'] == 'success' && $res['chuanglan']['result']['code'] == 0){
            ds_json_encode(10000, '发送成功');
        }else{
            ds_json_encode(10001, '发送失败');
        }
    }

    public function genercode($length = 6)
    {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

}