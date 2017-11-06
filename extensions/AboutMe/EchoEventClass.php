<?php
class joymeEchoEven{

    static function getMessageByTyle( $type = 'echo-system-message' ){

        global $wgUser;
        $dbw = wfGetDB( DB_MASTER );
        $events = array();
        $res = $dbw->select(
            'echo_event',
            '*',
            array(
                'event_type'=>$type,
                'event_agent_id'=>$wgUser->mId,
                'event_page_namespace is null',
            )
        );
        if($res->numRows()){
            foreach($res as $v){
                $events[] = $v;
            }
        }
        return $events;
    }


    static function getMessageCountByTyle( $time ,$type = 'echo-system-message'){

        global $wgUser;
        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            'echo_event',
            '*',
            array(
                'event_type'=>$type,
                'event_agent_id'=>$wgUser->mId,
                'event_page_namespace is null',
                "event_page_title >= $time"
            )
        );
    }
}