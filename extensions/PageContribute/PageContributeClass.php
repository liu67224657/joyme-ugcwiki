<?php
/**
 * Created by zend studio.
 * User: tianming
 * Date: 2016/11/24
 * Time: 19:02
 */
class JoymePageContribute{
	
	public static function updateContributeUser($pageid,$bytes = 0){
		global $wgUser;
		if ( !$wgUser->isLoggedIn() ) {
			return false;
		}
		
    	$uid = $wgUser->getId();
    	
    	$dbr = wfGetDB( DB_SLAVE );
    	$dbw = wfGetDB( DB_MASTER );
    	
    	$pageContributeId = 0;
    	
    	//查询当前用户当前page的记录
    	$res = $dbr->selectRow( 
    			'page_contribute',
    			 '*', 
    			  array(
    				'uid'=>$uid,
    			 	'page_id'=>$pageid
    			 ),
    			 __METHOD__ 
    	);
    	if($res){
    		$contributes = JoymePageContribute::getContributeNum($bytes,$res->edit_count+1);
    		$rs = $dbw->update(
    				'page_contribute', 
    				array('edit_bytes=edit_bytes+'.$bytes,'edit_count=edit_count+1','contributes=contributes+'.$contributes), 
    				array('id'=>$res->id)
    		);
    		$pageContributeId = $res->id;
    		$contributes = $contributes+$res->contributes;
    	}else{
    		$contributes = JoymePageContribute::getContributeNum($bytes,1);
    		$rs = $dbw->insert(
    				'page_contribute',
    				 array(
    				 	'uid'=>$uid,
    				 	'page_id'=>$pageid,
    				 	'edit_bytes'=>$bytes,
    				 	'edit_count'=>1,
    				 	'contributes'=>$contributes
    				 ),
    				__METHOD__
    		);
    		$pageContributeId = $dbw->insertId();
    		//$dbw->commit(__METHOD__);
    	}
    	
    	//查询pageaddons贡献者
    	$jpa = new JoymePageAddons();
    	$pageaddons = $jpa->getPageAddons($pageid);
    	
    	if(empty($pageaddons->contribute_uid)){
    		return JoymePageAddons::updateContributeUid($uid, $pageid,$pageContributeId);
    	}else if($pageaddons->contribute_uid !== $uid){
    		
    		$res2 = $dbr->selectRow( 
    			'page_contribute',
    			 '*', 
    			  array(
    				'uid'=>$pageaddons->contribute_uid,
    			 	'page_id'=>$pageid
    			 ),
    			 __METHOD__ 
    		);
    		if($res2){
    			//比较两人贡献
    			if($contributes >= $res2->contributes){
    				//更新当前用户为最新编辑
    				return JoymePageAddons::updateContributeUid($uid, $pageid,$pageContributeId);
    			}
    		}else{
    			return false;
    		}
    		
    	}
    	
    	return true;
    }
    //贡献算法 bytes为编辑总长度 count为编辑次数
    public static function getContributeNum($bytes,$count){
    	$ratio = 0.25;
    	return $bytes*(1+($count-1)*$ratio);
    }
    
