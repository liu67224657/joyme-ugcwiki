<?php
class SystemMessageClass{

    static $type = 1000;

    static public function add_System_Message($wikikeys,$message,$um_user_id,$um_user_name){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->insert(
            'user_system_messages',
            array(
                'um_user_id' => $um_user_id,
                'um_user_name' => $um_user_name,
                'um_message' => $message,
                'um_type' => self::$type,
                'wiki_keys' => $wikikeys,
                'um_date' => date( 'Y-m-d H:i:s' ),
            ),
            __METHOD__
        );
    }


    static public function getUser(){

        $dbw = wfGetDB( DB_MASTER );
        $users = array();
        $res = $dbw->select(
            'user',
            array('user_id')
        );
        if($res->numRows()){
            foreach($res as $v){
                $users[] = $v->user_id;
            }
        }
        return $users;
    }


    static public function getWikiSiteId($wikikes){

        $dbw = wfGetDB( DB_MASTER );
        $result = array();
        if(is_array($wikikes)){
            $keystr = implode('","',$wikikes);
            $where = array(
                'site_key in ("'.$keystr.'")'
            );
        }else{
            $where = array(
                'site_key'=>$wikikes
            );
        }
        $res = $dbw->select(
            'joyme_sites',
            array(
                'site_id',
            ),
            $where
        );
        foreach($res as $v){
            $result[] = $v->site_id;
        }
        return $result;
    }

    static function userIdExistInRelation( $user_id,$site_id,$time ){

        $dbw = wfGetDB( DB_MASTER );
        $site_ids = implode(',',$site_id);
        return $dbw->selectRowCount(
            'user_site_relation',
            array(
                '*',
            ),
            array(
                'user_id'=>$user_id,
                "site_id in ($site_ids)",
                "create_time <= $time"
            )
        );
    }

    static function getSiteRelation($sites){

        $dbw = wfGetDB( DB_MASTER );
        $result = array();
        if(is_array($sites)){
            $sitestr = implode('","',$sites);
            $where = array(
                'site_id in ("'.$sitestr.'")',
                'status'=>3
            );
        }else{
            $where = array(
                'site_id'=>$sites,
                'status'=>3
            );
        }
        $res = $dbw->select(
            'user_site_relation',
            array(
                'user_id',
            ),
            $where
        );
        foreach($res as $v){
            $result[] = $v->user_id;
        }
        return $result;
    }


    static function deleteByUserEventOffset( $user_id, $even_type ) {

        $dbw = wfGetDB( DB_MASTER );
        return self::deleteEvent(
            $dbw->select(
                'echo_event',
                'event_id',
                array(
                    'event_agent_id' => $user_id,
                    'event_type'=>$even_type
                )
            )
        );
    }

    static function deleteEvent( $res = array()){

        $dbw = wfGetDB( DB_MASTER );
        if($res){
            foreach($res as $k=>$v){
                $dbw->delete(
                    'echo_notification',
                    array(
                        'notification_event'=>$v->event_id
                    )
                );
                $dbw->commit();
            }
        }
        return true;
    }




    static public function select_Message_By_Id( $um_id ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            'user_system_messages',
            array(
                '*'
            ),
            array(
                'um_type'=>self::$type,
                'um_id'=>$um_id
            )
        );
    }

    static public function select_System_Message( $um_ids = array() ,$time){

        $dbw = wfGetDB( DB_MASTER );
        if(count($um_ids)>0){
            $um_ids = implode(',',$um_ids);
            $um_ids = rtrim($um_ids, ',');
            $where = array(
                'um_type'=>self::$type,
                "um_id not in ( $um_ids )",
                "UNIX_TIMESTAMP(um_date) >= $time"
            );
        }else{
            $where = array(
                'um_type'=>self::$type,
                "UNIX_TIMESTAMP(um_date) >= $time"
            );
        }
        return $dbw->select(
            'user_system_messages',
            array(
                '*',
            ),
            $where
        );
    }

    static public function select_System_Message_Count( $time ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            'user_system_messages',
            array(
                'um_id'
            ),
            array(
                'um_type'=>self::$type,
                "UNIX_TIMESTAMP(um_date) >= $time"
            )
        );
    }
}