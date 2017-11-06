<?php
class QuickMessageAgainSend{

    //消息重发
    static function againSend( $umi_id,$umi_status,$send_um_id ){

        global $wgUser;
        $data = QuickUserMessageItemClass::select_Row_By_Umi_Id( $umi_id );
        if($data){
            if(SendSystemMessage::pullQuickMessage( $data->user_id,$wgUser->mId,$wgUser->getName(),$data->content,$data->um_id)){

                if($umi_status == 2){
                    QuickUserMessageClass::update_Success_Num( $send_um_id );
                }
                return QuickUserMessageItemClass::updateSendTime($umi_id,$umi_status);
            }else{
                return false;
            }
        }
    }
}