<?php

namespace app\mobile\controller;


class Document extends MobileMall
{
    public function _initialize()
    {
        parent::_initialize(); 
    }
    public function agreement() {
        $type=input('param.type');
        if(!$type){
            $type='agreement';
        }
        $doc = model('document')->getOneDocumentByCode($type);
        ds_json_encode(10000, '', $doc);
    }
}