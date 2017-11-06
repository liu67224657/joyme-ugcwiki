<?php
/**
 * This Class manages the User and Site follows.
 */
class UserUserFollow{

	/** add a user follow site action to the database.
	 *
	 *  @param $follower User object: the user who initiates the follow
	 *  @param $followee User object: the user to be followed
	 *	@return mixed: false if unsuccessful, id if successful
	 */
	public function addUserUserFollow($follower, $followee){

		if ($follower == null || $followee == null ){
			return false;
		}
		if ($followee->getId() == 0){
			return false;
		}
		if ($follower == $followee){
			return false;
		}
		//检测权限
		$stats = new UserStats( $followee->getId(), $followee->getName() );
		$stats_data = $stats->getUserStats();
		
		if($stats_data['is_attention'] == '0'){
			return -1;
		}
		
		//检测是否已关注
		if ( $this->checkUserUserFollow( $follower, $followee ) != false ){
			return 0;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'user_user_follow',
			array(
				'f_user_id' => $follower->getId(),
				'f_user_name' => $follower->getName(),
				'f_target_user_id' => $followee->getId(),
				'f_target_user_name' => $followee->getName(),
				'f_date' => date( 'Y-m-d H:i:s' )
			), __METHOD__
		);
		$followId = $dbw->insertId();
		//更新我的好友数
		$stats = new UserStatsTrack( $follower->getId(), $follower->getName() );
		$stats->incStatField( 'friend' ); //use friend record to count the number of people followed.
		//更新我的私信数
		$row = $dbw->selectRow(
				'user_board_list',
				array(
						'ubl_id', 'ub_msg_count'
				),
				array(
						'ub_user_id'=>$follower->getId(),
						'ub_friend_id' => $followee->getId()
				),
				__METHOD__
		);
		
		if(!empty($row) && $row->ub_msg_count > 0 ){
			$dbw->update('user_board_list', array('ub_isfollow'=>'1'), array('ubl_id'=>$row->ubl_id));
			$stats->incStatField('user_board_count',$row->ub_msg_count);
			$stats->decStatField('user_board_count_priv',$row->ub_msg_count);
		}
		
		//更新对方粉丝数
		$stats = new UserStatsTrack( $followee->getId(), $followee->getName() );
		$stats->incStatField( 'foe' ); // use foe record to count the number of people following.
		// TODO: Notify the followee?
		/*
		EchoHooks::onAddNewConsiderMe($followee->getId(),$follower->getName(),$follower->getId(),true);
		

		JoymeWikiUser::addActionLog(
		$follower->getId(),
		5,
		'关注了<a href="/home/用户:'.$followee->getName().'" target="_blank">'.$followee->getName().'</a>'
		);
		*/
		return $followId;

	}
	
	public static function getJson($message,$rs='success'){
		return json_encode(array('message'=>$message,$rs=>'1'));
	}

