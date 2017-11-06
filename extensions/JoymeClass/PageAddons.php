<?php
/**
 * Created by PhpStorm.
 * User: xinshi
 * Date: 2016/7/27
 * Time: 19:02
 */
class JoymePageAddons{

    static function getPageLastEditUser( $articleid ){

        if( empty($articleid) ){
            return false;
        }
        $array = array();
        $dbr = wfGetDB( DB_MASTER );
        /*
        $res = $dbr->selectRow(
            'page_addons',
            "last_edit_user,DATE_FORMAT(pa_timestamp,'%Y-%m-%d') as time",
            array(
                'page_id'=>$articleid
            )
        );*/
        
        $user = $dbr->selectRow(
            'revision',
            'rev_user,rev_user_text,rev_timestamp',
            array(
                'rev_page'=>$articleid
            ),
            __METHOD__,
            array(
                'ORDER BY'=>'rev_id DESC'
            )
        );
        $array['last_edit_user'] = $user->rev_user_text;
		$array['time'] = MWTimestamp::getLocalInstance( $user->rev_timestamp )->format( 'Y-m-d H:i:s' );
        $array['user_id'] = $user->rev_user;
        
        return $array;
    }
    
    public function getPageAddons( $aid ){
    	$dbr = wfGetDB( DB_SLAVE );
    	$res = $dbr->selectRow( 'page_addons', '*', array('page_id'=>$aid) , __METHOD__ ,array('LIMIT'  =>1));
    	return $res;
    }
	
	public function updateFieldAddOne( $data ){
		// $data[0] 字段名  $data[1] 文章id
    	$dbw = wfGetDB( DB_MASTER );
		$sql = "INSERT page_addons (page_id, {$data[0]}) VALUES ({$data[1]}, 1) ON DUPLICATE KEY UPDATE {$data[0]}={$data[0]} + 1";
    	$res = $dbw->query( $sql );
    	return $res;
    }
    
    //修改贡献者uid
    public static function updateContributeUid($uid,$pageid,$pageContributeId){
    	$dbw = wfGetDB(DB_MASTER);
    	$ret = $dbw->update(
    			'page_addons',
    			array("contribute_uid=".$uid,"contribute_id=".$pageContributeId ),
    			array( 'page_id' => $pageid  ),
    			__METHOD__
    	);
    	$dbw->commit(__METHOD__);
    	return $ret;
    }
    //增加文章质量
    //pageid 文章id  type：1感谢 2 膜拜
    public static function addQuality($pageid,$type,$num){
    	$dbw = wfGetDB(DB_MASTER);
		
		$quality = self::getQuality($type,$num);

    	$ret = $dbw->update(
    			'page_addons',
    			array("quality=quality+".$quality ),
    			array( 'page_id' => $pageid  ),
    			__METHOD__
    	);
    	$dbw->commit(__METHOD__);
    	if($ret){
    		//加入排行榜
    		JoymeRank::addContentRank('good', $pageid,$quality);
    	}
    	return $ret;
    }

	public static function getQuality($type,$num){
		if($type == 1){
			$quality = 10;
		}else{
			$quality = 20*$num;
		}
		return $quality;
	}
}