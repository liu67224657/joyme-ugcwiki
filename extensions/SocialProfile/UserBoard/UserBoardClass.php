<?php
/**
 * Functions for managing user board data
 */
class UserBoard {

	/**
	 * Constructor
	 */
	public function __construct() {}
	
	public function getSessionId($uid,$fid,$date){
		return md5($uid.'|'.$fid.'|'.$date);
	}

	/**
	 * Sends a user board message to another user.
	 * Performs the insertion to user_board table, sends e-mail notification
	 * (if appliable), and increases social statistics as appropriate.
	 *
	 * @param $user_id_from Integer: user ID of the sender
	 * @param $user_name_from Mixed: user name of the sender
	 * @param $user_id_to Integer: user ID of the reciever
	 * @param $user_name_to Mixed: user name of the reciever
	 * @param $message Mixed: message text
	 * @param $message_type Integer: 0 for public message
	 * @return Integer: the inserted value of ub_id row
	 */
	public function sendBoardMessage( $sender_uid, $receiver_uid, $message,$sender_isfollow,$receiver_isfollow) {
		$dbw = wfGetDB( DB_MASTER );
		
		$message = urldecode($message);
		
		$date = $nowdate = date( 'Y-m-d H:i:s' );

		//修改自己的聊天list表
		$s = $dbw->selectRow(
				'user_board_list',
				array( 'ubl_id','ub_date' ),
				array( 'ub_user_id' => $sender_uid,'ub_friend_id' => $receiver_uid ),
				__METHOD__
		);
		if($s){
			$date = $s->ub_date;
		}
		$session_id = $this->getSessionId($sender_uid, $receiver_uid,$date);
		
		$res = $dbw->insert(
				'user_board',
				array(
						'ub_session_id' => $session_id,
						'ub_sender_uid' => $sender_uid,
						'ub_receiver_uid' => $receiver_uid,
						'ub_message' => $message,
						'ub_date' => $nowdate,
						'ub_status' => '1'
				),
				__METHOD__
		);
		if ( $res ) {
			$ub_id = $dbw->insertId();
		} else {
			return false;
		}
		if ( !$s ) {
			$dbw->insert(
					'user_board_list',
					array(
							'ub_user_id' => $sender_uid,
							'ub_friend_id' => $receiver_uid,
							'ub_id' => $ub_id,
							'ub_date' => $nowdate,
							'ub_isfollow'=>$sender_isfollow
					),
					__METHOD__
			);
		}else{
			$dbw->update(
					'user_board_list',
					array( 'ub_id' => $ub_id,'ub_isfollow'=>$sender_isfollow ),
					array( 'ubl_id' => $s->ubl_id ),
					__METHOD__
			);
		}
		//修改对方的list表
		$s = $dbw->selectRow(
				'user_board_list',
				array( 'ubl_id','ub_date','ub_msg_count' ),
				array( 'ub_user_id' => $receiver_uid,'ub_friend_id' => $sender_uid ),
				__METHOD__
		);
		
		if($s){
			$date = $s->ub_date;
		}
		$session_id = $this->getSessionId($receiver_uid, $sender_uid,$date);
		
		$res = $dbw->insert(
				'user_board',
				array(
						'ub_session_id' => $session_id,
						'ub_sender_uid' => $sender_uid,
						'ub_receiver_uid' => $receiver_uid,
						'ub_message' => $message,
						'ub_date' => $nowdate,
						'ub_status' => '0'
				),
				__METHOD__
		);
		if ( $res ) {
			$ub_id = $dbw->insertId();
		} else {
			return false;
		}
		if ( !$s ) {
			$dbw->insert(
					'user_board_list',
					array(
							'ub_user_id' => $receiver_uid,
							'ub_friend_id' => $sender_uid,
							'ub_id' => $ub_id,
							'ub_date' => $nowdate,
							'ub_msg_count' => '1',
							'ub_isfollow'=>$receiver_isfollow
					),
					__METHOD__
			);
		}else{
			$dbw->update(
					'user_board_list',
					array( 'ub_id' => $ub_id,'ub_isfollow'=>$receiver_isfollow,'ub_msg_count' => intval($s->ub_msg_count)+1 ),
					array( 'ubl_id' => $s->ubl_id ),
					__METHOD__
			);
		}
		$field = $receiver_isfollow==1?'user_board_count':'user_board_count_priv';

		$dbw->update(
				'user_stats',
				array( $field . '=' . $field . "+1" ),
				array( 'stats_user_id' => $receiver_uid  ),
				__METHOD__
		);
		$stats = new UserStatsTrack( $receiver_uid );
		$stats->clearCache();
		
		return $ub_id;
	}

