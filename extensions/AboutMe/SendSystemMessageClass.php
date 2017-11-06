<?php
ini_set("max_execution_time", "50000");
@ignore_user_abort(true);
class SendSystemMessage{

    static $host = '';
    static $port = 80;
    static $errno = '';
    static $errstr = '';
    static $timeout = 5000;
    static $post_data = '';
    static $url;
    static $params = array( );

    static function int(){

        global $wgEnv,$wgWikiname;
        self::$host = 'wiki.joyme.'.$wgEnv;
        self::$url = '/'.$wgWikiname.'/index.php?action=ajax&rs=wfAboutMeSendSystemMessages';
    }

    static function SendTheRequest( $messages , $wiki_key = '' ){

        if( !$messages ){
            return false;
        }
        self::int();

        self::$params = array(
            'message' =>$messages,
            'wiki_key' =>$wiki_key,
            'sign'=>'!/^(-joyem-%-send-*message%$'
        );
        $data = http_build_query(self::$params);

        $fp = fsockopen(self::$host, self::$port, self::$errno, self::$errstr, self::$timeout);

        if(!$fp){
            return false;
        }
        $out = "POST ".self::$url." HTTP/1.1\r\n";
        $out .= "Host:".self::$host."\r\n";
        $out .= "Content-type:application/x-www-form-urlencoded\r\n";
        $out .= "Content-length:".strlen($data)."\r\n";
        $out .= "Connection:close\r\n\r\n";
        $out .= "${data}";
        fputs($fp, $out);
        fclose($fp);
    }


    static public function sendJoymeSystemMessage($wikikeys,$messages){

        global $wgUser;
        if($wikikeys){
            $sites = SystemMessageClass::getWikiSiteId($wikikeys);
            $users = SystemMessageClass::getSiteRelation($sites);
        }else{
            $users = SystemMessageClass::getUser();
        }
        foreach($users as $k=>$v){
            EchoSystemMessages::createNewType(
                array(
                    'be_user_id'=>$v,
                    'extra'=>array(
                        'user_id'=>$wgUser->mId,
                        'username'=>$wgUser->mName,
                        'content'=>$messages,
                        'type'=>true
                    )
                )
            );
        }
        return true;
    }

    static public function pullJoymeSystemMessage($messages){

        global $wgUser;
        if(!$wgUser->mId){
            return false;
        }
        EchoSystemMessages::createNewType(
            array(
                'be_user_id'=>$wgUser->mId,
                'extra'=>array(
                    'user_id'=>$messages->um_user_id,
                    'username'=>$messages->um_user_name,
                    'content'=>$messages->um_message,
                    'um_id'=>$messages->um_id,
                    'time'=>$messages->um_date,
                    'type'=>true
                )
            )
        );
        return true;
    }

    //一键系统消息
    static public function pullQuickMessage( $be_user_id,$um_user_id,$um_user_name,$um_message,$um_id){

        if($be_user_id && $um_user_id && $um_user_name && $um_message && $um_id){
            return EchoSystemMessages::createNewType(
                array(
                    'be_user_id'=>$be_user_id,
                    'extra'=>array(
                        'user_id'=>$um_user_id,
                        'username'=>$um_user_name,
                        'content'=>$um_message,
                        'event_page_namespace'=>$um_id,
                        'time'=>time(),
                        'type'=>true
                    )
                )
            );
        }
    }
}


class EchoSystemMessages{

    const messagesType = 'echo-system-message';

    static $extra = '';
    static $userid = '';
    static $message = '';
    static $um_id = '';
    static $time = '';
    static $event_namespace;

    static function int( $data ){

        if( !key_exists( 'be_user_id' ,$data )){
            return false;
        }
        self::$userid = $data['be_user_id'];

        self::$time = $data['extra']['time'];

        if( key_exists( 'extra' ,$data )){
            self::$extra = $data['extra'];
        }

        if( key_exists('event_page_namespace',$data['extra']) && !key_exists('um_id',$data['extra'])){
            self::$event_namespace = $data['extra']['event_page_namespace'];
        }else{
            self::$um_id = $data['extra']['um_id'];
        }
    }

