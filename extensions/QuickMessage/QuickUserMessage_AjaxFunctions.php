<?php

$wgAjaxExportList[] = 'wfQuickMessageAgainSend';

function wfQuickMessageAgainSend( $umi_id,$umi_status ,$send_um_id)
{
    /**
     * The user center QuickMessage
     * @return Integer: time
     */
    if( empty($umi_id) || empty($umi_status)){
        return false;
    }
    global $wgUser;

    if($wgUser->isAllowed( 'quickmessage' )){
        $ret = QuickMessageAgainSend::againSend( $umi_id ,$umi_status,$send_um_id);
        if($ret){
            $data = array('time'=>date('Y-m-d H:i:s',time()));
        }else{
            $data = false;
        }
        $res = RecommendUsers::returnJson( $data );
        echo $res;
        exit;
    }
}