	/**
	 * Sends an email to a user if someone wrote on their board
	 *
	 * @param $user_id_to Integer: user ID of the reciever
	 * @param $user_from Mixed: the user name of the person who wrote the board message
	 */
	public function sendBoardNotificationEmail( $user_id_to, $user_from ) {
		$user = User::newFromId( $user_id_to );
		$user->loadFromId();

		// Send email if user's email is confirmed and s/he's opted in to recieving social notifications
		if ( $user->isEmailConfirmed() && $user->getIntOption( 'notifymessage', 1 ) ) {
			$board_link = SpecialPage::getTitleFor( 'UserBoard' );
			$update_profile_link = SpecialPage::getTitleFor( 'UpdateProfile' );
			$subject = wfMessage( 'message_received_subject', $user_from )->parse();
			$body = array(
				'html' => wfMessage( 'message_received_body_html',
					$user->getName(),
					$user_from
				)->parse(),
				'text' => wfMessage( 'message_received_body',
					$user->getName(),
					$user_from,
					htmlspecialchars( $board_link->getFullURL() ),
					htmlspecialchars( $update_profile_link->getFullURL() )
				)->text()
			);

			$user->sendMail( $subject, $body );
		}
	}
	
	/**
	 * set board clientID by uid
	 *
	 * @param $user_id_to Integer: user ID 
	 */
	public function setBoardClientidByUid( $user_id ,$client_id) {
		global $wgMemc,$wgCachePrefix;
		
		$key = wfMemcKey( 'user', 'wsclientid', $user_id );
		$data = $wgMemc->get( $key );
		if(empty($data)){
			$wgMemc->set( $key ,$client_id);
		}else{
			$client_id = $data.'|'.$client_id;
			$wgMemc->set( $key ,$client_id);
		}
		
	}
	
	/**
	 * get board clientID by uid
	 *
	 * @param $user_id_to Integer: user ID 
	 */
	public function getBoardClientidByUid( $user_id ) {
		global $wgMemc,$wgCachePrefix;
		
		$key = wfMemcKey( 'user', 'wsclientid', $user_id );
		$data = $wgMemc->get( $key );
		return $data;
	}
	
	public function clearAllUserClient(){
		global $wgMemc,$wgCachePrefix;
		
		$key = wfMemcKey( 'user', 'wsclientid*' );

		$wgMemc->delete($wgMemc->keys($key));
	}
	
	/**
	 * clear board clientID by uid
	 *
	 * @param $user_id_to Integer: user ID 
	 */
	public function clearBoardClientidByUid( $user_id,$cid ) {
		global $wgMemc,$wgCachePrefix;
		
		$key = wfMemcKey( 'user', 'wsclientid', $user_id );
		$data = $wgMemc->get( $key );
		if($data){
			$toclientidarr = explode('|', $data);
			if(count($toclientidarr) > 1){
				foreach ($toclientidarr as $k=>$client){
					if($cid == $client){
						unset($toclientidarr[$k]);
						break;
					}
				}
				$wgMemc->set( $key,implode('|', $toclientidarr) );
			}else{
				$wgMemc->delete( $key );
			}
		}
	}
	

	/**
	 * clear a user all board message from the database and decreases social
	 *
	 * @param $user_id Integer: ID number of the userid
	 * @param $friend_id Integer: ID number of the friendid
	 * @param $type Integer: type 1关注 2 未关注
	 */
	public function clearMessage( $user_id, $friend_id='' ,$type=1) {
		$dbw = wfGetDB( DB_MASTER );
		
		$where = array( 'ub_user_id' => $user_id );
		
		if($friend_id){
			$where['ub_friend_id'] = $friend_id;
		
			$s = $dbw->selectRow(
					'user_board_list',
					array( 'ub_msg_count','ub_isfollow' ),
					$where,
					__METHOD__
			);
			if ( $s !== false ) {
				$stats = new UserStatsTrack( $user_id );
				if ( $s->ub_isfollow == '1') {
					$stats->decStatField( 'user_board_count',$s->ub_msg_count );
				} else {
					$stats->decStatField( 'user_board_count_priv' ,$s->ub_msg_count);
				}
			}
		}else{
			$stats = new UserStatsTrack( $user_id );
			$stats->clearBoardCount($type);
			$where['ub_isfollow'] = $type;
		}
		
		$nowdate = date( 'Y-m-d H:i:s' );
		
		$dbw->update(
				'user_board_list',
				array('ub_id'=>'0','ub_msg_count'=>'0','ub_date'=>$nowdate),
				$where,
				__METHOD__
		);
	}

