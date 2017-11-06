<?php
/**
 * RecommendUsers class
 */
class RecommendUsers{

    public $table_name = 'recommend_users';
    /**
     * Adds a new recommend users to the database.
     *
     * @param $rec_user_id Mixed: recommend users id
     * @param $bel_user_id Mixed: belongs to the user
     * @param $category Integer: see the $categories class member variable
     * @return Integer: the inserted ID primary key number
     */
    public function add_Recommend_Users($rec_user_id,$bel_user_id){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->insert(
            $this->table_name,
            array(
                'rec_user_id' => $rec_user_id,
                'bel_user_id' => $bel_user_id,
                'user_createdate' => date( 'Y-m-d H:i:s' ),
            ),
            __METHOD__
        );
    }

    /**
     * Delete recommend users by id to the database.
     *
     * @param $rec_user_id Mixed: recommend users id
     * @param $bel_user_id Mixed: belongs to the user
     * @param $category Integer: see the $categories class member variable
     * @return Integer: boole
     */
     public function delete_Recommend_Users($rec_user_id){

         $dbw = wfGetDB( DB_MASTER );
         $where = array(
            'rec_user_id'=>$rec_user_id
         );
         $dbw->delete($this->table_name,$where);
         $dbw->commit();
     }

    /**
     * find user name
     * @param $string Mixed: A comma to separate the user ID
     * @return Integer: array
     */
    public function find_User_Name($string){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->select(
            'user',
            array( 'user_id,user_name' ),
            array( "user_id in ($string)")
        );
    }

    /**
     * find user name or phone number exists
     * @param $rec_user_id Mixed: recommend users id
     * @param $bel_user_id Mixed: belongs to the user
     * @param $category Integer: see the $categories class member variable
     * @return Integer: array
     */
    public function find_User_Id($user_name){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            'user',
            array( 'user_id' ),
            array( 'user_name'=>$user_name)
        );
    }

    /**
     * Query user all recommend users
     * @param $user_id Mixed:  users id
     * @return Integer: array
     */
    public function select_All_User(){

        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            $this->table_name,
            array('rec_user_id')
        );
        return $res;
    }

    /**
     * Query user id
     * @param $user_id Mixed:  users id
     * @return Integer: boole
     */
    public function select_User_Id($user_id){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            $this->table_name,
            array('rec_user_id'),
            array( 'rec_user_id'=>$user_id)
        );
    }

    /**
     * The user center recommend users data
     * @return Integer: array
     */
    public function getUserInfo( $userid ){

        $user = array();
        $rand_num = 20;
        $dbr = wfGetDB( DB_SLAVE );
        $res = $dbr->select(
            'recommend_users',
            array('rec_user_id'),
            '',
            __METHOD__,
            array( 'LIMIT' => 50, 'OFFSET' => 0 )
        );
        $numRows = $res->numRows();
        $result = array();
        if($numRows>0){
            $users = array();
            foreach($res as $k=>$v){
            	if($v->rec_user_id == $userid){
            		continue;
            	}else{
                	$users[] = $v->rec_user_id;
            	}
            }
            if($users){
                $rand_num = (count($users)>$rand_num)?$rand_num:count($users);
                $rand_user = array_rand($users,$rand_num);
                if(count($rand_user)>1){
                    foreach($rand_user as $k=>$v){
                        $user[] = $users[$v];
                    }
                }else{
                    $user[] = $users[0];
                }
                $model = new UserUserFollow();
                $res = $model->getUserUserIsFollow( $userid, $user , 2);
                foreach($res as $k=>&$v){
                    if($v==0){
                        $v = array(
                            'is_follow'=>$v
                        );
                    }else{
                        $v = array(
                            'is_follow'=>10
                        );
                    }
                }
                if( $user ){
                    $joymewikiuser = new JoymeWikiUser();
                    $manitos = $joymewikiuser->getProfile($user);
                    if ($manitos) {
                        $result = $manitos;
                    }
                }
                $usekeys = array_column($result,null,'uid');
                $result = array_merge_recursive($this->toString($usekeys),$this->toString($res));
                if($result){
                    foreach($result as $k=>$v){
                        if($v['is_follow']==10){
                            unset($result[$k]);
                            continue;
                        }
                        $ust = new UserStats($v['uid'],$v['nick']);
                        $info = $ust->getUserStats();
                        $result[$k]['brief'] = $info['brief'];
                    }
                }
                return $this->shuffle_assoc($result);
            }
        }
        return $result;
    }

    function shuffle_assoc($list) {
        if (!is_array($list)) return $list;

        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key)
            $random[$key] = $this->shuffle_assoc($list[$key]);
        return $random;
    }


    public function toString($array = array()){

        if(empty($array)){
            return false;
        }
        $arr = array();
        foreach($array as $k=>$v){
            $arr[serialize($k)] = $v;
        }
        return $arr;
    }

    public static function returnJson( $data , $callback = '' ){

        if( $data ){
            $result = array('data'=>$data,'rs'=>1);
        }else{
            $result = array('data'=>'','rs'=>0);
        }
        if (strlen($callback) > 1) {
            return $callback . "([" . json_encode($result) . "])";
        } else {
            return json_encode($result);
        }
    }
}