	/**
	 * Remove a follower from followee
	 *
	 * @param $user1 User object: user to be removed
	 * @param $user2 string: site prefix
	 * @return bool: true if successfully deleted
	 */
	public function deleteUserUserFollow($follower, $followee,$fan=false){
		if ($follower == null || $followee == null ){
			return false;
		}
		
		if($follower == $followee){
			return false;
		}
		
		//检测是否已关注
		if ( $this->checkUserUserFollow( $follower, $followee ) == false ){
			return -1;
		}

		$dbw = wfGetDB( DB_MASTER );
		$rs = $dbw->delete(
			'user_user_follow',
			array( 'f_user_id' => $follower->getId(), 'f_target_user_id' => $followee->getId() ),
			__METHOD__
		);
		if(!$rs){
			return false;
		}
		
		$stats = new UserStatsTrack( $follower->getId(), $follower->getName() );
		$stats->decStatField( 'friend' ); //use friend record to count the number of people followed.
		
		//更新我的私信数
		$dbr = wfGetDB( DB_SLAVE );
		$row = $dbr->selectRow(
				'user_board_list',
				array(
						'ubl_id', 'ub_msg_count'
				),
				array(
						'ub_user_id'=>$follower->getId(),
						'ub_friend_id' => $followee->getId()
				),
				__METHOD__
		);
		
		if(!empty($row) && $row->ub_msg_count > 0 ){
			$dbw->update('user_board_list', array('ub_isfollow'=>'2'), array('ubl_id'=>$row->ubl_id));
			$stats->decStatField('user_board_count',$row->ub_msg_count);
			$stats->incStatField('user_board_count_priv',$row->ub_msg_count);
		}
		
		$stats = new UserStatsTrack( $followee->getId(), $followee->getName() );
		$stats->decStatField( 'foe' ); // use foe record to count the number of people following.
		
		if($fan == false){
			//EchoHooks::onAddNewConsiderMe($followee->getId(),$follower->getName(),$follower->getId(),false);
		}
		return true;

	}
	/**
	* @param $user User Object
	* @param $huijiPrefix string: same as wgHuijiPrefix
	* @return Mixed: integer or boolean false
	*/
	public function checkUserUserFollow($follower, $followee){
		//TODO: We are not caching the result for now. 
		//But if we have a performance hit, this is where to go.
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(			
			'user_user_follow',
			array( 'f_id' ),
			array( 'f_user_id' => $follower->getId(), 'f_target_user_id' => $followee->getId() ),
			__METHOD__
		);
		if ($s != false){
			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * @param $user User Object
	 * @param $huijiPrefix string: same as wgHuijiPrefix
	 * @return Mixed: integer or boolean false
	 */
	public function getUserUserIsFollow($user_id, $follow_ids,$type=1){
		//TODO: We are not caching the result for now.
		//But if we have a performance hit, this is where to go.
		$dbr = wfGetDB( DB_SLAVE );
		
		$where = array();
		
		if ($type == 1) {
			$where['f_user_id'] = $follow_ids;
			$where['f_target_user_id'] = $user_id;
			$field = 'f_user_id';
		} else {
			$where['f_user_id'] = $user_id;
			$where['f_target_user_id'] = $follow_ids;
			$field = 'f_target_user_id';
		}
		
		if(is_array($follow_ids)){
			$list = $dbr->select(
					'user_user_follow',
					array( $field ),
					$where,
					__METHOD__
			);
			$flist = array();
			foreach ($follow_ids as $k=>$follow_id){
				$flist[$follow_id] = 0;
				foreach ($list as $v){
					if($v->$field == $follow_id){
						$flist[$follow_id] = 1;
						break;
					}
				}
			}
			return $flist;
		}else{
			$row = $dbr->selectRow(
					'user_user_follow',
					array( $field ),
					$where,
					__METHOD__
			);
			if($row){
				return true;
			}else{
				return false;
			}
		}
		
	}

	/**
	 * Get the Follower or Following list for the current user.
	 *
	 * @param $type Integer: 1 for following, 2 (or anything else but 1) for followers
	 * @param $limit Integer: used as the LIMIT in the SQL query
	 * @param $page Integer: if greater than 0, will be used to calculate the
	 *                       OFFSET for the SQL query
	 * @return Array: array of follower/following information
	 */
	public function getFollowList( $user_id, $type = 0, $limit = 0, $page = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );

		$where = array();
		$options = array();
		if ($type != 1) {
			$where['f_target_user_id'] = $user_id;
		} else {
			$where['f_user_id'] = $user_id;
		}
		
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$options['LIMIT'] = $limit;
			$options['OFFSET'] = $limitvalue;
		}
		$options['ORDER BY'] = 'f_date DESC';
		$res = $dbr->select(
			'user_user_follow',
			array(
				'f_id', 'f_user_id', 'f_user_name', 'f_target_user_id',
				'f_target_user_name', 'f_date'
			),
			$where,
			__METHOD__,
			$options
		);

		$requests = array();
		foreach ( $res as $row ) {
			$requests[] = array(
				'id' => $row->f_id,
				'timestamp' => ( $row->f_date ),
				'user_id' => ( $type != 1? $row->f_user_id : $row->f_target_user_id),
				'user_name' => ( $type != 1? $row->f_user_name : $row->f_target_user_name),
				'type' => $type
			);
		}

		return $requests;
	}
	
