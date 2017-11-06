<?php
/**
 * AJAX functions used by UserBoard.
 */

$wgAjaxExportList[] = 'wfGetPageAddons';
$wgAjaxExportList[] = 'wfClickLike';
$wgAjaxExportList[] = 'wfShortComments';
$wgAjaxExportList[] = 'wfAddShortComment';
$wgAjaxExportList[] = 'wfShortCommentClickLike';

function wfGetPageAddons( $pageid ) {

	$out = JoymeWikiUser::getJson('发生未知错误','0');

	$jpa = new JoymePageAddons();

	$model = new ShortCommentsPage( $pageid );

	$shortNum = $model->getPageShortCommentNum();

	$count = $model->getShortCommentLikeCount();

	$data = $jpa->getPageAddons($pageid);

	if(!empty($data)){

		$data->countnum = $shortNum+$count+$data->like_count;
	}
	if ( $data ) {
		$data->list = $model->shortCommentsList();
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$out = JoymeWikiUser::getJson('no this page','0');
	}
	return $out;
}

function wfClickLike( $aid, $uid ){
	$aid = intval($aid);
	$ClickLike = new ClickLike($aid);
	$isClick = $ClickLike->checkIsClickLike($uid);
	if($isClick){
		$out = JoymeWikiUser::getJson('请勿重复点赞','0');
		return $out;
	}
	$logID = $ClickLike->clickLikeLog($uid);
	if(!$logID){
		$out = JoymeWikiUser::getJson('插入记录失败','0');
		return $out;
	}
	$jpa = new JoymePageAddons();
	$data = $jpa->updateFieldAddOne(array('like_count', $aid));
	if ( $data ) {
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$out = JoymeWikiUser::getJson('页面不存在','0');
	}
	return $out;
}

function wfShortComments( $aid ){
	$aid = intval($aid);
	$ShortComment = new ShortCommentsPage($aid);
	$data = $ShortComment->shortCommentsList();
	$out = JoymeWikiUser::getJson($data,'1');
    return $out;
}

function wfAddShortComment( $aid, $con, $uid ){
	$ShortComment = new ShortCommentsPage($aid);
	$data = $ShortComment->checkWord($con);
	if( $data['rs'] == 1 ){
		$data = $ShortComment->addShortComment($con, $uid);
		JoymeRank::addContentRank('hot', $aid);//增加排行榜热度
		//发表短评
		JoymeWikiUser::pointsreport(30,$uid);
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$data['msg'] = '短评中含有敏感词';
		$out = JoymeWikiUser::getJson($data,'2');
	}
	return $out;
}

function wfShortCommentClickLike( $aid, $pscid, $uid ){
	$aid = intval($aid);
	$pscid = intval($pscid);
	$ShortComment = new ShortCommentsPage($aid);
	$data = $ShortComment->addClickLike($pscid, $uid);
	JoymeRank::addContentRank('hot', $aid);//增加排行榜热度
	$out = JoymeWikiUser::getJson($data,'1');
	return $out;
}