<?php
/**
 * AJAX functions used by UserBoard.
 */
$wgAjaxExportList[] = 'wfUserViewBoardList';
$wgAjaxExportList[] = 'wfGetBoardMessage';
$wgAjaxExportList[] = 'wfClearBoardMessage';

function wfUserViewBoardList( $page, $type ) {
	global $wgUser;
	$out = JoymeWikiUser::getJson('发生未知错误','0');

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('您尚未登录哦','0');
		return $out;
	}
	
	$sbl = new SpecialBoardList();
	$list = $sbl->getBoardList($type,10,$page);
	$data = '';
	if ( $list ) {
		foreach ($list as $v){
			if($v->ub_msg_count > 0){
				$msg_count_str = '<i class="news-count '.($v->ub_msg_count>99?'on':'').'">'.($v->ub_msg_count>99?99:$v->ub_msg_count).'</i>';
			}else{
				$msg_count_str = '';
			}
			$msg ='<li id="board_uid_'.$v->ub_friend_id.'">
							<a target="_blank" href="'.UserBoard::getUserBoardToBoardURL( $v->ub_friend_id ).'">
                                <div class="list-item-l">
                                    <cite class="board-headicon"><img src="'.$v->url.'">'.$msg_count_str.'</cite>
                                </div>
                                <div class="list-item-r">
									
                                    <div class="item-r-name fn-clear">
                                        <span class="fn-left">'.$v->ub_friend_name.'</span>
                                        <b class="time-stamp fn-right">'.$v->ub_date.'</b>
                                    </div>
                                    <div class="item-r-text">
                                       	'.$v->ub_message.'
                                    </div>
                                </div>
                             </a>
                             <i class="del-icon" data-uid="'.$v->ub_friend_id.'"></i>
                          </li>';
			$data.=$msg;
		}
		$out = JoymeWikiUser::getJson($data,'1');
	}else{
		$out = JoymeWikiUser::getJson('没有更多了','0');
	}
	return $out;
}

function wfGetBoardMessage( $friend_id, $page ) {
	global $wgUser;

	// Don't allow blocked users to send messages and also don't allow message
	// sending when the database is locked for some reason
	if ( $wgUser->isBlocked() || wfReadOnly() ) {
		$out = JoymeWikiUser::getJson('error','-1');
		return $out;
	}
	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = JoymeWikiUser::getJson('no login','-1');
		return $out;
	}
	$b = new UserBoard();

	$rs = $b->displayMessages($wgUser->getId(), $friend_id, $page );
	
	if($rs == -1){
		$out = JoymeWikiUser::getJson('没有更多了','-1');
	}else{
		$out = JoymeWikiUser::getJson($rs,'1');
	}
	
	return $out;
}


function wfClearBoardMessage( $friend_id='',$type=1 ) {
	global $wgUser;

	// Don't allow deleting messages when the database is locked for some reason
	if ( wfReadOnly() ) {
		return '';
	}

	$b = new UserBoard();
	$b->clearMessage( $wgUser->getId(), $friend_id ,$type);

	return 'ok';
}
/*
 * //单条删除、备用
$wgAjaxExportList[] = 'wfDeleteBoardMessage';
function wfDeleteBoardMessage( $ub_id ) {
	global $wgUser;

	// Don't allow deleting messages when the database is locked for some reason
	if ( wfReadOnly() ) {
		return '';
	}

	$b = new UserBoard();
	if (
		$b->doesUserOwnMessage( $wgUser->getID(), $ub_id ) ||
		$wgUser->isAllowed( 'userboard-delete' )
	) {
		$b->deleteMessage( $ub_id );
	}

	return 'ok';
}
*/