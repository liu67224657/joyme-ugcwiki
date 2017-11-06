<?php
/**
 * RecommendUsers class
 */
class RecommendWiki{

    public $table_name = 'recommend_wiki';

    /**
     * Adds a new recommend wiki to the database.
     * @param $wiki_key Mixed: wiki_key
     * @param $icon Mixed: icon images path
     * @param $bel_user_id Mixed: add people
     * @return Integer:
     */
    public function add_Recommend_Wiki($wiki_key,$icon,$bel_user_id){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->insert(
            $this->table_name,
            array(
                'wiki_key' => $wiki_key,
                'icon' => $icon,
                'bel_user_id'=>$bel_user_id,
                'wiki_createdate' => date( 'Y-m-d H:i:s' ),
            ),
            __METHOD__
        );
    }

    /**
     * delete a recommend wiki to the database.
     * @param $wiki_key Mixed: wiki_key
     * @param $bel_user_id Mixed: add people
     * @return Integer:
     */
    public function delete_Recommend_Wiki($delete_id,$bel_user_id){

        $dbw = wfGetDB( DB_MASTER );
        $where = array(
            'rec_id' => $delete_id,
            'bel_user_id'=>$bel_user_id
        );
        $dbw->delete($this->table_name,$where);
        $dbw->commit();
    }

    /**
     * Query wiki row
     * @param $user_id Mixed:  users id
     * @param $wiki_key Mixed: wiki_key
     * @return Integer: boole
     */
    public function select_Wikikey_Exists($wiki_key,$user_id){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRow(
            $this->table_name,
            array('rec_user_id'),
            array(
                'wiki_key'=>$wiki_key,
                'bel_user_id'=>$user_id
            )
        );
    }

    /**
     * Query user all recommend wiki
     * @param $user_id Mixed:  users id
     * @return Integer: array
     */
    public function select_All_User($user_id){

        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            $this->table_name,
            array('wiki_key','icon','rec_id'),
            array( 'bel_user_id'=>$user_id)
        );
        return $res;
    }


    public function select_All_wiki(){

        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            $this->table_name,
            array('wiki_key','icon','rec_id'),
            '',
            __METHOD__,
            array(
                'ORDER BY'=>'rec_id desc'
            )

        );
        return $res;
    }

    /**
     * The user center recommend wiki data
     * @return Integer: array
     */
    static function getWikiInfo(){

        $result = array();
        $dbw = wfGetDB( DB_MASTER );
        $res = $dbw->select(
            'recommend_wiki',
            array('wiki_key','icon'),
            '',
            __METHOD__,
            array( 'ORDER BY'=>'rec_id desc','LIMIT' => 16, 'OFFSET' => 0 )
        );
        if($res->numRows()){
            foreach($res as $k=>$v){
                $data = array();
                $data['site_key'] = $v->wiki_key;
                $data['icon'] = $v->icon;
                $result[] = $data;
            }
        }
        return $result;
    }

    static function getWikiCount(){

        $dbw = wfGetDB( DB_MASTER );
        return $dbw->selectRowCount(
            'recommend_wiki',
            array('wiki_key')
        );
    }
}