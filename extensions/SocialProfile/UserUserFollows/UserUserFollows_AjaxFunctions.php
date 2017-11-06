<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserViewFollows';
//$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
//$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
$wgAjaxExportList[] = 'wfUserFollowsInfoResponse';
$wgAjaxExportList[] = 'wfUserUserIsFollow';
function wfUserUserIsFollow( $uid ) {
	global $wgUser;
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('您尚未登录哦','0');
		return $out;
	}

	$uuf = new UserUserFollow();
	$isfollow = $uuf->getUserUserIsFollow( $wgUser->getId(),$uid,2);
	$out = JoymeWikiUser::getJson($isfollow,'1');
	//TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserViewFollows( $page, $uid, $type ) {
	global $wgUser;
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('您尚未登录哦','0');
		return $out;
	}

	$uuf = new UserUserFollow();
	$follows = $uuf->getFollowList( $uid, $type, 10, $page);
	if ( $follows ) {
		$rs = $uuf->displayFollowList($uid, $type, $follows);
		$out = JoymeWikiUser::getJson($rs,'1');
	}else{
		$out = JoymeWikiUser::getJson('没有更多了','0');
	}
	//TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserUserFollowsResponse( $follower, $followee ) {
	global $wgUser;
	$out = UserUserFollow::getJson('发生未知错误','false');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = UserUserFollow::getJson('您尚未登录哦','false');
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = UserUserFollow::getJson('您没有操作权限哦','false');
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = UserUserFollow::getJson('暂时无法关注哦','false');
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = UserUserFollow::getJson('您没有编辑权限哦','false');
		return $out;
	}
	if($followee == $follower){
		$out = UserUserFollow::getJson('您不能添加自己关注哦','false');
		return $out;
	}
	if ( $follower == $wgUser->getId()){
		$b = new UserUserFollow();
		$followee = User::newFromId($followee);
		$rs = $b->addUserUserFollow($wgUser,$followee);
		if ($rs === -1){
			$out = UserUserFollow::getJson('对方不允许关注哦','false');
		}else if ($rs == 0){
			$out = UserUserFollow::getJson('您已经关注对方了哦','false');
		}else if (!empty($rs)){
			$out = UserUserFollow::getJson('关注成功');
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserUserUnfollowsResponse( $follower, $followee ) {
	global $wgUser;

	$out = UserUserFollow::getJson('发生未知错误','false');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = UserUserFollow::getJson('您尚未登录哦','false');
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = UserUserFollow::getJson('您没有操作权限哦','false');
		return $out;
	}
	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = UserUserFollow::getJson('暂时无法操作哦','false');
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = UserUserFollow::getJson('您没有编辑权限哦','false');
		return $out;
	}
	
	if($followee === $follower){
		$out = UserUserFollow::getJson('您不能操作自己哦','false');
		return $out;
	}
	if ( $follower == $wgUser->getId()){
		$b = new UserUserFollow();
		$followee = User::newFromId($followee);
		$rs = $b->deleteUserUserFollow($wgUser,$followee);
		if ($rs === -1){
			$out = UserUserFollow::getJson('您已经移除关注了哦','false');
		}else if($rs){
			$out = UserUserFollow::getJson('操作成功');
		}
	}elseif( $followee == $wgUser->getId()){
		$b = new UserUserFollow();
		$follower = User::newFromId($follower);
		$rs = $b->deleteUserUserFollow($follower,$wgUser,true);
		if ($rs === -1){
			$out = UserUserFollow::getJson('您已经移除粉丝了哦','false');
		}else if($rs){
			$out = UserUserFollow::getJson('操作成功');
		}
	}
	//$out = UserUserFollow::getJson($followee.$follower.$wgUser->getId(),'false');

	return $out;
}
function wfUserFollowsInfoResponse( $username ) {
	global $wgUser;
	$user = User::newFromName( $username );
	//No such user
	if ($username == null||$user == null || $user->getId() == 0 ){
		$data = '<div class="wiki-person-popup" data-username="'.$username.'">
					<div class="wpp-top">
						<div class="toux"><a href="javascript:;"><img src="http://lib.joyme.com/static/theme/default/img/head_is_m.jpg" alt=""></a></div>
						<div class="wpp-info">
							<p class="fn-clear">
								<span class="name">'.$username.'</span>
							</p>
						</div>
						<div class="wpp-intro"><p>用户中心未激活，资料保密中……</p></div>
					</div>
				</div>';
		
		$out = UserUserFollow::getJson($data);
		return $out;
	}
	
	//获取用户基础信息
	$profile = new JoymeWikiUser();
	$userprofile = $profile->getProfile($user->getId());
	
	if(empty($userprofile)){
		$data = '<div class="wiki-person-popup" data-username="'.$username.'">
					<div class="wpp-top">
						<div class="toux"><a href="javascript:;"><img src="http://lib.joyme.com/static/theme/default/img/head_is_m.jpg" alt=""></a></div>
						<div class="wpp-info">
							<p class="fn-clear">
								<span class="name">'.$username.'</span>
							</p>
						</div>
						<div class="wpp-intro"><p>用户中心未激活，资料保密中……</p></div>
					</div>
				</div>';
		
		$out = UserUserFollow::getJson($data);
		return $out;
	}else{
		//$username = $user->getName();
		$userprofile = $userprofile[0];
		$userprofile['icon'] = $userprofile['icon'].'?imageView2/1/w/52/h/52';
		
		if ($userprofile['sex'] == '1'){
			$genderIcon = 'man';
		} elseif ($userprofile['sex'] == '0'){
			$genderIcon = 'female';
		} else {
			$genderIcon = '';
		}
	}
	//加载stats信息
	$ust = new UserStats( $user->getId(),$user->getName() );
	$stats = $ust->getUserStats();
	
	//判断权限
	$group = $user->getGroups();
	
	if(in_array('sysop', $group) || in_array('bureaucrat', $group)){
		$groupicon = 'vip';
	}else{
		$groupicon = '';
	}
	//判断是否关注
	if ( !$wgUser->isLoggedIn() ) {
//		$followinfo = '<a href="javascript:mw.loginbox.login();" class="gz-p"><i class="icon-plus"></i>关注</a>';
		$followinfo = '<a href="javascript:loginDiv();" class="gz-p"><i class="icon-plus"></i>关注</a>';
	}else{
		$uuf = new UserUserFollow();
		$isfollow = $uuf->getUserUserIsFollow( $wgUser->getId(),$user->getId(),2);

		if($isfollow){
			$followinfo = '<a href="javascript:;" class="user-userinfo-follow" data-action="unfollow" data-uid="'.$user->getId().'">已关注</a>';
		}else{
			$followinfo = '<a href="javascript:;" class="user-userinfo-follow gz-p" data-action="follow" data-uid="'.$user->getId().'"><i class="icon-plus"></i>关注</a>';
		}
		
	}
	
	//关注数、粉丝数、编辑数
	$gzfsbjstr = '<span><a target="_blank" title="特殊:ViewFollows" href="/home/index.php?title=special:ViewFollows&user='.$username.'&rel_type=1">关注'.$stats['friend_count'].'</a></span><cite></cite>
		<span><a target="_blank" title="特殊:ViewFollows" href="/home/index.php?title=special:ViewFollows&user='.$username.'&rel_type=2">粉丝'.$stats['foe_count'].'</a></span><cite></cite>
		<span><a target="_blank" title="特殊:着迷贡献" href="/home/index.php?title=%E7%89%B9%E6%AE%8A:%E7%9D%80%E8%BF%B7%E8%B4%A1%E7%8C%AE&userid='.$user->getId().'">编辑'.$stats['total_edit_count'].'</a></span>';
	
	if($wgUser->getId() == $user->getId()){
		$bottomstr = '';
	}else if ( !$wgUser->isLoggedIn() ) {
		/*$bottomstr = '<div class="gz-qx">
			            <p class="gz-sx">
							'.$followinfo.'
							<a href="javascript:mw.loginbox.login();" class="sx-p">私信</a>
						</p>
			        </div>';*/
		$bottomstr = '<div class="gz-qx">
			            <p class="gz-sx">
							'.$followinfo.'
							<a href="javascript:loginDiv();" class="sx-p">私信</a>
						</p>
			        </div>';
		$gzfsbjstr = '<span>关注'.$stats['friend_count'].'</span><cite></cite>
						<span>粉丝'.$stats['foe_count'].'</span><cite></cite>
						<span>编辑'.$stats['total_edit_count'].'</span>';
	}else{
		$bottomstr = '<div class="gz-qx">
			            <p class="gz-sx">
							'.$followinfo.'
							<a href="'.UserBoard::getUserBoardToBoardURL($user->getId()).'" target="_blank" class="sx-p">私信</a>
						</p>
			        </div>';
	}
	
	
	
	//处理个人签名
	if($wgUser->getId() == $user->getId()){
		$stats['brief'] = empty($stats['brief'])?'一句话介绍一下自己吧，让别人更了解你':$stats['brief'];
	}else{
		$stats['brief'] = empty($stats['brief'])?'这个人很懒，什么都没留下……':$stats['brief'];
	}
	
	$target = SpecialPage::getTitleFor('ViewFollows');
	$jcontribution = SpecialPage::getTitleFor('JContribution');
	
	$data = '<div class="wiki-person-popup" data-username="'.$username.'">
				<div class="wpp-top">
					<div class="toux"><a href="' . htmlspecialchars( '/home/用户:'.$username ) . '"><img src="'.$userprofile['icon'].'" alt=""></a></div>
					<div class="wpp-info">
						<p class="fn-clear">
							<span class="name"><a href="' . htmlspecialchars( '/home/用户:'.$username ) . '">'.$username.'</a></span>
							<span class="'.$genderIcon.'"></span>
							<span class="'.$groupicon.'"></span>
						</p>
					</div>
					<div class="wpp-intro"><p>简介：'.$stats['brief'].'</p></div>
				</div>
			    <div class="wpp-bottom">
			        <div class="gz-fs-bj">
							'.$gzfsbjstr.'
					</div>
			        '.$bottomstr.'
			    </div>
			</div>';
	
	$out = UserUserFollow::getJson($data);
	return $out;
}

