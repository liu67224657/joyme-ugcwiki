<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserViewFollows11';
$wgAjaxExportList[] = 'wfGetContributionList';

function wfGetContributionList($pageno, $uid, $year, $month, $wikikey, $actype){
	$ContribList = new ContribListAPI();
	// var_dump($ContribList);exit;
	$out = JoymeWikiUser::getJson($uid,'1');
	return $out;
}



function wfUserViewFollows1( $page, $uid, $type ) {
	global $wgUser;
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('您尚未登录哦','0');
		return $out;
	}

	$uuf = new UserUserFollow();
	$follows = $uuf->getFollowList( $wgUser->getId(), $type, 10, $page);
	if ( $follows ) {
		$rs = $uuf->displayFollowList($uid, $type, $follows);
		$out = JoymeWikiUser::getJson($rs,'1');
	}else{
		$out = JoymeWikiUser::getJson('没有更多了','0');
	}
	//TODO: use wfMessage instead of hard code
	return $out;
}

