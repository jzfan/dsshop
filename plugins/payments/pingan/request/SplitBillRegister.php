<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-15
 * Time: 14:21
 */

class SplitBillRegister extends AbstractRequest
{

    protected $bizContent = array(
        "service"   =>  "zlx.splitBill.registered"
    );


    public function setNickName($nickName)
    {
        $this->bizContent['nickName'] = $nickName;
        return $this;
    }

    public function getNickName()
    {
        return $this->bizContent['nickName'];
    }

    public function setMobile($mobile)
    {
        $this->bizContent['mobile'] = $mobile;
        return $this;
    }

    public function getMobile()
    {
        return $this->bizContent['mobile'];
    }

}