<?php
/**
 * Created by Zend Studio.
 * User: TianMing
 * Date: 2016/12/12
 * Time: 15:30
 */
use Joyme\net\Curl;

class JoymeRank{
	
	//获取java三个排行榜数据 
	//type :  point=积分排行榜   prestige=声望排行榜  consumption=消费排行榜
	public static function getRankData($type,$limit=10){
	
		global $wgEnv;
	
		$rankapiurl = 'http://api.joyme.'.$wgEnv.'/joyme/api/point/rank/list';
	
		$curl = new Curl();
	
		$data = array(
				'ranktype'=>$type,
				'count'=>$limit
		);
	
		$res = $curl->Post($rankapiurl, $data);
		$res = json_decode($res, true);
	
		return $res;
	}
	
	//记录排行 使用redis有序集合
	public static function addContentRank($key,$pageid,$val=1){
		global $wgMemc;
		if(!in_array($key,array('hot','good'))){
			return false;
		}
		
		if($pageid > 0){
		
			$keyw = wfMemcKey( 'joymerank',$key,date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600)).'w' );
			$wgMemc->zIncrBy($keyw, $val, $pageid);
				
			$keym = wfMemcKey( 'joymerank',$key,date('Y-m-01',time()).'m' );
			$wgMemc->zIncrBy($keym, $val, $pageid);
			
			return true;
		}else{
			return false;
		}
	}
	//获取内容排行榜
	public static function getContentRankData($key,$limit=10){
		global $wgMemc;
		
		$limit = intval($limit);
		
		if(!in_array($key,array('hot','new','good'))){
			return false;
		}
		
		$dbr = wfGetDB( DB_MASTER );
		
		if($key == 'new'){
			
			$where = array('rc_namespace'=>0,'rc_type'=>array(0,1));
			$options = array('ORDER BY'=>'rc_id desc','LIMIT'=>$limit);
			$res = $dbr->select(
					'recentchanges',
					array(
							'rc_timestamp', 'rc_title', 'rc_new', 'rc_user', 'rc_user_text', 'rc_old_len','rc_new_len'
					),
					$where,
					__METHOD__,
					$options
			);
			$res_w = array();
			
			while ( $row = $res->fetchRow() ) {
				$res_w[] = $row;
			}

			$dbr->commit( __METHOD__ );
			$res_m = array();
		}else{
			$keyw = wfMemcKey( 'joymerank',$key,date('Y-m-d',(time()-(date('N')+6)*24*3600)).'w' );
			$res_w = $wgMemc->zRange($keyw,$limit);
			
			$keym = wfMemcKey( 'joymerank',$key,date('Y-m-01',strtotime('-1 month')).'m' );
			$res_m = $wgMemc->zRange($keym,$limit);

			$pageids = '0';

			foreach($res_w as $k=>$v){
				if(!empty($k)){
					$pageids.=','.$k;
				}else{
					unset($res_w[$k]);
				}
			}
			foreach($res_m as $k=>$v){
				if(!empty($k)){
					$pageids.=','.$k;
				}else{
					unset($res_w[$k]);
				}
			}
			$res = $dbr->query('SELECT page.page_id,page_title,last_edit_user FROM page,page_addons WHERE page.page_id=page_addons.page_id AND page.page_id IN('.$pageids.')');

			while ( $row = $res->fetchRow() ) {
				foreach($res_w as $k=>$v){
					if($k == $row['page_id']){
						$row['value'] = $v;
						$res_w[$k] = $row;
						break;
					}
				}
				foreach($res_m as $k=>$v){
					if($k == $row['page_id']){
						$row['value'] = $v;
						$res_m[$k] = $row;
						break;
					}
				}
			}
			
			
		}
		
		$result = array('rs'=>1,'result'=>array('week'=>$res_w,'month'=>$res_m,'all'=>array()));
		return $result;
	}
}


