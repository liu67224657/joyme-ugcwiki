<?php
/**
 * Display User Board messages for a user
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
use Joyme\qiniu\Qiniu_Utils;
class SpecialViewUserBoard extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'UserBoard' );
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgUser,$wgWikiname,$wgQiNiuBucket,$wgQiNiuPath,$wgUserBoardWebSocketUrl,$wgUserCenterUrl;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$currentUser = $wgUser;

		// Set the page title, robot policies, etc.
		
		$this->setHeaders();

		// Add CSS & JS
		$out->addModuleStyles( array(
			'ext.socialprofile.userprofile.usercentercommon.css',
			'ext.socialprofile.clearfix',
			'ext.socialprofile.userboard.headskin.css',
			'ext.socialprofile.userboard.css'
		) );
		$out->addModules( 'ext.socialprofile.userboard.js' );

		$ub_messages_show = 3;
		$user_id = $currentUser->getId();
		$friend_id = $request->getVal( 'fid' ,0);
		$friend_user = User::newFromId($friend_id);
		
		$page = 1;
		//$page = $request->getInt( 'page', 1 );
		
		if($user_id == $friend_id){
			$out->setPageTitle( $this->msg( 'boardnofriends' )->plain() );
			$out->addWikiMsg( 'boardnofriends' );
			return '';
		}

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the UserBoard page
		 */
		if ( !$currentUser->isLoggedIn() ) {
			$out->setPageTitle( $this->msg( 'boardblastlogintitle' )->plain() );
			$out->addWikiMsg( 'boardblastlogintext' );
			return '';
		}
		
		$out->redirectHome('Special:UserBoard&fid='.$friend_id);

		if(User::isUsableName($friend_user->getName()) == false){
			$out->setPageTitle( $this->msg( 'boardnofriends' )->plain() );
			$out->addWikiMsg( 'boardnofriends' );
			return '';
		}
		
		$user_list = array(
				$user_id=>$currentUser->getName(),
				$friend_id=>$friend_user->getName()
		);

		/**
		 * Config for the page
		 */

		$b = new UserBoard();
		$ub_messages = $b->getUserBoardMessages(
			$user_id,
			$friend_id,
			$ub_messages_show,
			$page
		);
		
		$msg_count = $b->getUserBoardToBoardCount($user_id, $friend_id);
		
		$uuf = new UserUserFollow();
		$isfollow = $uuf->checkUserUserFollow($currentUser, $friend_user);
		
		
		$friendPageURL = htmlspecialchars( Title::makeTitle( NS_USER, $friend_user->getName() )->getFullURL() );
		
		$out->setPageTitle( $this->msg( 'boardtitle', $friend_user->getName() )->parse()  );
		
		$blast = SpecialPage::getTitleFor('BoardList');
		if($isfollow){
			$backtypestr = 'type=1';
		}else{
			$backtypestr = 'type=2';
		}
		
		
		$output = '<div class="col-md-9">
	                <div id="main">
	                    <div class="talking-box">
	                        <h3 class="web-hide">'.Linker::LinkKnown($blast, '<i class="fa fa-angle-left"></i>返回',array(),$backtypestr).'</h3>
	                        <div class="talk-box">
								<div class="talk-tit-box">
                                <div class="talk-tit">
                                    <cite>
                                        <i class="fa fa-envelope-o"></i>
                                    </cite>
                                    <span>与<a href="'.$friendPageURL.'">'.$friend_user->getName().'</a>对话中</span>
                                    <div class="talk-r" style="opacity:0;height:0;width:0;margin:0;padding:0;min-height:0;"><div><span class="sending"></span><span class="send-error"></span></div></div>
                                </div>
                                <p>着迷WIKI不会以私信形式向你索要账号密码等个人信息，请谨慎回复</p>
                            </div>
                            <div class="talk-cont-con">
                                <div id="board_list" class="talk-con">';
		
		//$output .= '<div align="center"><a id="clearboardmessage" href="javascript:;">清空</a></div>';
		
		$jwuser = new JoymeWikiUser();
		$friend_profile = $jwuser->getProfile($friend_id);
		$friend_profile = $friend_profile[0];
		
		$profile = new JoymeWikiUser();
		$userprofile = $profile->getProfile($currentUser->getId());
		$userprofile = $userprofile[0];
		//var_dump('<pre>',$userprofile);exit;
		
		if ($userprofile['sex'] == '1'){
			$genderIcon = 'man';
		} elseif ($userprofile['sex'] == '0'){
			$genderIcon = 'female';
		} else {
			$genderIcon = '';
		}
		//头像
		$iconlist = array(
				$currentUser->getId()=>$userprofile['icon'],
				$friend_id=>$friend_profile['icon']
		);
		//profile
		$profilelist = array(
				$currentUser->getId()=>$userprofile['profileid'],
				$friend_id=>$friend_profile['profileid']
		);
		//聊天皮肤
		$chatskinlist = array(
				$currentUser->getId()=>$userprofile['bubbleskin'],
				$friend_id=>$friend_profile['bubbleskin']
		);
		//头像皮肤
		$headskinlist = array(
				$currentUser->getId()=>$userprofile['headskin'],
				$friend_id=>$friend_profile['headskin']
		);
		
		$friend_stats = new UserStats( $friend_user->getID(), $friend_user->getName() );
		$friend_stats_data = $friend_stats->getUserStats();
		
		$is_secretchat = $friend_stats_data['is_secretchat'];
		
		if ( $ub_messages ) {
			
			if($msg_count>3){
				$output .='<p class="more-new"><a id="getboardmessage" href="javascript:;">查看更多信息</a></p>';
			}
			$lasttime = date('Y-m-d H:i:s');

			foreach ( $ub_messages as $ub_message ) {
				$ub_message_text = $ub_message['message_text'];
				
				//显示时间
				$timestamp = $b->dateDiff(strtotime($lasttime), strtotime($ub_message['timestamp']));
				
				if($timestamp !== false){
					$output .='<p class="talk-time">'.$timestamp.'</p>';
				}
				$lasttime = $ub_message['timestamp'];
				
				//展示内容
				$talk_class = $ub_message['sender_uid'] == $user_id?'r':'l';
				
				$tempProfile = $ub_message['sender_uid'] == $currentUser->getId()?$userprofile:$friend_profile;
				$vipstr = $tempProfile['vtype']>0?'<span class="user-vip" title="'.$tempProfile['vdesc'].'"></span>':'';

				$headskin_class = $headskinlist[$ub_message['sender_uid']]==''?'':'chat-xt-def chat-xt-0'.$headskinlist[$ub_message['sender_uid']];
				$chatskin_class = $chatskinlist[$ub_message['sender_uid']]==''?'':'chat-group-def chat-group-0'.$chatskinlist[$ub_message['sender_uid']];
				
				$output .="<div id=\"user_board_msgid_{$ub_message['id']}\" class=\"user-board-message talk-{$talk_class}\">
                                        <a href='".$wgUserCenterUrl.$profilelist[$ub_message['sender_uid']]."' target=\"_blank\"><cite class='userinfo' data-username='".$profilelist[$ub_message['sender_uid']]."'><img src=\"{$iconlist[$ub_message['sender_uid']]}\"><span class='".$headskin_class."'></span>".$vipstr."</cite></a>
                                        <div class='".$chatskin_class."'><i class='chat-group-icon-def icon1'></i><i class='chat-group-icon-def icon2'></i><i class='chat-group-icon-def icon3'></i><i class='chat-group-icon-def icon4'></i>
											{$ub_message_text}
                                        </div>
                                    </div>";
				
			}
			$output .='<p id="msg-history" class="talk-time">以上为历史消息</p>';
		} else {
			$output .= '<p class="talk-time">没有任何消息</p>';
		}
		$output .= '</div></div>';
		$output .= '<div class="talk-text fn-clear">
                                <div class="talk-text-bq" id="szlistBtn">
                                    <i class="fa fa-smile-o talk-btn-face talk-btns">
										<div class="talk-btn-box" id="first-face" style="display:none;">
				                             <span></span> 
				                             <div class="talk-btn-facecont">
				                                 <span class="facecont-tit"></span>
				                                 <div class="facecont-cont"></div>
				                             </div>
				                        </div>
									</i>
                                    <i class="fa fa-picture-o" id="commentImg"></i>
                                </div>
                                <div class="talk-input">
                                    <textarea name="message" id="message"></textarea>
                                </div>
                                <div class="fn-clear talk-send">
                                    <button id="sendmsg" class="talk-btn fn-right">发送</button>
                                </div>
								<i class="i-error" id="sendmsg_rs"></i>
                                <div class="cancel-flo '.($is_secretchat==0?'on':'').'">对方已关闭私信功能</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
		
		//右侧数据
		$user = $currentUser;
		$stats = new UserStats( $user->getID(), $user->getName() );
		$stats_data = $stats->getUserStats();
		
		$userPageURL = htmlspecialchars( Title::makeTitle( NS_USER, $user->getName() )->getFullURL() );
		
		$boardCount = $stats_data['user_board'];
		$privboardCount = $stats_data['user_board_priv'];
		
		
		$output.='<div class="col-md-3 web-hide ">
                <div id="sidebar">
                    <div class="user-mess-box">
                        <div class="user-int-mess">
                            <a target="_blank" href="'.$userPageURL.'" class="userinfo" data-username="'.$userprofile['profileid'].'">
                            	<img src="'.$userprofile['icon'].'" />
                            	'.($userprofile['vtype']>0?'<span class="user-vip" title="'.$userprofile['vdesc'].'"></span>':'').'
                            	<span class="luojiaoye-def luojiaoye-dec-0'.$userprofile['headskin'].'"></span>
                            </a>
                            <font class="nickname">'.$user->getName().'</font>
                            <i class="user-sex '.$genderIcon.'"></i>
                        </div>
                        <div class="user-messing">
                            <a id="boardcount_a" href="'.SpecialPage::getTitleFor('BoardList')->getLocalUrl('type=1').'" class="lettering '.($isfollow?'on':'').'">私信'.($boardCount>0?'<i id="boardcount" '.($boardCount>99?'class="on"':'').'>'.$boardCount.'</i>':'').'</a>
                            <a id="preboardcount_a" href="'.SpecialPage::getTitleFor('BoardList')->getLocalUrl('type=2').'" class="unletter '.($isfollow?'':'on').'">未关注人私信'.($privboardCount>0?'<i id="preboardcount" '.($privboardCount>99?'class="on"':'').'>'.$privboardCount.'</i>':'').'</a>
                        </div>
                    </div>
                </div>
            </div>';
		
		$uptoken = Qiniu_Utils::Qiniu_UploadToken($wgQiNiuBucket);
		
		
		$output .='<input type="hidden" id="friend_id" name="friend_id" value="' . $friend_id . '"/>
					<input type="hidden" id="qiniu_domain" name="qiniu_domain" value="' . $wgQiNiuPath . '"/>
					<input type="hidden" id="uptoken" name="uptoken" value="' . $uptoken . '"/>
					<input type="hidden" id="friend_name" name="friend_name" value="' . $friend_user->getName() . '"/>';
		
		$output .='<input type="hidden" id="UserBoardWebSocketUrl" name="UserBoardWebSocketUrl" value="' . $wgUserBoardWebSocketUrl . '"/>';
		
		$output.="<script type='text/javascript'>
				
				var profilelist = {'".$user_id."':".json_encode($userprofile).",'".$friend_id."':".json_encode($friend_profile)."};
				
				</script>";
		
		$output.="<script type='text/javascript' src='http://lib.joyme.com/static/third/qiniuupload/qiniu.js'></script>";
		$output.="<script type='text/javascript' src='http://lib.joyme.com/static/third/qiniuupload/plupload/plupload.full.min.js'></script>";
		
		$out->addHTML( $output );
	}
}
