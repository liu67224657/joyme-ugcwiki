<?php
class QuickUserMessageItemClass{

    //一键系统消息详情表
    static public function add_User_Message_Item($um_id,$user_name,$user_id,$content,$send_time,$status){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->insert(
            'user_message_item',
            array(
                'um_id' => $um_id,
                'user_name' => $user_name,
                'user_id' => $user_id,
                'content' => $content,
                'send_time' => $send_time,
                'status' => $status,
            ),
            __METHOD__
        );
    }


    //查询总数
    static function select_Messages_Item_Count( $where ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            'user_message_item',
            '*',
            $where
        );
    }

    //列表数据查询
    static function select_Messages_List( $limit , $skip ,$where){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->select(
            'user_message_item',
            '*',
            $where,
            __METHOD__,
            array(
                'ORDER BY'=>'umi_id desc',
                'LIMIT'=>$limit,
                'OFFSET'=>$skip
            )
        );
    }

    //根据ID查询
    static function select_Row_By_Umi_Id( $uim_id ){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            'user_message_item',
            '*',
            array(
                'umi_id'=>$uim_id
            )
        );
    }

    //重发更新最后发送时间
    static function updateSendTime( $uim_id,$umi_status ){

        $dbw = wfGetDB( DB_MASTER );
        if($umi_status == 2){
            $data = array(
                'send_time'=>time(),
                'status'=>1
            );
        }else{
            $data = array(
                'send_time'=>time()
            );
        }
        $ret = $dbw->update(
            'user_message_item',
            $data,
            array(
                'umi_id'=>$uim_id
            )
        );
        $dbw->commit();
        return $ret;
    }


}