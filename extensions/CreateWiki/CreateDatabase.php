<?php
class CreateDatabase{

    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_charset = 'utf8';
    protected $db_link = null;
    protected $db_timeout = 30;
    public $count =0;

    function __construct() {
        global $wgDBservers;
        if( $wgDBservers[0] ){
            $this->db_host = $wgDBservers[0]['host'];
            $this->db_user = $wgDBservers[0]['user'];
            $this->db_password = $wgDBservers[0]['password'];
            $this->connect();
        }
    }

    public function connect(){

        $this->db_link = mysqli_connect($this->db_host,$this->db_user,$this->db_password) or die("Can't connect :" . mysqli_error($this->db_link));;
        mysqli_query($this->db_link,'SET NAMES '.$this->db_charset);
        set_time_limit($this->db_timeout) or die('timeout set failed');
        return $this->db_link;
    }

    public function createDataBase($dbName){

        if($this->db_link){
            $sql = 'CREATE DATABASE '.$dbName.' default charset utf8 COLLATE utf8_general_ci';
            return mysqli_query($this->db_link,$sql);
        }
        return false;
    }

    public function createTable($sqls,$wikikey){

        if($this->db_link){
            if(mysqli_select_db($this->db_link,$wikikey)){
                foreach($sqls as $k=>$v){
                    mysqli_query($this->db_link,$v);
                }
                return true;
            }else{
                $this->count++;
                if($this->count<=5){
                    sleep(5);
                    $this->createTable($sqls,$wikikey);
                }else{
                    echo 'The database was not found!';
                    exit();
                }
            }
        }else{
            echo 'Database is not connected!';
            exit();
        }
    }

    function insertSeoTable($wiki_type,$site_name,$wiki_title,$wiki_keywords,$wiki_description,$user_editstatus,$is_mobile,$skin_type){

        $sql = "insert into site_info(sid,site_name,site_title,site_seokeywords,site_seodescription,wiki_type,useredit_status,mindex_status,skin_style)VALUES (1,'$site_name','$wiki_title','$wiki_keywords','$wiki_description','$wiki_type','$user_editstatus','$is_mobile','$skin_type')";
        return mysqli_query($this->db_link,$sql);
    }

    public function getTableNumByDbName($dbNema){

        $sql = "SELECT count(table_name) FROM information_schema.tables WHERE table_schema = '$dbNema'";

        $result = mysqli_query($this->db_link,$sql);
        /* numeric array */
        $row = $result->fetch_array(MYSQLI_NUM);
        return @$row[0];
    }

    public function insertUserGroups(){

        global $joyme_u_adminid;
        foreach($joyme_u_adminid as $uk=>$uv){
            $sql = " insert into user_groups(ug_user,ug_group) VALUES ($uv,'bureaucrat');";
            mysqli_query($this->db_link,$sql);
        }
        return true;
    }


    //更新版本表时间为具体操作时间
    //解决BUG  17804
    public function updateRevision(){

        $sql = "update revision set rev_timestamp = '".date('YmdHis',time())."'";
        return mysqli_query($this->db_link,$sql);
    }


    public function __destruct(){

        mysqli_close($this->db_link);
    }
}