	/**
	 * Deletes a user board message from the database and decreases social
	 * statistics as appropriate (either 'user_board_count' or
	 * 'user_board_count_priv' is decreased by one).
	 *
	 * @param $ub_id Integer: ID number of the board message that we want to delete
	 */
	public function deleteMessage( $ub_id ) {
		if ( $ub_id ) {
			$dbw = wfGetDB( DB_MASTER );
			$s = $dbw->selectRow(
				'user_board',
				array( 'ub_user_id', 'ub_type' ),
				array( 'ub_id' => $ub_id ),
				__METHOD__
			);
			if ( $s !== false ) {
				$dbw->delete(
					'user_board',
					array( 'ub_id' => $ub_id ),
					__METHOD__
				);

				$stats = new UserStatsTrack( $s->ub_user_id );
				if ( $s->ub_type == 0 ) {
					$stats->decStatField( 'user_board_count' );
				} else {
					$stats->decStatField( 'user_board_count_priv' );
				}
			}
		}
	}
	
	/**
	 * get a user board message from the database and decreases social
	 * statistics as appropriate (either 'user_board_count' or
	 * 'user_board_count_priv' is decreased by one).
	 *
	 * @param $ub_id Integer: ID number of the board message that we want to get
	 */
	public function getMessage( $ub_id, $user_id, $friend_id) {
		if ( $ub_id ) {
			$dbr = wfGetDB( DB_SLAVE );
			$dbw = wfGetDB( DB_MASTER );
			$s = $dbr->selectRow(
					'user_board',
					array( 'ub_id','ub_status' ),
					array( 'ub_id' => $ub_id ),
					__METHOD__
			);
			if ( $s !== false ) {
				if($s->ub_status == '1'){
					return true;
				}
				$dbw->update(
						'user_board',
						array( 'ub_status' => '1' ),
						array( 'ub_id' => $ub_id ),
						__METHOD__
				);
				
				$s = $dbr->selectRow(
						'user_board_list',
						array( 'ubl_id','ub_msg_count','ub_isfollow' ),
						array( 'ub_user_id' => $user_id,'ub_friend_id'=>$friend_id ),
						__METHOD__
				);
				if ( $s !== false ) {
					$ub_msg_count = $s->ub_msg_count-1<0?0:$s->ub_msg_count-1;
					$dbw->update(
							'user_board_list',
							array( 'ub_msg_count' => $ub_msg_count ),
							array( 'ubl_id' => $s->ubl_id ),
							__METHOD__
					);
					$field = $s->ub_isfollow==1?'user_board_count':'user_board_count_priv';
					
					$dbw->update(
							'user_stats',
							array( $field . '=' . $field . "-1" ),
							array( 'stats_user_id' => $user_id  ),
							__METHOD__
					);
					$stats = new UserStatsTrack( $user_id );
					$stats->clearCache();
					
					return true;
				}
				
			}
		}
		return false;
	}

