<?php
/**
 * AJAX functions used by UserBoard.
 */
$wgAjaxExportList[] = 'wfPageContribute';
$wgAjaxExportList[] = 'wfPageContributeList';


function wfPageContribute( $pageid, $uid,$type=1,$num=1 ) {
	global $wgUser;
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('您尚未登录哦','0');
		return $out;
	}
	$current_uid = $wgUser->getId();
	$rs = JoymePageContribute::thank($pageid, $uid,$type,$num,$current_uid);
	$data = '';
	if ( $rs ) {
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$out = JoymeWikiUser::getJson('请勿重复感谢','0');
	}
	return $out;
}
function wfPageContributeList( $cid ) {
	global $wgUser;
	
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	$rs = JoymePageContribute::getList($cid);
	if ( $rs ) {
		$data = array('count'=>$rs['count'],'list'=>array(),'point'=>0);
		
		$contribute_uids = array(0);
		foreach ($rs['list'] as $v){
			$contribute_uids[] = $v->uid;
		}
			
		$joymewikiuser = new JoymeWikiUser();
		$user_profiles = $joymewikiuser->getProfile($contribute_uids);
		
		foreach ($rs['list'] as $v){
			foreach ($user_profiles as $pro){
				if($v->uid == $pro['uid']){
					$v->icon = $pro['icon'];
					$v->username = $pro['nick'];
					$v->uno = $pro['profileid'];
					break;
				}
			}
			$data['list'][] = $v;
		}
		//查询当前用户积分
		if ( !$wgUser->isLoggedIn() ) {
			$point = 0;
		}else{
			$profile = $joymewikiuser->getProfile($wgUser->getId());
			$point = intval($profile[0]['point']);
		}
		$data['point'] = $point;
		
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$out = JoymeWikiUser::getJson('数据异常','0');
	}
	return $out;
}