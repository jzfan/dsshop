<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-15
 * Time: 14:10
 */

abstract class AbstractRequest
{
    public $gateway = "";

    public $protocol = "httpPost";

    public $signType = "MD5";

    public $partnerId;

    public $secret;

    public $version = "1.0";

    public $charset = "UTF-8";

    public $notifyUrl;

    public $returnUrl;

    public $bizContent = array();



    public function execute()
    {
        $this->validate();

        $requestParams = array_merge(array(
            "partnerId" =>  $this->partnerId,
            "protocol"  =>  $this->protocol,
            "signType"  =>  $this->signType,
            "version"   =>  $this->version,
            "notifyUrl" =>  $this->notifyUrl,
            "returnUrl" =>  $this->returnUrl,
        ),$this->bizContent);

        $requestParams['sign'] = $this->makeSign($requestParams);

        return $this->buildRequestForm($this->gateway, $requestParams);
    }


    public function validate()
    {
        foreach ($this->bizContent as $key =>$item) {
            if (empty($item)) {
                throw new \Exception("{$key} should not be null");
            }
        }
    }


    public function buildRequestForm($requestParams, $method = 'POST')
    {
        $sHtml = "<form id='form' name='form' action='{$this->gateway}?' method='{$method}'>";

        foreach ($requestParams as $key => $value) {
            if (!empty($value)) {
                $sHtml.= "<input type='hidden' name='{$key}' value='{$value}'/>";
            }
        }

        $sHtml = $sHtml."<input type='submit' value='ok' style='display:none;'></form>";

        $sHtml = $sHtml."<script>document.forms['form'].submit();</script>";

        return $sHtml;
    }


    public function verify($requestParams)
    {
        return $requestParams == $this->makeSign($requestParams);
    }


    public function makeSign($requestParams)
    {
        $stringToBeSigned = $this->getSignContent($requestParams);

        return $stringToBeSigned;
    }


    public function getSignContent($requestParams)
    {
        ksort($requestParams);

        $stringToBeSigned = "";

        foreach ($requestParams as $key => $value) {

            if (!empty($value) && $key != "sign") {
                $stringToBeSigned .= $key . "=" . $value . "&";
            }
        }
        return trim($stringToBeSigned,"&");
    }
}