	public function displayFollowList($uid,$rel_type,$follows){
		global $wgUser;
		if(empty($follows)){
			return '';
		}
		
		$target = SpecialPage::getTitleFor('ViewFollows');
		$jcontribution = SpecialPage::getTitleFor('JContribution');
		
		$follow_ids = array(0);
		foreach ( $follows as $follow ) {
			$follow_ids[] = $follow['user_id'];
		}
			
		$joymewikiuser = new JoymeWikiUser();
		$user_profiles = $joymewikiuser->getProfile($follow_ids);

		$is_fans_list = $this->getUserUserIsFollow($wgUser->getId(), $follow_ids,1);
		$is_follow_list = $this->getUserUserIsFollow($wgUser->getId(), $follow_ids,2);
		
		$output = '';
		
		foreach ( $follows as $follow ) {
			$ust = new UserStats($follow['user_id'],$follow['user_name']);
			$allinfo = $ust->getUserStats();
		
			$allinfo['url'] = '';
			foreach ($user_profiles as $pro){
				if($follow['user_id'] == $pro['uid']){
					$allinfo['url'] = $pro['icon'];
					$allinfo['gender'] = $pro['sex'];
					break;
				}
			}
		
			$allinfo['username'] = $follow['user_name'];
			$userPage = Title::makeTitle( NS_USER, $allinfo['username'] );
			
			$userPageURL = htmlspecialchars( $userPage->getFullURL() );
			$avatar_img = $allinfo['url'];
			$user_status = $allinfo['brief'];
			$user_count = $allinfo['friend_count'];
			$user_counted = $allinfo['foe_count'];
			$editcount = $allinfo['total_edit_count'];
			$user_name_display = $allinfo['username'];
			
			//0 未关注 1 粉丝 2 已关注 3 相互关注
			if($is_follow_list[$follow['user_id']] == 1 && $is_fans_list[$follow['user_id']] == 1){
				$is_follow = 3;
				$follow_str = '相互关注';
				$follow_class = 'fa-exchange';
				$button_class = 'each-follow';
			}else if($is_follow_list[$follow['user_id']] == 1){
				$is_follow = 2;
				$follow_str = '已关注';
				$follow_class = 'fa-check';
				$button_class = 'each-follow';
			}else if($is_fans_list[$follow['user_id']] == 1){
				$is_follow = 1;
				$follow_str = '关注';
				$follow_class = 'fa-plus';
				$button_class = 'handle';
			}else{
				$is_follow = 0;
				$follow_str = '关注';
				$follow_class = 'fa-plus';
				$button_class = 'handle';
			}
		
			if($follow['user_id'] != $wgUser->getId()){
				$button_follow_str = '
					<button class="'.($button_class=='handle'?'user-user-follow':'').' '.$button_class.' fol-style" type="button" data-uid="'.$follow['user_id'].'" data-action="'.($button_class=='handle'?'follow':'').'" data-follow-status="'.$is_follow.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fa '.$follow_class.'" aria-hidden="true"></i>'.$follow_str.'
					</button>';
				if($wgUser->getId() == $uid){
					$button_more_str = '<div class="dropdown web-hide fn-right">
											<button  type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dLabel">
                                        		<li><a href="'.UserBoard::getUserBoardToBoardURL($follow['user_id']).'" target="_blank">私信</a></li>
                                                <li><a href="javascript:;" class="user-user-follow" data-action="'.($rel_type==1?'unfollow':'unfans').'" data-uid="'.$follow['user_id'].'">'.($rel_type==1?'取消关注':'移除粉丝').'</a></li>
                                            </ul>
                                	</div>';
				}else{
					$button_more_str='<a class="fn-right web-hide fol-style sixin-link" href="'.UserBoard::getUserBoardToBoardURL($follow['user_id']).'">私信</a>';
				}
					
			}else{
				$button_follow_str = '';
				$button_more_str = '';
			}
		
			$item ='<li id="user-follow-'.$follow['user_id'].'">
                                <div class="list-item-l guzhu-l">
                                    <cite><a target="_blank" href="'.$userPageURL.'" class="r-user-name"><img src="'.$avatar_img.'"></a></cite>
                                </div>
                                <div class="list-item-r guzhu-r">
                                    <div class="item-r-name fn-clear">
                                        <a href="'.$userPageURL.'" class="r-user-name">'.$user_name_display.'</a>
	                                        '.$button_more_str.'
                                        <div class="dropdown fn-right">
                                            '.$button_follow_str.'
                                        </div>
                                    </div>
                                    <div class="item-r-text">
                                        <font>个性签名</font>'.$user_status.'
                                    </div>
                                    <div class="item-r-other fn-clear">
                                        <span>关注&nbsp;'.Linker::LinkKnown($target, $user_count, array(), array('user' => $user_name_display, 'rel_type' => 1)).'</span>
                                        <span>粉丝&nbsp;'.Linker::LinkKnown($target, $user_counted, array(), array('user' => $user_name_display, 'rel_type' => 2)).'</span>
                                        <span>编辑&nbsp;'.Linker::LinkKnown($jcontribution, $editcount, array(), array('userid' => $follow['user_id'])).'</span>
                                    </div>
                                </div>
                            </li>';
			$output.=$item;
		}
		return $output;
	}
	
	
	/**
	* Used to pass Echo your definition for the notification category and the 
	* notification itself (as well as any custom icons).
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
        $notificationCategories['follow-msg'] = array(
            'priority' => 3,
            'tooltip' => 'echo-pref-tooltip-follow-msg',
        );
        $notifications['follow-msg'] = array(
        	// 'primary-link' => array('message' => 'notification-link-text-respond-to-user', 'destination' => 'agent'),
            'category' => 'follow-msg',
            'group' => 'positive',
            'formatter-class' => 'EchoFollowFormatter',
            'title-message' => 'notification-follow',
            'title-params' => array( 'agent', 'agent-link', 'follow', 'main-title-text' ),
            'flyout-message' => 'notification-follow-flyout',
            'flyout-params' => array( 'agent', 'agent-link', 'follow', 'main-title-text' ),
            'payload' => array( 'summary' ),
            'email-subject-message' => 'notification-follow-email-subject',
            'email-subject-params' => array( 'agent' ),
            'email-body-message' => 'notification-follow-email-body',
            'email-body-params' => array( 'agent', 'follow', 'main-title-text', 'email-footer' ),
            'email-body-batch-message' => 'notification-follow-email-batch-body',
            'email-body-batch-params' => array( 'agent', 'main-title-text' ),
            'icon' => 'gratitude',
            'section' => 'alert',
        );
        return true;
    }


	/**
	* Used to define who gets the notifications (for example, the user who performed the edit)
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
	 	switch ( $event->getType() ) {
	 		case 'follow-msg':
	 			$extra = $event->getExtra();
	 			if ( !$extra || !isset( $extra['followee-user-id'] ) ) {
	 				break;
	 			}
	 			$recipientId = $extra['followee-user-id'];
	 			$recipient = User::newFromId( $recipientId );
	 			$users[$recipientId] = $recipient;
	 			break;
	 	}
	 	return true;
	}

}
class EchoFollowFormatter extends EchoCommentFormatter {
   /**
     * @param $event EchoEvent
     * @param $param
     * @param $message Message
     * @param $user User
     */
    protected function processParam( $event, $param, $message, $user ) {
        if ( $param === 'follow' ) {
            $this->setTitleLink(
                $event,
                $message,
                array(
                    'class' => 'mw-echo-follow-msg',
                    'linkText' => wfMessage('notification-follow-msg-link')->text(),
                )
            );
        } elseif ( $param === 'agent-link') {
        	$eventData = $event->getExtra();
            if ( !isset( $eventData['agent-page']) ) {
                $message->params( '' );
                return;
            }
            $link = $this->buildLinkParam(
                $eventData['agent-page'],
                array(
                    'class' => 'mw-echo-follow-msg',
                    'linkText' => $eventData['agent-page']->getText(),
                )
            );
            $message->params( $link );
        } else {
            parent::processParam( $event, $param, $message, $user );
        }
    }
}