    //感谢
    //num感谢次数
    public static function thank($pageid,$uid,$type=1,$num=1,$current_uid){
		global $wgJoymeUserInfo,$wgSiteGameTitle,$wgServer,$wgWikiname;

    	$dbr = wfGetDB( DB_SLAVE );
    	$dbw = wfGetDB( DB_MASTER );
    	 
    	$type = $type==1?1:2;
    	
    	//膜拜  先上报  确认积分是否足够
    	if($type == 2){
    		$rs = JoymeWikiUser::pointsreport(19,$uid,$wgJoymeUserInfo['uid'],$num);
    		if($rs !== true){
    			return false;
    		}
    	}else{
    		$num = 1;
    	}
    	
    	

		//查询当前用户当前page的记录
    	$pageContributeResult = $dbr->selectRow(
    			'page_contribute',
    			'id',
    			array(
    					'uid'=>$uid,
    					'page_id'=>$pageid
    			),
    			__METHOD__
    	);
    	if($pageContributeResult){
    		$pageContributeThankResult = $dbr->selectRow(
    				'page_contribute_thanks',
    				'id',
    				array(
    						'contribute_id'=>$pageContributeResult->id,
    						'uid'=>$current_uid,
    						't_type'=>$type
    				),
    				__METHOD__
    		);
	    	if($pageContributeThankResult){
	    		//只能感谢一次
	    		if($type == 1){
	    			return false;
	    		}
	    		$rs = $dbw->update(
	    				'page_contribute_thanks', 
	    				array('t_count=t_count+'.$num), 
	    				array('id'=>$pageContributeThankResult->id)
	    		);
	    		
	    	}else{
	    		
	    		$rs = $dbw->insert(
	    				'page_contribute_thanks',
	    				 array(
	    				 	'uid'=>$current_uid,
	    				 	'contribute_id'=>$pageContributeResult->id,
	    				 	't_type'=>$type,
	    				 	't_count'=>$num
	    				 ),
	    				__METHOD__
	    		);
	    	}
	    	//更新page_contribute表膜拜次数
	    	if($type == 2){
		    	$dbw->update(
		    			'page_contribute',
		    			array('thanks_count=thanks_count+'.$num),
		    			array('id'=>$pageContributeResult->id)
		    	);
	    	}

	    	if($rs){
	    		//消息通知
				$quality = JoymePageAddons::getQuality($type,$num);
				$pageResult = $dbr->selectRow(
					'page',
					'page_title',
					array(
						'page_id'=>$pageid
					),
					__METHOD__
				);
				if($pageResult){
					if($type == 1){
						//感谢
						JoymeWikiUser::pointsreport(20,$uid,$wgJoymeUserInfo['uid'],$num);
						JoymeWikiUser::noticereport(array(
							'tuid' => $uid,
							'uid' => $wgJoymeUserInfo['uid'],
							'ntype' => 'sys',
							'curl' => '<a href="'.$wgServer.'/'.$wgWikiname.'/'.$pageResult->page_title.'" target="_blank">'.$wgSiteGameTitle.'&nbsp;'.$pageResult->page_title.'</a>',
							'desc' => $quality,
							'desttype' => 6
						));
					}
					elseif($type == 2){
						//膜拜
						JoymeWikiUser::noticereport(array(
							'tuid' => $uid,
							'uid' => $wgJoymeUserInfo['uid'],
							'ntype' => 'sys',
							'curl' => '<a href="'.$wgServer.'/'.$wgWikiname.'/'.$pageResult->page_title.'" target="_blank">'.$wgSiteGameTitle.'&nbsp;'.$pageResult->page_title.'</a>',
							'desc' => $quality,
							'desttype' => 7
						));
					}
				}
	    		
	    		//增加文章质量
	    		JoymePageAddons::addQuality($pageid,$type,$num);
	    		
	    		return true;
	    	}else{
	    		return false;
	    	}
    	
    	}else{
    		return false;
    	}
    }
    
    //获取感谢列表
    public static function getList($cid){
    	$dbr = wfGetDB( DB_SLAVE );
    	$pageContributeResult = $dbr->selectRow(
    			'page_contribute',
    			'*',
    			array(
    					'id'=>$cid
    			),
    			__METHOD__
    	);
    	if($pageContributeResult){
    		$list = array();
    		if($pageContributeResult->thanks_count>0){
	    		$list = $dbr->select(
	    				'page_contribute_thanks',
	    				'*',
	    				array(
	    						'contribute_id'=>$pageContributeResult->id,
	    						't_type'=>2
	    				),
	    				__METHOD__,
	    				array('limit'=>100,'ORDER BY' =>'t_count DESC')
	    		);
    		}
    		return array('count'=>$pageContributeResult->thanks_count,'list'=>$list);
    	}else{
    		return false;
    	}
    }
    
    
}

