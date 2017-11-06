<?php
class GetSystemMessageClass{

    static function getIndex(){

        global $wgUser;

        $model = new JoymeWikiUser();
        $userData = $model->getWikiUser($wgUser->mId);
        $userTime = strtotime($userData->time);
        if(empty($userTime)){
            $userTime = 1467302400;
        }
        //是否已有全部的系统消息
        if(joymeEchoEven::getMessageCountByTyle( $userTime ) == SystemMessageClass::select_System_Message_Count( $userTime )){
            return ;
        }else{
            //查看该用户有哪些系统消息
            $res = joymeEchoEven::getMessageByTyle( );
            $um_ids = array();
            if($res){
                foreach($res as $k=>$v){
                    $um_ids[] = $v->event_variant;
                }
            }
            //查询未接收的消息
            $messages = SystemMessageClass::select_System_Message($um_ids,$userTime);
            if( $messages->numRows() ){
                //拉取消息;
                foreach($messages as $mk=>$mv){
                    if($mv->wiki_keys){
                        $keys = explode(',',$mv->wiki_keys);
                        $site_ids = SystemMessageClass::getWikiSiteId($keys);
                        //判断消息发放是否针对wiki
                        if(SystemMessageClass::userIdExistInRelation( $wgUser->mId,$site_ids ,strtotime($mv->um_date) )){
                            SendSystemMessage::pullJoymeSystemMessage( $mv );
                        }
                    }else{
                        SendSystemMessage::pullJoymeSystemMessage( $mv );
                    }
                }
            }
        }
    }
}