	/**
	 * Get the user board messages for the user with the ID $user_id.
	 *
	 * @todo FIXME: rewrite this function to be compatible with non-MySQL DBMS
	 * @param $user_id Integer: user ID number
	 * @param $user_id_2 Integer: user ID number of the second user; only used
	 *                            in board-to-board stuff
	 * @param $limit Integer: used to build the LIMIT and OFFSET for the SQL
	 *                        query
	 * @param $page Integer: used to build the LIMIT and OFFSET for the SQL
	 *                       query
	 * @return Array: array of user board messages
	 */
	public function getUserBoardMessages( $user_id, $user_id_2, $limit = 0, $page = 0 ) {
		global $wgUser, $wgOut, $wgTitle;
		$dbr = wfGetDB( DB_SLAVE );
		$dbw = wfGetDB( DB_MASTER );
		
		$row = $dbr->selectRow(
				'user_board_list',
				array(
						'ubl_id', 'ub_date', 'ub_msg_count', 'ub_isfollow'
				),
				array(
						'ub_user_id'=>$user_id,
						'ub_friend_id' => $user_id_2
				),
				__METHOD__
		);
		
		if(!$row){
			return array();
		}
		if($row->ub_msg_count > 0 ){
			$dbw->update('user_board_list', array('ub_msg_count'=>'0'), array('ubl_id'=>$row->ubl_id));
			
			$field = $row->ub_isfollow==1?'user_board_count':'user_board_count_priv';
			$stats_field = $row->ub_isfollow==1?'user_board':'user_board_priv';
			if($wgUser->getId() == $user_id){
				$stats = new UserStats( $user_id ,$wgUser->getName());
			}else{
				$stats = new UserStats( $user_id );
			}
			$stats_data = $stats->getUserStats();
			if($row->ub_msg_count>$stats_data[$stats_field]){
				$row->ub_msg_count = $stats_data[$stats_field];
			}
			
			$stats = new UserStatsTrack($user_id);
			$stats->decStatField($field,$row->ub_msg_count);
		}
		
		$session_id = $this->getSessionId($user_id, $user_id_2,$row->ub_date);
		
		$where = array('ub_session_id'=>$session_id);

		$options = array();
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$options['LIMIT'] = $limit;
			$options['OFFSET'] = $limitvalue;
		}
		$options['ORDER BY'] = 'ub_id desc';
		
		$res = $dbr->select(
				'user_board',
				array(
						'ub_id', 'ub_sender_uid', 'ub_receiver_uid', 'ub_message', 'ub_date', 'ub_status'
				),
				$where,
				__METHOD__,
				$options
		);

		$messages = array();

		foreach ( $res as $row ) {
			//$parser = new Parser();
			//$message_text = $parser->parse( $row->ub_message, $wgTitle, $wgOut->parserOptions(), true );
			//$message_text = $message_text->getText();
			$message_text = nl2br(str_replace(chr(32),'&nbsp;',htmlspecialchars($row->ub_message)));

			$messages[] = array(
				'id' => $row->ub_id,
				'timestamp' => $row->ub_date ,
				'sender_uid' => $row->ub_sender_uid,
				'receiver_uid' => $row->ub_receiver_uid,
				'status' => $row->ub_status,
				'message_text' => $message_text
			);
		}
		
		asort($messages);

