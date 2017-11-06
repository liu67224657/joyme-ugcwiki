<?php
ini_set("max_execution_time", "3000");
@ignore_user_abort(true);
@set_time_limit(0);
use Joyme\core\Log;
class SynchronousData{

    function index( $wiki = '',$time = null ){

        if(empty($wiki)){
            echo 'The wiki is empty!';
            exit();
        }
        $user_registration = date('Ymd000000',time());
        if(!empty($time)){
            $user_registration = $time.'000000';
        }
        $wikiname = $wiki.'wiki';
        $data = self::getUserInfo( $wikiname );
        $res = array();
        if($data){
            $users = array();
            foreach($data as $k=>$v){
                $users[] = $v->user_id;
            }
            if($users){
                $urlarray = array_chunk($users,100);
                if($urlarray){
                    $getJavaUser = new getJavaUser();
                    foreach($urlarray as $uk=>$uv){
                        $res[] = $getJavaUser->getJavaUser( $uv );
                    }
                }
            }
        }
        if($res){
            $num = 0;
            $num1 = 0;
            $num2 = 0;
            $count = 0;
            $user_ids = '';
            $user_addition = ':';
            foreach($res as $sk=>$sv){
                if(is_array($sv)){
                    foreach($sv as $usk=>$usv){
                        if(preg_match('/^[0-9a-zA-Z\x{4e00}-\x{9fa5}]+$/u',$usv[2])){
                            $udata = array(
                                'user_id' => $usv[1],
                                'user_name' => ucfirst($usv[2]),
                                'user_registration'=>$user_registration    //默认注册时间---获取系统消息会用到
                            );
                            $pdata = array(
                                'user_id' => $usv[1],
                                'profileid' => $usv[0]
                            );
                            if(!self::checkUserExist( $usv[1] )){
                                if(self::insertUser( $udata )){
                                    //入表user,处理成功，log记录uid
                                    $user_ids.=$usv[1].',';
                                    $num1++;
                                }
                            }
                            if(!self::checkUserAdditionExist( $usv[1] )){
                                if(self::insertUserAddition( $pdata )){
                                    //入表user_addition成功，log记录uid
                                    $user_addition.=$usv[1].',';
                                    $num2++;
                                }
                            }
                            usleep(10000);
                            $num++;
                        }else{
                            $count++;
                        }
                        continue;
                    }
                }
            }
            //log start
            $log = $wikiname.':user_ids: num-'.$num1.' list-'.$user_ids.' user_additions: num-'.$num2.' list-'.$user_addition;
            Log::error(__FUNCTION__,"ugcwiki user data import",$log);
            echo 'pass user '.$num.' inser user '.$num1.' ! miss num '.$count.' !';
        }else{
            echo 'no data!';
        }
    }

    static function checkUserExist( $userid ){

        if( !$userid ){
            return false;
        }
        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            'user',
            array(
                'user_id'
            ),
            array(
                'user_id'=>$userid
            )
        );
    }


    static function checkUserAdditionExist( $userid ){

        if( !$userid ){
            return false;
        }
        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            'user_addition',
            array(
                'user_id'
            ),
            array(
                'user_id'=>$userid
            )
        );
    }


    //insert user_addition
    static function  insertUserAddition( $data ){

        if($data){
            $dbw = wfGetDB( DB_MASTER );
            $ret = $dbw->insert(
                'user_addition',
                $data
            );
            $dbw->commit();
            return $ret;
        }
        return false;
    }

    //insert home user
    static function insertUser( $data ){

        if($data){
            $dbw = wfGetDB( DB_MASTER );
            $dbw->selectDB( 'homewiki' );
            $res = $dbw->insert(
                'user',
                $data
            );
            $dbw->commit();
            return $res;
        }
        return false;
    }

    //获取用户信息
    static function getUserInfo( $wikiname ){

        $dbw = wfGetDB( DB_SLAVE );
        return $dbw->select(
            "$wikiname.user",
            array(
                'user_id'
            )
        );
    }
}

class getJavaUser{

    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_charset = 'utf8';
    protected $db_link = null;
    protected $db_timeout = 3000;
    public $count =0;

    function __construct() {
        global $wgDBservers;
        if( $wgDBservers[0] ){
////            $this->db_host = '172.16.75.75';
////            $this->db_user = 'root';
////            $this->db_password = '654321';
            $this->db_host = '10.251.0.252';
            $this->db_user = 'shixin';
            $this->db_password = 'qxB53454';
            $this->connect();
        }
    }

    public function connect(){

        $this->db_link = mysql_connect($this->db_host,$this->db_user,$this->db_password) or die("Can't connect :" . mysql_error());;
        mysql_query('SET NAMES '.$this->db_charset);
        set_time_limit($this->db_timeout) or die('timeout set failed');
        return $this->db_link;
    }

    public function getJavaUser( $users ){

        mysql_select_db("usercenter");

        $users = implode(',',$users);
        $sql = "SELECT profile_id,uid,nick from profile WHERE uid IN ( $users )";
        $result = mysql_query($sql,$this->db_link);

        $arr = array();
        while($rs = mysql_fetch_row($result)){
            $arr[] = $rs;
        }
        return $arr;
    }

    public function __destruct(){

        mysql_close($this->db_link);
    }
}
