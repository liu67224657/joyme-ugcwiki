<?php
/**
 * Created by PhpStorm.
 * User: xinshi
 * Date: 2015/6/15
 * Time: 16:31
 */

class InterestTesting{

    function setTestingValue($vals,$len){

        global $com,$wgMemc;

        $wikiid = wfWikiID();
        $Prefix = $wikiid."|".$com."|". __CLASS__ ."| ".$vals;

        if(!$wgMemc->get($Prefix)){
            $num = mt_rand(0,$len);
            if($num>=0){
                $wgMemc->set($Prefix,$num,$this->expiresTestingTime());
                $result = $num;
            }else{
                $result = false;
            }
        }else{
            $result = $wgMemc->get($Prefix);
        }
        return $result;
    }

    //计算有效期
    function expiresTestingTime(){
        //获取零点的时间戳
        $time = mktime(00,00,00,date("m"),date("d")+1,date("Y"))-time();
        return $time;
    }
}