		return $messages;
	}
	
	//获取未读消息
	public function getUserBoardUnReadMessages( $user_id, $user_id_2 ) {
		$dbr = wfGetDB( DB_SLAVE );
		$dbw = wfGetDB( DB_MASTER );
	
		$row = $dbr->selectRow(
				'user_board_list',
				array(
						'ubl_id', 'ub_date', 'ub_msg_count', 'ub_isfollow'
				),
				array(
						'ub_user_id'=>$user_id,
						'ub_friend_id' => $user_id_2
				),
				__METHOD__
		);
	
		if(!$row){
			return array();
		}
		if($row->ub_msg_count > 0 ){
			$dbw->update('user_board_list', array('ub_msg_count'=>'0'), array('ubl_id'=>$row->ubl_id));
				
			$field = $row->ub_isfollow==1?'user_board_count':'user_board_count_priv';
				
			$stats = new UserStatsTrack($user_id);
			$stats->decStatField($field,$row->ub_msg_count);
			
			$limit = $row->ub_msg_count;
			
		}else{
			return array();
		}
	
		$session_id = $this->getSessionId($user_id, $user_id_2,$row->ub_date);
	
		$where = array('ub_session_id'=>$session_id);
	
		$options = array(
			'LIMIT'		=> $limit,
			'OFFSET'	=> 0,
			'ORDER BY'	=>'ub_id desc'
		);
	
		$res = $dbr->select(
				'user_board',
				array(
						'ub_id', 'ub_sender_uid', 'ub_receiver_uid', 'ub_message', 'ub_date', 'ub_status'
				),
				$where,
				__METHOD__,
				$options
		);
	
		$messages = array();
	
		foreach ( $res as $row ) {
			$message_text = nl2br(str_replace(chr(32),'&nbsp;',htmlspecialchars($row->ub_message)));
	
			$messages[] = array(
					'id' => $row->ub_id,
					'timestamp' => $row->ub_date ,
					'sender_uid' => $row->ub_sender_uid,
					'receiver_uid' => $row->ub_receiver_uid,
					'status' => $row->ub_status,
					'message_text' => $message_text
			);
		}
	
		asort($messages);
		$messages = array_values($messages);
		return $messages;
	}

	/**
	 * Get the amount of board-to-board messages sent between the users whose
	 * IDs are $user_id and $user_id_2.
	 *
	 * @todo FIXME: rewrite this function to be compatible with non-MySQL DBMS
	 * @param $user_id Integer: user ID of the first user
	 * @param $user_id_2 Integer: user ID of the second user
	 * @return Integer: the amount of board-to-board messages
	 */
	public function getUserBoardToBoardCount( $user_id, $user_id_2 ) {
		global $wgUser, $wgOut, $wgTitle;
		$dbr = wfGetDB( DB_SLAVE );
		
		$row = $dbr->selectRow(
				'user_board_list',
				array(
						'ubl_id', 'ub_date', 'ub_msg_count', 'ub_isfollow'
				),
				array(
						'ub_user_id'=>$user_id,
						'ub_friend_id' => $user_id_2
				),
				__METHOD__
		);
		
		if(empty($row)){
			return 0;
		}
		
		$session_id = $this->getSessionId($user_id, $user_id_2,$row->ub_date);
		
		$where = array('ub_session_id'=>$session_id);
		
		
		$count = $dbr->selectRowCount(
				'user_board',
				'*',
				$where,
				__METHOD__
		);

		return $count;
	}

	public function displayMessages( $user_id, $friend_id, $page = 0) {
		global $wgUser, $wgTitle,$wgUserCenterUrl;
		
		$ub_messages_show = 10;
		
		$friend_user = User::newFromId($friend_id);
		
		$jwuser = new JoymeWikiUser();
		$friend_profile = $jwuser->getProfile($friend_id);
		$friend_profile = $friend_profile[0];
		
		$profile = new JoymeWikiUser();
		$userprofile = $profile->getProfile($user_id);
		$userprofile = $userprofile[0];
		
		//头像
		$iconlist = array(
				$user_id=>$userprofile['icon'],
				$friend_id=>$friend_profile['icon']
		);
		//profile
		$profilelist = array(
				$user_id=>$userprofile['profileid'],
				$friend_id=>$friend_profile['profileid']
		);
		//聊天皮肤
		$chatskinlist = array(
				$user_id=>$userprofile['bubbleskin'],
				$friend_id=>$friend_profile['bubbleskin']
		);
		//头像皮肤
		$headskinlist = array(
				$user_id=>$userprofile['headskin'],
				$friend_id=>$friend_profile['headskin']
		);

		$output = ''; // Prevent E_NOTICE
		$ub_messages = $this->getUserBoardMessages( $user_id,$friend_id,$ub_messages_show,$page );
		
		if($page == 1 && count($ub_messages)<=3){
			//$output .= '<p class="talk-time">没有任何消息</p>';
			return -1;
		}else if ( $ub_messages ) {
			if( $page == 1 ){
				array_pop($ub_messages);
				array_pop($ub_messages);
				array_pop($ub_messages);
			}
			$jwuser = new JoymeWikiUser();
			$friend_profile = $jwuser->getProfile($friend_id);
			$friend_profile = $friend_profile[0];
			
			$lasttime = date('Y-m-d H:i:s');

			foreach ( $ub_messages as $ub_message ) {
				$ub_message_text = $ub_message['message_text'];
				
				//显示时间
				$timestamp = $this->dateDiff(strtotime($lasttime), strtotime($ub_message['timestamp']));
				
				if($timestamp !== false){
					$output .='<p class="talk-time">'.$timestamp.'</p>';
				}
				$lasttime = $ub_message['timestamp'];
				
				//展示内容
				$talk_class = $ub_message['sender_uid'] == $user_id?'r':'l';
				
				$tempProfile = $ub_message['sender_uid'] == $user_id?$userprofile:$friend_profile;
				$vipstr = $tempProfile['vtype']>0?'<span class="user-vip" title="'.$tempProfile['vdesc'].'"></span>':'';
				
				$headskin_class = $headskinlist[$ub_message['sender_uid']]==''?'':'chat-xt-def chat-xt-0'.$headskinlist[$ub_message['sender_uid']];
				$chatskin_class = $chatskinlist[$ub_message['sender_uid']]==''?'':'chat-group-def chat-group-0'.$chatskinlist[$ub_message['sender_uid']];
				
				$output .="<div id=\"user_board_msgid_{$ub_message['id']}\" class=\"user-board-message talk-{$talk_class}\">
											<a href='javascript:;' onclick='UserInfo.goHome(\"".$profilelist[$ub_message['sender_uid']]."\")'><cite class='userinfo' data-username='".$profilelist[$ub_message['sender_uid']]."'>{$iconlist[$ub_message['sender_uid']]}<span class='".$headskin_class."'></span>$vipstr</cite></a>
                                        <div class='".$chatskin_class."'><i class='chat-group-icon-def icon1'></i><i class='chat-group-icon-def icon2'></i><i class='chat-group-icon-def icon3'></i><i class='chat-group-icon-def icon4'></i>
											                                        {$ub_message_text}
											                                        </div>
											                                        </div>";
			}
		}else{
			//$output .= '<p class="talk-time">没有任何消息</p>';
			return -1;
		}

		return $output;
	}

	/**
	 * Get the escaped full URL to Special:SendBoardBlast.
	 * This is just a silly wrapper function.
	 *
	 * @return String: escaped full URL to Special:SendBoardBlast
	 */
	static function getBoardBlastURL() {
		$title = SpecialPage::getTitleFor( 'BoardList' );
		return htmlspecialchars( $title->getFullURL() );
	}

	/**
	 * Get the user board URL for $user_name.
	 *
	 * @param $user_name Mixed: name of the user whose user board URL we're
	 *							going to get.
	 * @return String: escaped full URL to the user board page
	 */
	static function getUserBoardURL( $user_name ) {
		$title = SpecialPage::getTitleFor( 'UserBoard' );
		$user_name = str_replace( '&', '%26', $user_name );
		return htmlspecialchars( $title->getFullURL( 'user=' . $user_name ) );
	}

	/**
	 * Get the board-to-board URL for the users $user_name_1 and $user_name_2.
	 *
	 * @param $user_id Mixed: name of the first user
	 * @return String: escaped full URL to the board-to-board conversation
	 */
	static function getUserBoardToBoardURL( $user_id ) {
		$title = SpecialPage::getTitleFor( 'UserBoard' );
		return htmlspecialchars( $title->getFullURL( 'fid=' . $user_id ) );
	}

	/**
	 * Gets the difference between two given dates
	 *
	 * @param $dt1 Mixed: current time, as returned by PHP's time() function
	 * @param $dt2 Mixed: date
	 * @return Difference between dates
	 */
	public function dateDiff( $date1, $date2 ) {
		$dtDiff = abs($date1 - $date2);
		
		if($dtDiff < 3600){
			return false;
		}
		
		$date1 = time();
		
		if(date("Y",$date1) != date("Y",$date2)){
			return date('Y年m月d日 H:i',$date2);
		}else if(date("m",$date1) != date("m",$date2)){
			return date('m月d日 H:i',$date2);
		}else if(date("d",$date1) != date("d",$date2)){
			if(abs(date("d",$date1)-date("d",$date2)) == 1){
				return date('昨日  H:i',$date2);
			}else{
				return date('m月d日 H:i',$date2);
			}
		}else{
			return date('H:i',$date2);
		}
	}

	public function getTimeOffset( $time, $timeabrv, $timename ) {
		$timeStr = '';
		if ( $time[$timeabrv] > 0 ) {
			$timeStr = wfMessage( "userboard-time-{$timename}", $time[$timeabrv] )->parse();
		}
		if ( $timeStr ) {
			$timeStr .= ' ';
		}
		return $timeStr;
	}

	/**
	 * Gets the time how long ago the given board message was posted
	 *
	 * @param $time
	 * @return $timeStr Mixed: time, such as "20 days" or "11 hours"
	 */
	public function getTimeAgo( $time ) {
		$timeArray = $this->dateDiff( time(), $time );
		$timeStr = '';
		$timeStrD = $this->getTimeOffset( $timeArray, 'd', 'days' );
		$timeStrH = $this->getTimeOffset( $timeArray, 'h', 'hours' );
		$timeStrM = $this->getTimeOffset( $timeArray, 'm', 'minutes' );
		$timeStrS = $this->getTimeOffset( $timeArray, 's', 'seconds' );
		$timeStr = $timeStrD;
		if ( $timeStr < 2 ) {
			$timeStr .= $timeStrH;
			$timeStr .= $timeStrM;
			if ( !$timeStr ) {
				$timeStr .= $timeStrS;
			}
		}else{
			$timeStr = $time;
		}
		return $timeStr;
	}
}
