<?php
class QuickUserMessageClass{

    //一键系统消息主表
    static public function add_User_Message($theme,$content_format,$user_count,$success_num,$um_user_name){

        $dbw = wfGetDB( DB_MASTER );
        $dbw->insert(
            'user_message',
            array(
                'theme' => $theme,
                'content_format' => $content_format,
                'user_count' => $user_count,
                'success_num' => $success_num,
                'um_user_name' => $um_user_name,
                'create_time' => time()
            ),
            __METHOD__
        );
        $id = $dbw->insertId();
        $dbw->commit();
        return $id;
    }

    //更改一键系统消息主表
    static public function Update_User_Message_Success_num( $um_id,$success_num){

        $dbw = wfGetDB( DB_MASTER );
        $ret = $dbw->update(
            'user_message',
            array(
                'success_num' => $success_num
            ),
            array(
                'um_id'=>$um_id
            ),
            __METHOD__
        );
        $dbw->commit();
        return $ret;
    }

    //查询总数
    static function select_Messages_Count( $where ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            'user_message',
            '*',
            $where
        );
    }

    //列表数据查询
    static function select_Messages_List( $limit , $skip ,$where){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->select(
            'user_message',
            '*',
            $where,
            __METHOD__,
            array(
                'ORDER BY'=>'um_id desc',
                'LIMIT'=>$limit,
                'OFFSET'=>$skip
            )
        );
    }

    //重发总数加1
    static function update_Success_Num( $send_um_id ){

        $dbw = wfGetDB( DB_MASTER );
        $ret = $dbw->update(
            'user_message',
            array(
                'success_num = success_num+1'
            ),
            array(
                'um_id'=>$send_um_id
            ),
            __METHOD__
        );
        $dbw->commit();
        return $ret;
    }
}