    static function createNewType( $data = array() ){

        self::int( $data );
        $id = self::addEchoEvent();
        if(self::checkTheSettings( self::$userid )){
            if( $id && self::addEchoNotification( $id )){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    static function addEchoNotification( $id ){

        $dbw = wfGetDB( DB_MASTER );
        $row = array(
            'notification_event' =>$id,
            'notification_user' =>self::$userid,
            'notification_timestamp' =>wfTimestamp( TS_MW, strtotime(self::$time) )

        );
        return $dbw->insert( 'echo_notification', $row);
    }

    static function addEchoEvent(){

        $dbw = wfGetDB( DB_MASTER );
        $id = $dbw->nextSequenceValue( 'echo_event_id' );
        $row = self::toDbArray();
        $res = $dbw->insert( 'echo_event', $row, __METHOD__ );
        if ( $res ) {
            if ( !$id ) {
                $id = $dbw->insertId();
            }
            return $id;
        } else {
            return false;
        }
    }

    /**
     * Convert the object's database property to array
     * @return array
     */
    static function toDbArray() {

        if(self::$event_namespace){
            $data = array (
                'event_type' => self::messagesType,
                'event_page_namespace' => self::$event_namespace,
                'event_page_title'=>time(),
                'event_extra' => serialize(
                    self::$extra
                ),
                'event_agent_id' => self::$userid
            );
        }else{
            $data = array (
                'event_type' => self::messagesType,
                'event_variant' => self::$um_id,
                'event_page_title'=>time(),
                'event_extra' => serialize(
                    self::$extra
                ),
                'event_agent_id' => self::$userid
            );
        }
        return $data;
    }

    static function checkTheSettings( $be_user ){

        if( $be_user ){
            $dbw = wfGetDB( DB_MASTER );
            $res = $dbw->select(
                'user_properties',
                'up_property',
                array(
                    'up_user'=>$be_user
                )
            );
            $permissions = array();
            foreach($res as $k=>$v){
                $permissions[] = $v->up_property;
            }
            if(count($permissions)){
                if(in_array('echo-subscriptions-web-'.self::messagesType,$permissions)){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    }
}


class  JoymeReminderMessage{

    static $echoType;

    static $tuid;

    static $userid;

    static $username;

    static $article;

    static $desc;

    static $cid;

    static $wgWikiname;

    static $wgSiteGameTitle;

    static $type;

    static $pid;

    static function onAddMyNotification( $echoType,$tuid,$userid,$username,$article,$desc,$cid,$wgWikiname,$wgSiteGameTitle,$type,$pid=null){

        $array = array(
            'article-thumb-up',
            'article-comments',
            'article-cite-my'
        );

        if(in_array($echoType,$array)){

            if(!$type || !$tuid || !$article || !$desc  || !$wgWikiname || !$wgSiteGameTitle || !$userid ||!$username){
                wfDebugLog( __CLASS__, __FUNCTION__ . ": Parameter is null for $echoType" );
            }else{
                self::init( $echoType,$tuid,$userid,$username,$article,$desc,$cid,$wgWikiname,$wgSiteGameTitle,$type,$pid);
                self::createNewType();
            }
        }
        return true;
    }


    static function init( $echoType,$tuid,$userid,$username,$article,$desc,$cid,$wgWikiname,$wgSiteGameTitle,$type,$pid ){

        self::$echoType = $echoType;
        self::$tuid = $tuid;
        self::$article = $article;
        self::$desc = $desc;
        self::$cid = $cid;
        self::$wgWikiname = $wgWikiname;
        self::$wgSiteGameTitle = $wgSiteGameTitle;
        self::$type = $type;
        self::$userid = $userid;
        self::$username = $username;
        self::$pid = $pid;
    }


    static function createNewType( ){

        if( self::$userid==0 ){
            return false;
        }
        $id = self::addEchoEven();
        if(self::checkTheSettings( self::$tuid )){
            if( $id ){
                self::addEchoNotification( $id );
            }else{
                return false;
            }
        }
    }


    static function addEchoNotification( $id ){

        $dbw = wfGetDB( DB_MASTER );
        $row = array(
            'notification_event' =>$id,
            'notification_user' =>self::$tuid,
            'notification_timestamp' =>wfTimestamp( TS_MW, time() )
        );
        return $dbw->insert( 'echo_notification', $row);
    }


    static function addEchoEven(){

        $dbw = wfGetDB( DB_MASTER );
        $row = self::toEchoEventDbArray();
        $id = $dbw->nextSequenceValue( 'echo_event_id' );
        $res = $dbw->insert( 'echo_event', $row, __METHOD__ );
        if ( $res ) {
            if ( !$id ) {
                $id = $dbw->insertId();
            }
            return $id;
        } else {
            return false;
        }
    }

    static function toEchoEventDbArray() {

        return  array (
            'event_type' => self::$echoType,
            'event_extra' => serialize(
                array(
                    'user_id'=> intval(self::$userid),
                    'username' => self::$username,
                    'type' => self::$type,
                    'synopsis' => self::$desc,
                    'article' => self::$article,
                    'action_id' =>self::$cid,
                    'wikikey'=>self::$wgWikiname,
                    'from' => self::$wgSiteGameTitle,
                    "pid" =>self::$pid
                )
            ),
            'event_agent_id' => self::$tuid
        );
    }


    static function checkTheSettings( $be_user ){

        if( $be_user ){
            $dbw = wfGetDB( DB_MASTER );
            $res = $dbw->select(
                'user_properties',
                'up_property',
                array(
                    'up_user'=>$be_user
                )
            );
            $permissions = array();
            foreach($res as $k=>$v){
                $permissions[] = $v->up_property;
            }
            if(count($permissions)){
                if(in_array('echo-subscriptions-web-'.self::$echoType,$permissions)){
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }
    }
}